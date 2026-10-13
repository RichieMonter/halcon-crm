<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Halcón System - Catálogo e Inventario</title>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal" 
      x-data="{ 
          openModal: false, 
          openEditModal: false, 
          openMenu: false, 
          createType: 'PRODUCTO',
          editForm: { id: '', code: '', name: '', type: 'PRODUCTO', category: '', price: 0, cost: 0, stock: 0, min_stock: 5 } 
      }">

    <!-- Encabezado Superior -->
    <nav class="bg-slate-900 text-white px-6 py-3 flex justify-between items-center shadow-md">
        <div class="flex items-center space-x-4">
            <button @click="openMenu = !openMenu" class="text-gray-300 hover:text-white focus:outline-none">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>
            <span class="font-bold tracking-wider text-sm md:text-base">PAPELERÍA N / HALCÓN SYSTEM</span>
        </div>

        <div class="flex items-center space-x-4">
            <form action="{{ route('logout') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white text-xs px-3 py-1.5 rounded transition">Cerrar Sesión</button>
            </form>
        </div>
    </nav>

    <!-- Menú Desplegable Lateral -->
    <div x-show="openMenu" @click.away="openMenu = false" class="bg-slate-800 text-white w-64 fixed left-0 top-14 bottom-0 z-50 p-4 shadow-xl border-t border-slate-700" x-cloak>
        <p class="text-xs uppercase font-semibold text-gray-400 mb-4 tracking-wider">Módulos del Sistema</p>
        <ul class="space-y-2">
            <li><a href="{{ route('orders.index') }}" class="block px-3 py-2 rounded hover:bg-slate-700 text-sm text-gray-300">📊 Dashboard (Inicio)</a></li>
            <li><a href="{{ route('customers.index') }}" class="block px-3 py-2 rounded hover:bg-slate-700 text-sm text-gray-300">👥 Gestión de Clientes</a></li>
            <li><a href="{{ route('products.index') }}" class="block px-3 py-2 rounded bg-blue-600 font-semibold text-sm">📦 Catálogo e Inventario</a></li>
        </ul>
    </div>

    <!-- Contenido Principal -->
    <div class="container mx-auto px-4 py-6">

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative mb-6 shadow-sm">
                <span class="font-bold">{{ session('success') }}</span>
            </div>
        @endif

        <!-- TARJETA CONTENEDORA PRINCIPAL -->
        <div class="bg-white rounded-xl shadow-md border border-gray-200 p-6">
            
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center pb-6 mb-6 border-b border-gray-100 gap-4">
                <div>
                    <h2 class="text-xl font-extrabold text-gray-800">Catálogo de Productos, Servicios e Insumos</h2>
                    <p class="text-xs text-gray-500">Gestión general del inventario, precios y stock del negocio</p>
                </div>
                
                <button @click="openModal = true" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs px-4 py-2.5 rounded-lg shadow transition flex items-center">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Agregar producto/servicio
                </button>
            </div>

            <!-- FILTROS DE BÚSQUEDA -->
            <form method="GET" action="{{ route('products.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6 bg-gray-50 p-4 rounded-xl border border-gray-200">
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Producto o Código</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Ej. CUAD-01 o Cuaderno..." class="w-full bg-white border border-gray-300 rounded-lg p-2 text-sm focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Categoría</label>
                    <select name="category" class="w-full bg-white border border-gray-300 rounded-lg p-2 text-sm focus:ring-2 focus:ring-indigo-500">
                        <option value="">-- Todas las categorías --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Estado de Stock</label>
                    <select name="stock_status" class="w-full bg-white border border-gray-300 rounded-lg p-2 text-sm focus:ring-2 focus:ring-indigo-500">
                        <option value="">-- Todos --</option>
                        <option value="disponible" {{ request('stock_status') == 'disponible' ? 'selected' : '' }}>Disponible</option>
                        <option value="bajo" {{ request('stock_status') == 'bajo' ? 'selected' : '' }}>Stock Bajo / Alerta</option>
                        <option value="agotado" {{ request('stock_status') == 'agotado' ? 'selected' : '' }}>Agotado</option>
                    </select>
                </div>

                <div class="flex items-end space-x-2">
                    <button type="submit" class="w-full bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs py-2.5 rounded-lg shadow transition">
                        Buscar
                    </button>
                    @if(request()->anyFilled(['search', 'category', 'stock_status']))
                        <a href="{{ route('products.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold text-xs py-2.5 px-3 rounded-lg text-center transition">
                            Limpiar
                        </a>
                    @endif
                </div>
            </form>

            <!-- TABLA DE PRODUCTOS -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200 text-xs font-bold text-gray-500 uppercase tracking-wider">
                            <th class="py-3 px-4">Código</th>
                            <th class="py-3 px-4">Nombre del Producto/Servicio</th>
                            <th class="py-3 px-4">Categoría / Tipo</th>
                            <th class="py-3 px-4">Precio ($)</th>
                            <th class="py-3 px-4">Stock</th>
                            <th class="py-3 px-4 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm">
                        @forelse($products as $product)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="py-3 px-4 font-bold text-indigo-600 text-xs">{{ $product->code }}</td>
                                <td class="py-3 px-4 font-semibold text-gray-800">{{ $product->name }}</td>
                                <td class="py-3 px-4 text-xs text-gray-600">
                                    <span class="block font-bold">{{ $product->category ?? 'General' }}</span>
                                    <span class="text-[10px] bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full border border-slate-200">{{ $product->type }}</span>
                                </td>
                                <td class="py-3 px-4 font-bold text-gray-900">${{ number_format($product->price, 2) }}</td>
                                <td class="py-3 px-4">
                                    @if($product->type === 'SERVICIO')
                                        <span class="text-xs text-gray-400 italic">No aplica (Servicio)</span>
                                    @else
                                        <span class="px-2.5 py-1 text-xs font-bold rounded-full 
                                            {{ $product->stock == 0 ? 'bg-red-100 text-red-700 border border-red-200' : ($product->stock <= $product->min_stock ? 'bg-amber-100 text-amber-700 border border-amber-200' : 'bg-emerald-100 text-emerald-700 border border-emerald-200') }}">
                                            {{ $product->stock }} pzas
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-center space-x-2">
                                    <!-- Botón Editar -->
                                    <button @click="
                                        openEditModal = true;
                                        editForm.id = '{{ $product->id }}';
                                        editForm.code = '{{ $product->code }}';
                                        editForm.name = '{{ $product->name }}';
                                        editForm.type = '{{ $product->type }}';
                                        editForm.category = '{{ $product->category }}';
                                        editForm.price = '{{ $product->price }}';
                                        editForm.cost = '{{ $product->cost }}';
                                        editForm.stock = '{{ $product->stock }}';
                                        editForm.min_stock = '{{ $product->min_stock }}';
                                    " class="text-blue-500 hover:text-blue-700 p-1" title="Editar / Reabastecer Stock">✏️</button>

                                    <!-- Botón Eliminar -->
                                    <form action="{{ route('products.destroy', $product->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro de eliminar este elemento?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700 p-1" title="Eliminar">🗑️</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-gray-400 text-sm">
                                    No se encontraron productos o servicios registrados en el catálogo.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="mt-4">
                {{ $products->links() }}
            </div>

        </div>

    </div>

    <!-- MODAL DE NUEVO PRODUCTO / SERVICIO -->
    <div x-show="openModal" class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50 flex items-center justify-center p-4" x-cloak>
        <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full p-6 relative">
            <div class="flex justify-between items-center pb-3 border-b border-gray-100">
                <h3 class="text-lg font-bold text-gray-800">Registrar Nuevo Elemento</h3>
                <button @click="openModal = false" class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
            </div>

            <form action="{{ route('products.store') }}" method="POST" class="space-y-4 mt-4">
                @csrf
                
                @if ($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-3 py-2 rounded-lg text-xs">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase">Código / SKU</label>
                        <input type="text" name="code" required placeholder="Ej. CUAD-01" class="mt-1 w-full border border-gray-300 rounded-lg p-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase">Tipo</label>
                        <select name="type" x-model="createType" required class="mt-1 w-full border border-gray-300 rounded-lg p-2 text-sm">
                            <option value="PRODUCTO">PRODUCTO (Inventario)</option>
                            <option value="SERVICIO">SERVICIO (Mano de obra)</option>
                            <option value="INSUMO">INSUMO (Uso interno)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase">Nombre del Producto / Servicio / Insumo</label>
                    <input type="text" name="name" required placeholder="Ej. Cuaderno Profesional Rayado" class="mt-1 w-full border border-gray-300 rounded-lg p-2 text-sm">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase">Categoría</label>
                        <input type="text" name="category" placeholder="Ej. Papelería" class="mt-1 w-full border border-gray-300 rounded-lg p-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase">Stock Inicial</label>
                        <input type="number" name="stock" 
                               :value="createType === 'SERVICIO' ? 0 : 10" 
                               :disabled="createType === 'SERVICIO'" 
                               :class="{ 'bg-gray-100 cursor-not-allowed text-gray-400': createType === 'SERVICIO' }"
                               min="0" required class="mt-1 w-full border border-gray-300 rounded-lg p-2 text-sm">
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase">Precio ($)</label>
                        <input type="number" step="0.01" name="price" value="0.00" min="0" required class="mt-1 w-full border border-gray-300 rounded-lg p-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase">Costo ($)</label>
                        <input type="number" step="0.01" name="cost" value="0.00" min="0" required class="mt-1 w-full border border-gray-300 rounded-lg p-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase">Stock Mínimo</label>
                        <input type="number" name="min_stock" 
                               :value="createType === 'SERVICIO' ? 0 : 5" 
                               :disabled="createType === 'SERVICIO'" 
                               :class="{ 'bg-gray-100 cursor-not-allowed text-gray-400': createType === 'SERVICIO' }"
                               min="0" required class="mt-1 w-full border border-gray-300 rounded-lg p-2 text-sm">
                    </div>
                </div>

                <div class="pt-3 flex space-x-2">
                    <button type="button" @click="openModal = false" class="w-1/2 bg-gray-200 text-gray-700 py-2.5 rounded-lg text-sm font-semibold">Cancelar</button>
                    <button type="submit" class="w-1/2 bg-indigo-600 text-white py-2.5 rounded-lg text-sm font-semibold hover:bg-indigo-700 transition">Guardar Elemento</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL DE EDITAR / REABASTECER STOCK -->
    <div x-show="openEditModal" class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50 flex items-center justify-center p-4" x-cloak>
        <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full p-6 relative">
            <div class="flex justify-between items-center pb-3 border-b border-gray-100">
                <h3 class="text-lg font-bold text-gray-800">Editar / Reabastecer Elemento</h3>
                <button @click="openEditModal = false" class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
            </div>

            <form :action="'/products/' + editForm.id" method="POST" class="space-y-4 mt-4">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase">Código / SKU</label>
                        <input type="text" name="code" x-model="editForm.code" required class="mt-1 w-full border border-gray-300 rounded-lg p-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase">Tipo</label>
                        <select name="type" x-model="editForm.type" required class="mt-1 w-full border border-gray-300 rounded-lg p-2 text-sm">
                            <option value="PRODUCTO">PRODUCTO (Inventario)</option>
                            <option value="SERVICIO">SERVICIO (Mano de obra)</option>
                            <option value="INSUMO">INSUMO (Uso interno)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase">Nombre del Producto / Servicio / Insumo</label>
                    <input type="text" name="name" x-model="editForm.name" required class="mt-1 w-full border border-gray-300 rounded-lg p-2 text-sm">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase">Categoría</label>
                        <input type="text" name="category" x-model="editForm.category" class="mt-1 w-full border border-gray-300 rounded-lg p-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase">Stock Actual (Reabastecer)</label>
                        <input type="number" name="stock" x-model="editForm.stock" 
                               :disabled="editForm.type === 'SERVICIO'" 
                               :class="{ 'bg-gray-100 cursor-not-allowed text-gray-400': editForm.type === 'SERVICIO' }"
                               min="0" required class="mt-1 w-full border border-gray-300 rounded-lg p-2 text-sm">
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase">Precio ($)</label>
                        <input type="number" step="0.01" name="price" x-model="editForm.price" min="0" required class="mt-1 w-full border border-gray-300 rounded-lg p-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase">Costo ($)</label>
                        <input type="number" step="0.01" name="cost" x-model="editForm.cost" min="0" required class="mt-1 w-full border border-gray-300 rounded-lg p-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase">Stock Mínimo</label>
                        <input type="number" name="min_stock" x-model="editForm.min_stock" 
                               :disabled="editForm.type === 'SERVICIO'" 
                               :class="{ 'bg-gray-100 cursor-not-allowed text-gray-400': editForm.type === 'SERVICIO' }"
                               min="0" required class="mt-1 w-full border border-gray-300 rounded-lg p-2 text-sm">
                    </div>
                </div>

                <div class="pt-3 flex space-x-2">
                    <button type="button" @click="openEditModal = false" class="w-1/2 bg-gray-200 text-gray-700 py-2.5 rounded-lg text-sm font-semibold">Cancelar</button>
                    <button type="submit" class="w-1/2 bg-blue-600 text-white py-2.5 rounded-lg text-sm font-semibold hover:bg-blue-700 transition">Actualizar Cambios</button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>
