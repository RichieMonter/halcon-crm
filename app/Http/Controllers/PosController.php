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
    public function index(Order $order)
    {
        $order->load('customer', 'items.product');

        // Solo productos y servicios (excluyendo insumos internos)
        $products = Product::whereIn('type', ['PRODUCTO', 'SERVICIO'])->get();
        $customers = Customer::orderBy('name')->get();
        $categories = Product::whereNotNull('category')->distinct()->pluck('category');

        // Modo estático si la orden ya está completada
        $isReadOnly = ($order->status === 'completado');

        return view('pos.index', compact('order', 'products', 'categories', 'customers', 'isReadOnly'));
    }

    public function update(Request $request, Order $order)
    {
        if ($order->status === 'completado') {
            return response()->json(['success' => false, 'message' => 'Esta orden ya está completada y no se puede modificar.'], 403);
        }

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

            $order->update([
                'customer_id' => $request->customer_id,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $total,
                'status' => $request->status,
                'service_notes' => $request->service_notes,
            ]);

            $order->items()->delete();

            foreach ($request->cart as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'subtotal' => $item['price'] * $item['quantity'],
                ]);

                $product = Product::find($item['id']);
                if ($product && $product->type !== 'SERVICIO' && $request->status === 'completado') {
                    $product->decrement('stock', $item['quantity']);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '¡Venta procesada con éxito!',
                'order_id' => $order->id,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar: ' . $e->getMessage(),
            ], 500);
        }
    }
}
