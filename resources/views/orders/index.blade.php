<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kopion CRM - Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal" x-data="{ openModal: false, openMenu: false }">

    <!-- Encabezado Superior -->
    <nav class="bg-slate-900 text-white px-6 py-3 flex justify-between items-center shadow-md">
        <div class="flex items-center space-x-4">
            <button @click="openMenu = !openMenu" class="text-gray-300 hover:text-white focus:outline-none">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>

            <!-- Barra de Búsqueda -->
            <div class="relative w-64 md:w-96">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </span>
                <input type="text" placeholder="Buscar cotizaciones, productos o clientes..." class="w-full bg-slate-800 text-sm text-gray-200 rounded-full pl-10 pr-4 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500 border border-slate-700">
            </div>
        </div>

        <div class="flex items-center space-x-4">
            <span class="font-bold tracking-wider text-sm md:text-base text-blue-400">KOPION CRM</span>
            @auth
                <span class="hidden md:inline text-xs text-gray-300 bg-slate-800 px-2.5 py-1 rounded border border-slate-700">{{ Auth::user()->name }}</span>
            @endauth
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
            <li><a href="{{ route('orders.index') }}" class="block px-3 py-2 rounded bg-blue-600 font-semibold text-sm">📊 Dashboard (Inicio)</a></li>
            <li><a href="{{ route('customers.index') }}" class="block px-3 py-2 rounded hover:bg-slate-700 text-sm text-gray-300">👥 Gestión de Clientes</a></li>
            <li><a href="{{ route('products.index') }}" class="block px-3 py-2 rounded hover:bg-slate-700 text-sm text-gray-300">📦 Catálogo e Inventario</a></li>
        </ul>
    </div>

    <!-- Contenido Principal -->
    <div class="container mx-auto px-4 py-6">

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative mb-6 shadow-sm">
                <span class="font-bold">{{ session('success') }}</span>
            </div>
        @endif

        <!-- TARJETAS DE MÉTRICAS -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            
            <!-- Ventas del Día -->
            <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                <h3 class="text-xs font-bold uppercase text-gray-500 tracking-wider">Ventas del Día</h3>
                <p class="text-2xl font-black text-gray-900 mt-2">$ {{ number_format($todaySales ?? 0, 2) }}</p>
                <p class="text-xs text-gray-400 mt-2 font-semibold">Sin ventas registradas hoy</p>
            </div>

            <!-- Pedidos en Proceso -->
            <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                <h3 class="text-xs font-bold uppercase text-gray-500 tracking-wider">Pedidos en Proceso</h3>
                <p class="text-2xl font-black text-blue-600 mt-2">Pendientes: {{ $pendingOrdersCount ?? 0 }}</p>
                <p class="text-xs text-gray-400 mt-2">Sincronizado con cotizaciones</p>
            </div>

            <!-- Alertas de Inventario -->
            <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                <h3 class="text-xs font-bold uppercase text-gray-500 tracking-wider">Alertas de Inventario</h3>
                <div class="mt-4 text-center py-2">
                    <p class="text-xs text-gray-400 font-medium">Inventario al corriente / Sin catálogo cargado</p>
                </div>
            </div>

            <!-- Productos Más Vendidos -->
            <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                <h3 class="text-xs font-bold uppercase text-gray-500 tracking-wider">Productos Más Vendidos</h3>
                <div class="mt-4 text-center py-2">
                    <p class="text-xs text-gray-400 font-medium">Sin datos de ventas aún</p>
                </div>
            </div>

        </div>

        <!-- TABLA COMPLETA DE COTIZACIONES Y PROSPECTOS PENDIENTES -->
        <div class="bg-white rounded-xl shadow-md border border-gray-200 p-6">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h2 class="text-xl font-extrabold text-gray-800">Cotizaciones y prospectos pendientes:</h2>
                    <p class="text-xs text-gray-500">Listado general de pedidos creados en el sistema</p>
                </div>
                
                <button @click="openModal = true" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs px-4 py-2.5 rounded-lg shadow transition flex items-center">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Nueva Cotización
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200 text-xs font-bold text-gray-500 uppercase tracking-wider">
                            <th class="py-3 px-4">Folio / Cliente</th>
                            <th class="py-3 px-4">Tipo de Orden</th>
                            <th class="py-3 px-4">Precio ($)</th>
                            <th class="py-3 px-4">Interés</th>
                            <th class="py-3 px-4">Fecha Creación</th>
                            <th class="py-3 px-4">Estatus</th>
                            <th class="py-3 px-4 text-center">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm">
                        @forelse($orders as $order)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="py-3 px-4 font-bold text-gray-800">
                                    <span class="text-indigo-600 font-extrabold block text-xs">#{{ $order->invoice_number ?? $order->order_number }}</span>
                                    {{ $order->customer->company_name ?? ($order->customer->name ?? 'Cliente General') }}
                                </td>
                                <td class="py-3 px-4 text-gray-600">{{ $order->order_type }}</td>
                                <td class="py-3 px-4 font-bold text-gray-900">${{ number_format($order->total, 2) }}</td>
                                <td class="py-3 px-4">
                                    <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-emerald-100 text-emerald-700 border border-emerald-200">
                                        {{ $order->interest_level ?? 'MEDIO' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-gray-500 text-xs">
                                    {{ $order->created_at ? $order->created_at->format('d/m/Y H:i') : '' }}
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full bg-blue-50 text-blue-600 border border-blue-200">
                                        {{ ucfirst($order->status ?? 'completado') }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <a href="#" class="inline-flex items-center bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 px-3 py-1.5 rounded-lg text-xs font-bold transition shadow-sm" title="Cargar al Punto de Venta">
                                        🛒 Carrito
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-gray-400 text-sm">
                                    No hay cotizaciones registradas todavía. ¡Crea la primera con el botón de arriba!
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- MODAL DE NUEVA COTIZACIÓN -->
    <div x-show="openModal" class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50 flex items-center justify-center p-4" x-cloak>
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6 relative">
            <div class="flex justify-between items-center pb-3 border-b border-gray-100">
                <h3 class="text-lg font-bold text-gray-800">Registrar Nueva Cotización</h3>
                <button @click="openModal = false" class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
            </div>

            <form action="{{ route('orders.store') }}" method="POST" class="space-y-4 mt-4">
                @csrf
                
                <!-- Errores de validación -->
                @if ($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-3 py-2 rounded-lg text-xs">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase">Folio de Cotización</label>
                    <input type="text" value="{{ $suggestedFolio }}" disabled class="mt-1 w-full bg-gray-100 border border-gray-200 rounded-lg p-2 text-sm font-bold text-gray-600 cursor-not-allowed">
                    <input type="hidden" name="invoice_number" value="{{ $suggestedFolio }}">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase">Seleccionar Cliente</label>
                    <select name="customer_id" required class="mt-1 w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-indigo-500">
                        <option value="">-- Elige un cliente --</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->company_name ?? $customer->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase">Concepto / Servicio Inicial</label>
                    <input type="text" name="order_type" placeholder="Ej. Impresiones, Engargolados, Copias..." class="mt-1 w-full border border-gray-300 rounded-lg p-2 text-sm">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase">Nivel de Interés del Cliente</label>
                    <select name="interest_level" class="mt-1 w-full border border-gray-300 rounded-lg p-2 text-sm">
                        <option value="ALTO">ALTO 🔥</option>
                        <option value="MEDIO" selected>MEDIO ⚡</option>
                        <option value="BAJO">BAJO 💤</option>
                    </select>
                </div>

                <div class="pt-3 flex space-x-2">
                    <button type="button" @click="openModal = false" class="w-1/2 bg-gray-200 text-gray-700 py-2.5 rounded-lg text-sm font-semibold">Cancelar</button>
                    <button type="submit" class="w-1/2 bg-indigo-600 text-white py-2.5 rounded-lg text-sm font-semibold hover:bg-indigo-700 transition">Generar Cotización</button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>
