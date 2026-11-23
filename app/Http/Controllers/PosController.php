<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\MarketingCampaign;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PrinterReading;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PosController extends Controller
{
    public function index(Order $order)
    {
        $order->load('customer', 'items.product');

        // Solo productos y servicios (excluyendo insumos internos)
        $products = Product::whereIn('type', ['PRODUCTO', 'SERVICIO'])->get();
        $products->each(fn (Product $product) => $product->setAttribute('is_printing', $product->isPrintingService()));
        $printers = PrinterReading::PRINTERS;
        $customers = Customer::orderBy('name')->get();
        $categories = Product::whereNotNull('category')->distinct()->pluck('category');

        // Modo estático si la orden ya está completada
        $isReadOnly = ($order->status === 'completado');
        $recommendations = [];
        $paymentMethods = Order::PAYMENT_METHODS;
        if (!$isReadOnly) {
            $topIds = Customer::rankedByPurchases()->limit(5)->get()->modelKeys();
            $campaigns = MarketingCampaign::active()
                ->orderByDesc('discount_percent')->orderByDesc('id')->get();
            foreach ($customers as $customer) {
                $topCustomer = in_array($customer->id, $topIds, true);
                $campaign = $campaigns->first(fn ($campaign) => $campaign->audience === 'all' || $topCustomer);

                $recommendations[$customer->id] = array_values(array_filter([
                    $customer->preferred_discount > 0
                        ? ['label' => 'Descuento personalizado del cliente', 'percent' => $customer->preferred_discount] : null,
                    $customer->id === ($topIds[0] ?? null)
                        ? ['label' => 'Cliente con mayor importe de compras', 'percent' => 75] : null,
                    $campaign ? ['label' => 'Campaña: '.$campaign->name, 'percent' => $campaign->discount_percent] : null,
                ]));
            }
        }

        return view('pos.index', compact('order', 'products', 'categories', 'customers', 'isReadOnly', 'printers', 'recommendations', 'paymentMethods'));
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
            'cart.*.printer' => ['nullable', Rule::in(array_keys(PrinterReading::PRINTERS))],
            'cart.*.print_mode' => ['nullable', Rule::in(['mono', 'color'])],
            'discount_percent' => 'nullable|numeric|between:0,100|decimal:0,2',
            'payment_method' => ['exclude_unless:status,completado', 'required', Rule::in(array_keys(Order::PAYMENT_METHODS))],
            'status' => 'required|in:completado,pendiente,cancelado',
            'service_notes' => 'nullable|string',
        ], [
            'discount_percent.numeric' => 'Ingresa un porcentaje de descuento válido.',
            'discount_percent.between' => 'El descuento debe estar entre 0 y 100 %.',
            'discount_percent.decimal' => 'El descuento admite hasta dos decimales.',
            'payment_method.required' => 'Selecciona el método de pago para finalizar la venta.',
            'payment_method.in' => 'Selecciona un método de pago válido.',
        ]);

        DB::beginTransaction();

        try {
            $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            if ($order->status === 'completado') {
                DB::rollBack();

                return response()->json(['success' => false, 'message' => 'Esta orden ya está completada.'], 403);
            }

            $products = Product::findMany(array_column($request->cart, 'id'))->keyBy('id');
            $completed = $request->status === 'completado';
            $printedAt = $completed ? now()->format('Y-m-d H:i:s.u') : null;
            $subtotal = 0;
            foreach ($request->cart as $item) {
                $subtotal += $item['price'] * $item['quantity'];
            }

            $subtotal = round($subtotal, 2);
            $discountPercent = $request->discount_percent ?? 0;
            $discount = round($subtotal * $discountPercent / 100, 2);
            $total = round($subtotal - $discount, 2);

            $order->update([
                'customer_id' => $request->customer_id,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'discount_percent' => $discountPercent,
                'payment_method' => $completed ? $request->payment_method : $order->payment_method,
                'total' => $total,
                'status' => $request->status,
                'service_notes' => $request->service_notes,
            ]);

            $order->items()->delete();

            foreach ($request->cart as $index => $item) {
                $product = $products->get($item['id']);
                $printer = $product->isPrintingService() ? ($item['printer'] ?? null) : null;
                $mode = $printer ? ($item['print_mode'] ?? null) : null;
                if ($printer && count(PrinterReading::PRINTERS[$printer]['colors']) === 1) {
                    if ($mode === 'color') {
                        throw ValidationException::withMessages(["cart.$index.print_mode" => 'Selecciona una impresora a color para '.$product->name.'.']);
                    }
                    $mode = 'mono';
                }
                if ($completed && $product->isPrintingService() && (!$printer || !$mode)) {
                    throw ValidationException::withMessages(["cart.$index.printer" => 'Elige la impresora y el tipo de impresión para '.$product->name.'.']);
                }

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'subtotal' => $item['price'] * $item['quantity'],
                    'printer' => $printer,
                    'print_mode' => $mode,
                    'printed_at' => $printer ? $printedAt : null,
                ]);

                if ($product->type !== 'SERVICIO' && $completed) {
                    $product->decrement('stock', $item['quantity']);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '¡Venta procesada con éxito!',
                'order_id' => $order->id,
            ]);

        } catch (ValidationException $e) {
            DB::rollBack();

            throw $e;
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'No se pudo guardar la venta. Vuelve a intentarlo.',
            ], 500);
        }
    }
}
