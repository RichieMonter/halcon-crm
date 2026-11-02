<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function globalSearch(Request $request)
    {
        $query = trim($request->input('query'));

        if (empty($query)) {
            return redirect()->back();
        }

        // 1. Buscar coincidencia exacta o parcial en Cotizaciones / Órdenes (Folio)
        $order = Order::where('invoice_number', 'LIKE', "%{$query}%")
                      ->orWhere('order_number', 'LIKE', "%{$query}%")
                      ->first();

        if ($order) {
            return redirect()->route('orders.pos', $order->id);
        }

        // 2. Buscar coincidencia en Clientes (Nombre, Razón Social o RFC)
        $customerExists = Customer::where('name', 'LIKE', "%{$query}%")
                                  ->orWhere('company_name', 'LIKE', "%{$query}%")
                                  ->orWhere('rfc', 'LIKE', "%{$query}%")
                                  ->exists();

        if ($customerExists) {
            return redirect()->route('customers.index', ['search' => $query]);
        }

        // 3. Buscar coincidencia en Productos o Servicios (Código o Nombre)
        $productExists = Product::where('name', 'LIKE', "%{$query}%")
                                ->orWhere('code', 'LIKE', "%{$query}%")
                                ->exists();

        if ($productExists) {
            return redirect()->route('products.index', ['search' => $query]);
        }

        // 4. Si no hay coincidencia directa, enviar al catálogo de productos con el filtro
        return redirect()->route('products.index', ['search' => $query]);
    }
}
