<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Customer;
use App\Models\Product;
use App\Models\PrinterReading;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
    
        // Ventas del día y comparativa vs ayer
        $todaySales = Order::where('status', 'completado')
                            ->whereDate('updated_at', Carbon::today())
                            ->sum('total');
    
        $yesterdaySales = Order::where('status', 'completado')
                                ->whereDate('updated_at', Carbon::yesterday())
                                ->sum('total');
        
        $percentageVsYesterday = $yesterdaySales > 0
            ? (($todaySales - $yesterdaySales) / $yesterdaySales) * 100
            : null;
    
        $completedOrdersCount = Order::where('status', 'completado')
                                     ->whereDate('updated_at', Carbon::today())
                                     ->count();
    
        $pendingOrdersCount = Order::where('status', 'pendiente')->count();
    
        $lowStockProducts = Product::whereIn('type', ['PRODUCTO', 'INSUMO'])
            ->where(function ($query) {
                $query->whereColumn('stock', '<=', 'min_stock')
                    ->orWhere('stock', '<=', 5);
            })
            ->orderBy('stock')
            ->orderBy('name')
            ->get();
    
        $soldItems = OrderItem::select('product_id', DB::raw('SUM(quantity) as total_sold'))
            ->whereHas('order', function($q) {
                $q->where('status', 'completado');
            })
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->with('product');

        $topProductsList = (clone $soldItems)
            ->whereHas('product', fn ($query) => $query->where('type', 'PRODUCTO'))
            ->get();

        $internalSupplies = Product::where('type', 'INSUMO')->orderBy('stock')->orderBy('name')->get();

        $serviceGroups = (clone $soldItems)
            ->whereHas('product', fn ($query) => $query->where('type', 'SERVICIO'))
            ->addSelect('print_mode')
            ->groupBy('print_mode')
            ->get()
            ->toBase()
            ->groupBy(function ($item) {
                $service = $item->product;
                $description = Str::lower(Str::ascii($service->category.' '.$service->name));

                return match (true) {
                    $item->print_mode !== null || $service->isPrintingService() || str_contains($description, 'impresion') => 'Impresiones',
                    str_contains($description, 'engargol') => 'Engargolados',
                    str_contains($description, 'enmicad') => 'Enmicados',
                    default => $service->category ?: $service->name,
                };
            });

        $printSales = $serviceGroups->get('Impresiones', collect());
        $printCounts = [
            'Blanco y negro' => $printSales->where('print_mode', 'mono')->sum('total_sold'),
            'A color' => $printSales->where('print_mode', 'color')->sum('total_sold'),
            'Sin modo registrado' => $printSales->whereNull('print_mode')->sum('total_sold'),
        ];
        $printSizeCounts = $printSales->groupBy(function ($item) {
            $service = $item->product;
            $description = Str::lower(Str::ascii($service->name.' '.$service->category));

            return match (true) {
                preg_match('/\bdoble[\s-]+carta\b/', $description) === 1 => 'Doble carta',
                preg_match('/\boficio\b/', $description) === 1 => 'Oficio',
                preg_match('/\bcarta\b/', $description) === 1 => 'Carta',
                default => 'Sin tamaño registrado',
            };
        })->map(fn ($items) => $items->sum('total_sold'));
        $printSizeCounts = $printSizeCounts->all() + [
            'Carta' => 0, 'Oficio' => 0, 'Doble carta' => 0, 'Sin tamaño registrado' => 0,
        ];
        $servicesSold = $serviceGroups->except('Impresiones')
            ->map(fn ($items) => $items->sum('total_sold'))
            ->union(['Engargolados' => 0, 'Enmicados' => 0])
            ->sortDesc();
    
        // Consulta de órdenes con filtro de búsqueda individual
        $orders = Order::with('customer', 'user')
            ->when($search, function($query, $search) {
                $query->where('invoice_number', 'LIKE', "%{$search}%")
                      ->orWhere('order_number', 'LIKE', "%{$search}%")
                      ->orWhere('order_type', 'LIKE', "%{$search}%")
                      ->orWhereHas('customer', function($q) use ($search) {
                          $q->where('name', 'LIKE', "%{$search}%")
                            ->orWhere('company_name', 'LIKE', "%{$search}%");
                      });
            })
            ->latest()
            ->get();
    
        $customers = Customer::orderBy('name')->get();
    
        $nextId = (Order::max('id') ?? 0) + 1;
        $suggestedFolio = 'COT-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
        $printerLevels = PrinterReading::levels();
    
        return view('orders.index', compact(
            'orders', 
            'customers', 
            'todaySales', 
            'yesterdaySales',
            'percentageVsYesterday', 
            'completedOrdersCount',
            'pendingOrdersCount', 
            'lowStockProducts',
            'topProductsList',
            'servicesSold',
            'printCounts',
            'printSizeCounts',
            'internalSupplies',
            'printerLevels',
            'suggestedFolio'
        ));
    }
    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'order_type' => 'nullable|string',
            'interest_level' => 'required|in:ALTO,MEDIO,BAJO',
        ]);

        $nextId = (Order::max('id') ?? 0) + 1;
        $folio = 'COT-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

        Order::create([
            'order_number' => $folio,
            'invoice_number' => $folio,
            'customer_id' => $request->customer_id,
            'created_by_user_id' => Auth::id() ?? 1,
            'order_type' => $request->order_type ?? 'Cotización General',
            'total' => 0.00,
            'interest_level' => $request->interest_level,
            'status' => 'pendiente',
        ]);

        return redirect()->route('orders.index')->with('success', '¡Cotización ' . $folio . ' creada exitosamente!');
    }

    public function update(Request $request, Order $order)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'order_type' => 'nullable|string',
            'interest_level' => 'required|in:ALTO,MEDIO,BAJO',
            'status' => 'required|in:pendiente,cancelado',
        ], [
            'status.in' => 'Finaliza la venta desde el carrito para registrar el método de pago.',
        ]);

        $updated = DB::transaction(function () use ($request, $order) {
            $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            if ($order->status === 'completado') {
                return false;
            }
            return $order->update([
                'customer_id' => $request->customer_id,
                'order_type' => $request->order_type ?? 'Cotización General',
                'interest_level' => $request->interest_level,
                'status' => $request->status,
            ]);
        });

        if (!$updated) {
            return redirect()->route('orders.index')->with('error', 'Las ventas completadas quedan cerradas.');
        }

        return redirect()->route('orders.index')->with('success', '¡Cotización ' . $order->invoice_number . ' actualizada exitosamente!');
    }
}
