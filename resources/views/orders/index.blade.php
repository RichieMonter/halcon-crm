@extends('layouts.app')

@section('content')
    <!-- TARJETAS DE MÉTRICAS ESTILO FIGMA -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        
        <!-- 1. Ventas del Día -->
        <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex flex-col justify-between">
            <div>
                <h3 class="text-xs font-bold uppercase text-gray-500 tracking-wider">Ventas del día</h3>
                <p class="text-2xl font-black text-gray-900 mt-2">$ {{ number_format($todaySales ?? 0, 2) }}</p>
            </div>
            <div class="mt-3 flex items-center justify-between text-xs">
                @if($percentageVsYesterday > 0)
                    <span class="text-emerald-600 font-bold">▲ +{{ number_format($percentageVsYesterday, 1) }}% vs ayer</span>
                @else
                    <span class="text-gray-400">±0% vs ayer</span>
                @endif
                <span class="text-gray-500 font-medium">Completados: {{ $completedOrdersCount ?? 0 }}</span>
            </div>
        </div>

        <!-- 2. Pedidos en Proceso -->
        <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex flex-col justify-between">
            <div>
                <h3 class="text-xs font-bold uppercase text-gray-500 tracking-wider">Pedidos en proceso</h3>
                <p class="text-2xl font-black text-blue-600 mt-2">Pendientes: {{ $pendingOrdersCount ?? 0 }}</p>
            </div>
            <div class="mt-3 text-[11px] text-gray-500 space-y-0.5">
                <p>• Sincronizado con cotizaciones</p>
            </div>
        </div>

        <!-- 3. Alertas de Inventario (Diseño en cajitas limpias sin que se corten los nombres) -->
        <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex justify-between items-center mb-2">
                    <h3 class="text-xs font-bold uppercase text-gray-500 tracking-wider">Alertas de inventario</h3>
                    <span class="text-[10px] font-bold bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full">
                        Total: {{ isset($lowStockProducts) ? $lowStockProducts->count() : 0 }}
                    </span>
                </div>
                
                <div class="space-y-2 max-h-[100px] overflow-y-auto pr-1">
                    @forelse($lowStockProducts ?? [] as $prod)
                        <div class="bg-gray-50 p-2 rounded-lg border border-gray-100">
                            <div class="flex justify-between text-xs text-gray-800 font-bold mb-1">
                                <span class="truncate pr-2" title="{{ $prod->name }}">{{ $prod->name }}</span>
                                <span class="text-red-600 whitespace-nowrap">{{ $prod->stock }} pzas</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-1.5">
                                <div class="bg-red-500 h-1.5 rounded-full" style="width: {{ min(100, max(15, ($prod->stock / 10) * 100)) }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-emerald-600 font-medium py-3 text-center">¡Inventario al corriente!</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- 4. Productos Más Vendidos (Diseño en cajitas con nombres completos) -->
        <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex flex-col justify-between">
            <div>
                <h3 class="text-xs font-bold uppercase text-gray-500 tracking-wider mb-2">Productos más vendidos</h3>
                
                <div class="space-y-2 max-h-[100px] overflow-y-auto pr-1">
                    @if(isset($topProductsList) && count($topProductsList) > 0)
                        @foreach($topProductsList->take(3) as $top)
                            <div class="bg-gray-50 p-2 rounded-lg border border-gray-100">
                                <div class="flex justify-between text-xs text-gray-800 font-bold mb-1">
                                    <span class="truncate pr-2" title="{{ $top->product->name ?? 'Producto' }}">{{ $top->product->name ?? 'Producto' }}</span>
                                    <span class="text-indigo-600 whitespace-nowrap">{{ $top->total_sold }} vts</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-1.5 overflow-hidden">
                                    <div class="bg-indigo-600 h-1.5 rounded-full" style="width: {{ min(100, ($top->total_sold * 8)) }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-xs text-gray-400 text-center py-4">Sin datos de ventas aún</p>
                    @endif
                </div>
            </div>
        </div>

    </div>

    <!-- TABLA COMPLETA DE COTIZACIONES -->
    <div class="bg-white rounded-xl shadow-md border border-gray-200 p-6" x-data="{ openModal: false, editModal: false, currentOrder: {} }">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
            <div>
                <h2 class="text-xl font-extrabold text-gray-800">Cotizaciones y prospectos pendientes:</h2>
                <p class="text-xs text-gray-500">Listado general de pedidos creados en el sistema</p>
            </div>
            
            <button @click="openModal = true" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs px-4 py-2.5 rounded-lg shadow transition flex items-center">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Nueva Cotización
            </button>
        </div>

        <!-- BUSCADOR MODULAR DE ÓRDENES -->
        <form action="{{ route('orders.index') }}" method="GET" class="mb-6 flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="🔍 Filtrar por folio (COT-0001), cliente o tipo de orden..." 
                   class="w-full md:w-1/3 bg-gray-50 border border-gray-300 rounded-lg p-2.5 text-xs focus:ring-2 focus:ring-indigo-500">
            <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white px-4 py-2.5 rounded-lg text-xs font-bold transition">Filtrar</button>
            @if(request('search'))
                <a href="{{ route('orders.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-3 py-2.5 rounded-lg text-xs font-bold flex items-center">Limpiar</a>
            @endif
        </form>

        <div class="overflow-x-auto max-h-[500px] overflow-y-auto">
            <table class="w-full text-left border-collapse">
                <thead class="sticky top-0 bg-gray-50 z-10">
                    <tr class="border-b border-gray-200 text-xs font-bold text-gray-500 uppercase tracking-wider">
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
                                <a href="{{ route('orders.pos', $order->id) }}" class="text-indigo-600 font-extrabold block text-xs hover:underline">
                                    #{{ $order->invoice_number ?? $order->order_number }}
                                </a>
                                {{ ($order->customer->company_name && strtoupper($order->customer->company_name) !== 'NA') ? $order->customer->company_name : ($order->customer->name ?? 'Cliente General') }}
                            </td>
                            <td class="py-3 px-4 text-gray-600">{{ $order->order_type }}</td>
                            <td class="py-3 px-4 font-bold text-gray-900">${{ number_format($order->total, 2) }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2.5 py-1 text-xs font-bold rounded-full 
                                    {{ $order->interest_level == 'ALTO' ? 'bg-red-100 text-red-700 border border-red-200' : ($order->interest_level == 'MEDIO' ? 'bg-amber-100 text-amber-700 border border-amber-200' : 'bg-emerald-100 text-emerald-700 border border-emerald-200') }}">
                                    {{ $order->interest_level }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-gray-500 text-xs">
                                {{ $order->created_at ? $order->created_at->format('d/m/Y H:i') : '' }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full 
                                    {{ strtolower($order->status) == 'completado' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : (strtolower($order->status) == 'cancelado' ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-blue-50 text-blue-600 border border-blue-200') }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center space-x-1">
                                @if(strtolower($order->status) !== 'completado')
                                    <button @click="editModal = true; currentOrder = {
                                        id: '{{ $order->id }}',
                                        customer_id: '{{ $order->customer_id }}',
                                        order_type: '{{ $order->order_type }}',
                                        interest_level: '{{ $order->interest_level }}',
                                        status: '{{ $order->status }}',
                                        invoice_number: '{{ $order->invoice_number }}'
                                    }" class="inline-flex items-center bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 px-2.5 py-1.5 rounded-lg text-xs font-bold transition shadow-sm" title="Editar Cotización">
                                        ✏️ Editar
                                    </button>
                                @else
                                    <span class="inline-block px-2.5 py-1.5 text-[11px] font-bold text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed">Bloqueada</span>
                                @endif

                                <a href="{{ route('orders.pos', $order->id) }}" class="inline-flex items-center {{ strtolower($order->status) === 'completado' ? 'bg-slate-100 text-slate-700 border-slate-300' : 'bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200' }} px-2.5 py-1.5 rounded-lg text-xs font-bold transition shadow-sm" title="{{ strtolower($order->status) === 'completado' ? 'Ver Detalle de Venta' : 'Cargar al Punto de Venta' }}">
                                    🛒 {{ strtolower($order->status) === 'completado' ? 'Ver Venta' : 'Carrito' }}
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

        <!-- MODAL DE NUEVA COTIZACIÓN -->
        <div x-show="openModal" class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50 flex items-center justify-center p-4" x-cloak>
            <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6 relative">
                <div class="flex justify-between items-center pb-3 border-b border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800">Registrar Nueva Cotización</h3>
                    <button @click="openModal = false" class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
                </div>

                <form action="{{ route('orders.store') }}" method="POST" class="space-y-4 mt-4">
                    @csrf
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
                                <option value="{{ $customer->id }}">{{ ($customer->company_name && strtoupper($customer->company_name) !== 'NA') ? $customer->company_name : $customer->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase">Concepto / Servicio Inicial</label>
                        <input type="text" name="order_type" placeholder="Ej. Impresiones, Engargolados..." class="mt-1 w-full border border-gray-300 rounded-lg p-2 text-sm">
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

        <!-- MODAL DE EDICIÓN DE COTIZACIÓN -->
        <div x-show="editModal" class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50 flex items-center justify-center p-4" x-cloak>
            <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6 relative">
                <div class="flex justify-between items-center pb-3 border-b border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800" x-text="'Editar Cotización #' + currentOrder.invoice_number"></h3>
                    <button @click="editModal = false" class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
                </div>

                <form :action="'/orders/' + currentOrder.id" method="POST" class="space-y-4 mt-4">
                    @csrf
                    @method('PUT')
                    
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase">Seleccionar Cliente</label>
                        <select name="customer_id" x-model="currentOrder.customer_id" required class="mt-1 w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-indigo-500">
                            <option value="">-- Elige un cliente --</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}">{{ ($customer->company_name && strtoupper($customer->company_name) !== 'NA') ? $customer->company_name : $customer->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase">Concepto / Servicio</label>
                        <input type="text" name="order_type" x-model="currentOrder.order_type" class="mt-1 w-full border border-gray-300 rounded-lg p-2 text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase">Nivel de Interés del Cliente</label>
                        <select name="interest_level" x-model="currentOrder.interest_level" class="mt-1 w-full border border-gray-300 rounded-lg p-2 text-sm">
                            <option value="ALTO">ALTO 🔥</option>
                            <option value="MEDIO">MEDIO ⚡</option>
                            <option value="BAJO">BAJO 💤</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase">Estatus</label>
                        <select name="status" x-model="currentOrder.status" class="mt-1 w-full border border-gray-300 rounded-lg p-2 text-sm">
                            <option value="pendiente">Pendiente</option>
                            <option value="completado">Completado</option>
                            <option value="cancelado">Cancelado</option>
                        </select>
                    </div>

                    <div class="pt-3 flex space-x-2">
                        <button type="button" @click="editModal = false" class="w-1/2 bg-gray-200 text-gray-700 py-2.5 rounded-lg text-sm font-semibold">Cancelar</button>
                        <button type="submit" class="w-1/2 bg-amber-600 text-white py-2.5 rounded-lg text-sm font-semibold hover:bg-amber-700 transition">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection