<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Customer;
use App\Models\Product;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        
        $percentageVsYesterday = 0;
        if ($yesterdaySales > 0) {
            $percentageVsYesterday = (($todaySales - $yesterdaySales) / $yesterdaySales) * 100;
        } elseif ($todaySales > 0) {
            $percentageVsYesterday = 100;
        }
    
        $completedOrdersCount = Order::where('status', 'completado')
                                     ->whereDate('updated_at', Carbon::today())
                                     ->count();
    
        $pendingOrdersCount = Order::where('status', 'pendiente')->count();
    
        $lowStockProducts = Product::where('type', 'PRODUCTO')
                                   ->where(function($query) {
                                       $query->whereColumn('stock', '<=', 'min_stock')
                                             ->orWhere('stock', '<=', 5);
                                   })->get();
    
        $topProductsList = OrderItem::select('product_id', DB::raw('SUM(quantity) as total_sold'))
            ->whereHas('order', function($q) {
                $q->where('status', 'completado');
            })
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->with('product')
            ->get();
    
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
    
        return view('orders.index', compact(
            'orders', 
            'customers', 
            'todaySales', 
            'percentageVsYesterday', 
            'completedOrdersCount',
            'pendingOrdersCount', 
            'lowStockProducts',
            'topProductsList',
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
            'status' => 'required|in:completado,pendiente,cancelado',
        ]);

        $order->update([
            'customer_id' => $request->customer_id,
            'order_type' => $request->order_type ?? 'Cotización General',
            'interest_level' => $request->interest_level,
            'status' => $request->status,
        ]);

        return redirect()->route('orders.index')->with('success', '¡Cotización ' . $order->invoice_number . ' actualizada exitosamente!');
    }
}
