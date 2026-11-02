@extends('layouts.app')

@section('content')
<div class="bg-white rounded-xl shadow-md border border-gray-200 p-6"
     x-data="posApp({{ json_encode($products) }}, {{ json_encode($order) }}, {{ $isReadOnly ? 'true' : 'false' }})">
    
    <!-- ENCABEZADO -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center pb-4 mb-6 border-b border-gray-100 gap-4">
        <div>
            <h2 class="text-xl font-extrabold text-gray-800">Punto de Venta / Carrito de Cotización</h2>
            <p class="text-xs text-gray-500">
                Folio: <span class="font-bold text-indigo-600">#{{ $order->invoice_number ?? $order->order_number }}</span>
                @if($isReadOnly)
                    <span class="ml-2 bg-red-100 text-red-700 px-2 py-0.5 rounded text-[10px] font-bold">VENTA COMPLETADA (ESTÁTICA)</span>
                @endif
            </p>
        </div>

        <a href="{{ route('orders.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs px-4 py-2 rounded-lg transition border border-gray-300">
            ← Volver a Órdenes
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- COLUMNA IZQUIERDA: CATÁLOGO -->
        <div class="lg:col-span-7 space-y-4" x-show="!isReadOnly">
            <div class="relative">
                <input type="text" x-model="search" placeholder="🔍 Buscar Producto o Servicio..." 
                       class="w-full bg-gray-50 border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500">
            </div>

            <!-- FILTROS -->
            <div class="flex items-center space-x-2 overflow-x-auto pb-2">
                <button @click="selectedCategory = ''" :class="selectedCategory === '' ? 'bg-indigo-600 text-white font-bold' : 'bg-gray-100 text-gray-600'" class="px-3.5 py-1.5 rounded-full text-xs transition border">Todas</button>
                @foreach($categories as $cat)
                    <button @click="selectedCategory = '{{ $cat }}'" :class="selectedCategory === '{{ $cat }}' ? 'bg-indigo-600 text-white font-bold' : 'bg-gray-100 text-gray-600'" class="px-3.5 py-1.5 rounded-full text-xs transition border">{{ $cat }}</button>
                @endforeach
            </div>

            <!-- GRID PRODUCTOS -->
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 max-h-[500px] overflow-y-auto p-1">
                <template x-for="product in filteredProducts" :key="product.id">
                    <div class="bg-white border border-gray-200 rounded-xl p-3 flex flex-col justify-between hover:border-indigo-400 transition">
                        <div>
                            <div class="flex justify-between items-center">
                                <span class="text-[10px] font-bold text-indigo-600 uppercase" x-text="product.code"></span>
                                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded" :class="product.stock <= 5 ? 'bg-red-50 text-red-600' : 'bg-gray-100 text-gray-600'">
                                    Stock: <span x-text="product.stock"></span>
                                </span>
                            </div>
                            <h4 class="font-bold text-gray-800 text-xs mt-1 line-clamp-2" x-text="product.name"></h4>
                            <p class="text-sm font-extrabold text-gray-900 mt-2">$<span x-text="parseFloat(product.price).toFixed(2)"></span></p>
                        </div>
                        <button @click="addToCart(product)" class="mt-3 w-full bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold py-1.5 rounded-lg transition">+ Agregar</button>
                    </div>
                </template>
            </div>
        </div>

        <!-- COLUMNA DERECHA: RESUMEN Y CARRITO -->
        <div :class="isReadOnly ? 'lg:col-span-12' : 'lg:col-span-5'" class="bg-gray-50 border border-gray-200 rounded-xl p-4 flex flex-col justify-between space-y-4">
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Cliente:</label>
                    <select x-model="selectedCustomerId" :disabled="isReadOnly" class="w-full bg-white border border-gray-300 rounded-lg p-2 text-sm">
                        @foreach($customers as $cust)
                            <option value="{{ $cust->id }}">{{ ($cust->company_name && strtoupper($cust->company_name) !== 'NA') ? $cust->company_name : $cust->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- TABLA CARRITO -->
                <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm max-h-[300px] overflow-y-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-100 border-b text-gray-600 uppercase font-bold">
                            <tr>
                                <th class="p-2.5">Cant</th>
                                <th class="p-2.5">Descripción</th>
                                <th class="p-2.5 text-right">Subtotal</th>
                                <th class="p-2.5 text-center" x-show="!isReadOnly"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <template x-for="(item, index) in cart" :key="index">
                                <tr>
                                    <td class="p-2">
                                        <input type="number" min="1" :max="item.type !== 'SERVICIO' ? item.stock : 9999" x-model.number="item.quantity" @change="validateStockAndTotals(item)" :disabled="isReadOnly" class="w-12 border rounded p-1 text-center font-bold">
                                    </td>
                                    <td class="p-2">
                                        <p class="font-bold text-gray-800" x-text="item.name"></p>
                                        <p class="text-[10px] text-gray-400">$<span x-text="parseFloat(item.price).toFixed(2)"></span> c/u</p>
                                    </td>
                                    <td class="p-2 text-right font-bold text-gray-900">
                                        $<span x-text="(item.price * item.quantity).toFixed(2)"></span>
                                    </td>
                                    <td class="p-2 text-center" x-show="!isReadOnly">
                                        <button @click="removeFromCart(index)" class="text-red-500 hover:text-red-700 font-bold text-sm">&times;</button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Notas del Servicio:</label>
                    <textarea x-model="serviceNotes" :disabled="isReadOnly" rows="2" class="w-full bg-white border rounded-lg p-2 text-xs"></textarea>
                </div>
            </div>

            <!-- RESUMEN DE PAGOS CON DESGLOSE DE IVA -->
            <div class="border-t border-gray-200 pt-4 space-y-3">
                <div class="bg-white border border-gray-200 rounded-xl p-3 space-y-1.5 text-xs">
                    <div class="flex justify-between text-gray-600">
                        <span>Subtotal (Neto):</span>
                        <span class="font-bold">$<span x-text="subtotal.toFixed(2)"></span></span>
                    </div>
                    <div class="flex justify-between text-gray-600">
                        <span>IVA (16% Inc.):</span>
                        <span class="font-bold">$<span x-text="iva.toFixed(2)"></span></span>
                    </div>
                    <div class="flex justify-between text-gray-600 items-center" x-show="!isReadOnly">
                        <span>Descuento ($):</span>
                        <input type="number" step="0.01" min="0" x-model.number="discount" @input="updateTotals()" class="w-20 border rounded px-1.5 py-0.5 text-right font-bold">
                    </div>
                    <div class="flex justify-between text-base font-extrabold text-gray-900 border-t pt-1.5">
                        <span>TOTAL:</span>
                        <span class="text-indigo-600">$<span x-text="total.toFixed(2)"></span></span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2" x-show="!isReadOnly">
                    <button @click="submitPos('pendiente')" class="w-full bg-amber-500 text-white font-bold text-xs py-2.5 rounded-lg shadow">Guardar Pendiente</button>
                    <button @click="submitPos('completado')" class="w-full bg-emerald-600 text-white font-bold text-xs py-2.5 rounded-lg shadow">Cobrar / Finalizar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function posApp(products, initialOrder, isReadOnly) {
    return {
        products: products,
        search: '',
        selectedCategory: '',
        selectedCustomerId: initialOrder ? initialOrder.customer_id : '',
        serviceNotes: initialOrder ? initialOrder.service_notes : '',
        cart: [],
        discount: initialOrder ? parseFloat(initialOrder.discount || 0) : 0,
        subtotal: 0,
        iva: 0,
        total: 0,
        isReadOnly: isReadOnly,

        init() {
            if (initialOrder && initialOrder.items) {
                this.cart = initialOrder.items.map(item => {
                    const prodMatch = products.find(p => p.id === item.product_id);
                    return {
                        id: item.product_id,
                        name: item.product ? item.product.name : 'Producto',
                        price: parseFloat(item.unit_price),
                        quantity: parseInt(item.quantity),
                        stock: prodMatch ? prodMatch.stock : 9999,
                        type: prodMatch ? prodMatch.type : 'PRODUCTO'
                    };
                });
            }
            this.updateTotals();
        },

        get filteredProducts() {
            return this.products.filter(p => {
                const matchesSearch = p.name.toLowerCase().includes(this.search.toLowerCase()) || p.code.toLowerCase().includes(this.search.toLowerCase());
                const matchesCategory = this.selectedCategory === '' || p.category === this.selectedCategory;
                return matchesSearch && matchesCategory;
            });
        },

        addToCart(product) {
            if (this.isReadOnly) return;
            
            // Validación estricta para no rebasar el stock disponible
            if (product.type !== 'SERVICIO') {
                const currentInCart = this.cart.find(item => item.id === product.id)?.quantity || 0;
                if (currentInCart + 1 > product.stock) {
                    alert('¡Stock insuficiente! No hay suficientes unidades en inventario.');
                    return;
                }
            }

            const existing = this.cart.find(item => item.id === product.id);
            if (existing) {
                existing.quantity++;
            } else {
                this.cart.push({ 
                    id: product.id, 
                    name: product.name, 
                    price: parseFloat(product.price), 
                    quantity: 1, 
                    stock: product.stock, 
                    type: product.type 
                });
            }
            this.updateTotals();
        },

        validateStockAndTotals(item) {
            if (item.type !== 'SERVICIO' && item.quantity > item.stock) {
                alert('La cantidad supera el stock disponible (' + item.stock + ').');
                item.quantity = item.stock;
            }
            if (item.quantity < 1 || isNaN(item.quantity)) {
                item.quantity = 1;
            }
            this.updateTotals();
        },

        removeFromCart(index) {
            if (this.isReadOnly) return;
            this.cart.splice(index, 1);
            this.updateTotals();
        },

        updateTotals() {
            const rawSubtotal = this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            this.total = Math.max(0, rawSubtotal - this.discount);
            this.subtotal = this.total / 1.16;
            this.iva = this.total - this.subtotal;
        },

        submitPos(status) {
            if (this.cart.length === 0 || this.isReadOnly) return;

            fetch(`/orders/${initialOrder.id}/pos`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    customer_id: this.selectedCustomerId,
                    cart: this.cart,
                    discount: this.discount,
                    service_notes: this.serviceNotes,
                    status: status
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('¡Operación realizada con éxito!');
                    window.location.href = "{{ route('orders.index') }}";
                } else {
                    alert('Error: ' + data.message);
                }
            });
        }
    }
}
</script>
@endsection
