<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    public function index()
    {
        $products = Product::where('status', true)->get();
        $customers = Customer::orderBy('name')->get();

        return view('pos.index', compact('products', 'customers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'cart' => 'required|array|min:1',
            'cart.*.id' => 'required|exists:products,id',
            'cart.*.quantity' => 'required|integer|min:1',
            'cart.*.price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'status' => 'required|in:completado,pendiente,cancelado',
            'service_notes' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $subtotal = 0;
            foreach ($request->cart as $item) {
                $subtotal += $item['price'] * $item['quantity'];
            }

            $discount = $request->discount ?? 0;
            $total = max(0, $subtotal - $discount);

            // Generar número de orden único
            $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(uniqid());

            $order = Order::create([
                'customer_id' => $request->customer_id,
                'order_number' => $orderNumber,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $total,
                'status' => $request->status,
                'service_notes' => $request->service_notes,
            ]);

            foreach ($request->cart as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'subtotal' => $item['price'] * $item['quantity'],
                ]);

                // Descontar stock solo si el producto maneja inventario
                $product = Product::find($item['id']);
                if ($product && $product->type !== 'SERVICIO') {
                    $product->decrement('stock', $item['quantity']);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Venta registrada con éxito',
                'order_id' => $order->id,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la venta: ' . $e->getMessage(),
            ], 500);
        }
    }
}