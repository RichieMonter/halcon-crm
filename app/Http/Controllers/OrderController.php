<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class OrderController extends Controller
{
    public function index()
    {
        // 1. Ventas del día
        $todaySales = Order::whereDate('created_at', Carbon::today())->sum('total_amount');
        $yesterdaySales = Order::whereDate('created_at', Carbon::yesterday())->sum('total_amount');
        
        $percentageVsYesterday = 0;
        if ($yesterdaySales > 0) {
            $percentageVsYesterday = (($todaySales - $yesterdaySales) / $yesterdaySales) * 100;
        }

        // 2. Pedidos en proceso
        $pendingOrdersCount = Order::where('current_status', 'PENDIENTE')->count();

        // 3. Obtener cotizaciones y clientes
        $orders = Order::with('customer', 'user')->latest()->get();
        $customers = Customer::all();

        // Folio sugerido
        $nextId = (Order::max('id') ?? 0) + 1;
        $suggestedFolio = 'COT-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

        return view('orders.index', compact(
            'orders', 
            'customers', 
            'todaySales', 
            'percentageVsYesterday', 
            'pendingOrdersCount', 
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
            'invoice_number' => $folio,
            'customer_id' => $request->customer_id,
            'created_by_user_id' => Auth::id() ?? 1,
            'order_type' => $request->order_type ?? 'Cotización General',
            'total_amount' => 0.00,
            'interest_level' => $request->interest_level,
            'last_contact_date' => Carbon::now()->toDateString(),
            'current_status' => 'PENDIENTE',
            'notes' => null,
        ]);

        return redirect()->route('orders.index')->with('success', '¡Cotización ' . $folio . ' creada exitosamente!');
    }
}
