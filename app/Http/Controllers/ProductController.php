<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query();

        // Filtro por Código o Nombre del producto
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        // Filtro por Categoría
        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        // Filtro por Estado de Stock
        if ($request->filled('stock_status')) {
            $status = $request->input('stock_status');
            if ($status === 'bajo') {
                $query->whereColumn('stock', '<=', 'min_stock');
            } elseif ($status === 'agotado') {
                $query->where('stock', '=', 0);
            } elseif ($status === 'disponible') {
                $query->whereColumn('stock', '>', 'min_stock');
            }
        }

        $products = $query->latest()->paginate(10)->withQueryString();
        
        // Obtenemos categorías únicas para los filtros y el datalist
        $categories = Product::whereNotNull('category')->distinct()->pluck('category');

        // Conteo exacto de cada tipo para los SKUs autoincrementables
        $nextProdCount = Product::where('type', 'PRODUCTO')->count() + 1;
        $nextServCount = Product::where('type', 'SERVICIO')->count() + 1;
        $nextInsCount  = Product::where('type', 'INSUMO')->count() + 1;

        $suggestedSkus = [
            'PRODUCTO' => 'PROD-' . str_pad($nextProdCount, 3, '0', STR_PAD_LEFT),
            'SERVICIO' => 'SERV-' . str_pad($nextServCount, 3, '0', STR_PAD_LEFT),
            'INSUMO'   => 'INS-' . str_pad($nextInsCount, 3, '0', STR_PAD_LEFT),
        ];

        return view('products.index', compact('products', 'categories', 'suggestedSkus'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:products,code',
            'name' => 'required|string|max:255',
            'type' => 'required|in:PRODUCTO,SERVICIO,INSUMO',
            'category' => 'nullable|string|max:100',
            'price' => 'required|numeric|min:0',
            'cost' => 'required|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'min_stock' => 'nullable|integer|min:0',
        ]);

        // Si es servicio, aseguramos stock en 0 automáticamente
        if ($validated['type'] === 'SERVICIO') {
            $validated['stock'] = 0;
            $validated['min_stock'] = 0;
        } else {
            $validated['stock'] = $validated['stock'] ?? 0;
            $validated['min_stock'] = $validated['min_stock'] ?? 0;
        }

        Product::create($validated);

        return redirect()->route('products.index')->with('success', '¡Producto/Servicio registrado correctamente en el catálogo!');
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:products,code,' . $product->id,
            'name' => 'required|string|max:255',
            'type' => 'required|in:PRODUCTO,SERVICIO,INSUMO',
            'category' => 'nullable|string|max:100',
            'price' => 'required|numeric|min:0',
            'cost' => 'required|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'min_stock' => 'nullable|integer|min:0',
        ]);

        if ($validated['type'] === 'SERVICIO') {
            $validated['stock'] = 0;
            $validated['min_stock'] = 0;
        } else {
            $validated['stock'] = $validated['stock'] ?? 0;
            $validated['min_stock'] = $validated['min_stock'] ?? 0;
        }

        $product->update($validated);

        return redirect()->route('products.index')->with('success', '¡Catálogo actualizado correctamente!');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('products.index')->with('success', 'Producto eliminado del catálogo.');
    }
}