@extends(($isPortal ?? false) ? 'portal.layout' : 'layouts.app')

@section('content')
@php
    $isPortal = $isPortal ?? false;
    $submitUrl = $isPortal
        ? ($order->exists ? route('portal.orders.update', $order) : route('portal.orders.store'))
        : route('orders.pos.update', $order);
@endphp
<div class="bg-white rounded-xl shadow-md border border-gray-200 p-6"
     x-data="posApp(@js($products), @js($cartOrder ?? $order), @js($isReadOnly), @js($printers), @js($recommendations), @js($isPortal), @js($submitUrl))">
    
    <!-- ENCABEZADO -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center pb-4 mb-6 border-b border-gray-100 gap-4">
        <div>
            <h2 class="text-xl font-extrabold text-gray-800">{{ $isPortal ? 'Carrito de cotización' : 'Punto de Venta / Carrito de Cotización' }}</h2>
            <p class="text-xs text-gray-500">
                <span class="font-bold text-indigo-600">{{ $order->exists ? 'Folio: #'.($order->invoice_number ?? $order->order_number) : 'Nueva cotización' }}</span>
                @if($isReadOnly)
                    <span class="ml-2 bg-slate-100 text-slate-700 px-2 py-0.5 rounded text-[10px] font-bold">{{ ucfirst($order->status) }}</span>
                @endif
            </p>
        </div>

        <a href="{{ $isPortal ? route('portal.home').'#mis-pedidos' : route('orders.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs px-4 py-2 rounded-lg transition border border-gray-300">
            {{ $isPortal ? '← Volver a Mis pedidos' : '← Volver a Órdenes' }}
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- COLUMNA IZQUIERDA: CATÁLOGO -->
        <div class="min-w-0 space-y-4 lg:col-span-7" x-show="!isReadOnly">
            <div class="relative">
                <input type="text" x-model="search" placeholder="🔍 Buscar Producto o Servicio..." 
                       class="w-full bg-gray-50 border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500">
            </div>

            <!-- FILTROS -->
            <div class="flex items-center space-x-2 overflow-x-auto pb-2">
                <button @click="selectedCategory = ''" :class="selectedCategory === '' ? 'bg-indigo-600 text-white font-bold' : 'bg-gray-100 text-gray-600'" class="px-3.5 py-1.5 rounded-full text-xs transition border">Todas</button>
                @foreach($categories as $cat)
                    <button @click="selectedCategory = @js($cat)" :class="selectedCategory === @js($cat) ? 'bg-indigo-600 text-white font-bold' : 'bg-gray-100 text-gray-600'" class="px-3.5 py-1.5 rounded-full text-xs transition border">{{ $cat }}</button>
                @endforeach
            </div>

            <!-- GRID PRODUCTOS -->
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 max-h-[500px] overflow-y-auto p-1">
                <template x-for="product in filteredProducts" :key="product.id">
                    <div class="bg-white border border-gray-200 rounded-xl p-3 flex flex-col justify-between hover:border-indigo-400 transition">
                        <div>
                            <div class="flex justify-between items-center">
                                <span class="text-[10px] font-bold text-indigo-600 uppercase" x-text="product.code"></span>
                                <span x-show="!isPortal || product.type === 'PRODUCTO'" class="text-[10px] font-bold px-1.5 py-0.5 rounded" :class="product.stock <= 5 ? 'bg-red-50 text-red-600' : 'bg-gray-100 text-gray-600'">
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
        <div :class="isReadOnly ? 'lg:col-span-12' : 'lg:col-span-5'" class="min-w-0 bg-gray-50 border border-gray-200 rounded-xl p-4 flex flex-col justify-between space-y-4">
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Cliente:</label>
                    <select x-model="selectedCustomerId" @change="clearSuggestedDiscount()" :disabled="isReadOnly || isPortal" class="w-full bg-white border border-gray-300 rounded-lg p-2 text-sm">
                        @foreach($customers as $cust)
                            <option value="{{ $cust->id }}">{{ ($cust->company_name && strtoupper($cust->company_name) !== 'NA') ? $cust->company_name : $cust->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- TABLA CARRITO -->
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm max-h-[300px] overflow-auto">
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
                            <tr x-show="cart.length === 0"><td colspan="4" class="p-3 text-gray-500">Agrega productos o servicios para cotizar.</td></tr>
                            <template x-for="(item, index) in cart" :key="index">
                                <tr>
                                    <td class="p-2">
                                        <input type="number" min="1" :max="item.type !== 'SERVICIO' ? item.stock : 9999" x-model.number="item.quantity" @change="validateStockAndTotals(item)" :disabled="isReadOnly" class="w-12 border rounded p-1 text-center font-bold">
                                    </td>
                                    <td class="p-2">
                                        <p class="font-bold text-gray-800" x-text="item.name"></p>
                                        <p class="text-[10px] text-gray-400">$<span x-text="parseFloat(item.price).toFixed(2)"></span> c/u</p>
                                        @unless ($isPortal)
                                        <div x-show="item.isPrinting" class="mt-2 space-y-1">
                                            <label class="block text-xs">Impresora
                                                <select x-model="item.printer" @change="item.print_mode = printers[item.printer]?.colors.length === 1 ? 'mono' : ''" :disabled="isReadOnly" class="mt-1 block w-full rounded border p-1">
                                                    <option value="">Selecciona una impresora</option>
                                                    <template x-for="(printer, key) in printers" :key="key">
                                                        <option :value="key" :selected="key === item.printer" x-text="printer.name"></option>
                                                    </template>
                                                </select>
                                            </label>
                                            <label x-show="printers[item.printer]?.colors.length > 1" class="block text-xs">Tipo de impresión
                                                <select x-model="item.print_mode" :disabled="isReadOnly" class="mt-1 block w-full rounded border p-1">
                                                    <option value="">Selecciona un tipo</option>
                                                    <option value="mono">Blanco y negro</option>
                                                    <option value="color">A color</option>
                                                </select>
                                            </label>
                                            <p class="text-[10px] text-slate-600">La cantidad equivale a páginas impresas.</p>
                                            <button type="button" x-show="!isReadOnly" @click="addToCart(products.find(product => product.id === item.id), true)" class="text-[10px] font-semibold text-indigo-700 hover:underline">+ Otra línea de impresión</button>
                                        </div>
                                        @endunless
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

                @unless ($isPortal)
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Notas del Servicio:</label>
                    <textarea x-model="serviceNotes" :disabled="isReadOnly" rows="2" class="w-full bg-white border rounded-lg p-2 text-xs"></textarea>
                </div>
                @endunless
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
                    @unless ($isPortal)
                    <div class="flex justify-between text-gray-600 items-center" x-show="!isReadOnly">
                        <label for="discount-percent">Descuento (%)</label>
                        <input id="discount-percent" type="number" step="0.01" min="0" max="100" x-model.number="discountPercent" @input="suggestionApplied = false; updateTotals()" @change="discountPercent = Math.min(100, Math.max(0, Number(discountPercent) || 0)); updateTotals()" class="w-20 border rounded px-1.5 py-0.5 text-right font-bold">
                    </div>
                    <div class="flex justify-between text-gray-600" x-show="discountAmount > 0">
                        <span>Importe descontado</span>
                        <span class="font-bold">−$<span x-text="discountAmount.toFixed(2)"></span></span>
                    </div>
                    @endunless
                    <div class="flex justify-between text-base font-extrabold text-gray-900 border-t pt-1.5">
                        <span>TOTAL:</span>
                        <span class="text-indigo-600">$<span x-text="total.toFixed(2)"></span></span>
                    </div>
                </div>

                @unless ($isReadOnly)
                    @unless ($isPortal)
                    <aside x-show="customerRecommendations.length > 0" x-cloak class="space-y-2 rounded-lg border border-amber-200 bg-amber-50 p-3">
                        <h3 class="text-sm font-bold text-amber-900"><span aria-hidden="true">💡</span> Descuentos sugeridos</h3>
                        <template x-for="suggestion in customerRecommendations" :key="suggestion.label">
                            <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-amber-900">
                                <span x-text="suggestion.label + ' · ' + suggestion.percent + ' %'"></span>
                                <button type="button" @click="applySuggestedDiscount(suggestion.percent)" class="rounded border border-amber-300 bg-white px-2 py-1 font-semibold">Aplicar</button>
                            </div>
                        </template>
                    </aside>
                    @endunless

                    <label for="payment-method" class="block text-sm font-semibold text-slate-800">Método de pago
                        <select id="payment-method" x-model="paymentMethod" class="mt-1 block w-full rounded-lg border border-slate-300 bg-white p-2 text-sm">
                            <option value="">{{ $isPortal ? 'Selecciona un método de pago' : 'Selecciona al finalizar la venta' }}</option>
                            @foreach ($paymentMethods as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                @else
                    <p class="text-sm text-slate-700"><strong>Método de pago:</strong> {{ $paymentMethods[$order->payment_method] ?? 'Sin registro' }}</p>
                @endunless

                @if ($isPortal)
                    @unless ($isReadOnly)
                        <button type="button" @click="submitQuote()" :disabled="saving" class="w-full rounded-lg bg-indigo-600 py-2.5 text-sm font-semibold text-white disabled:opacity-50">Guardar cotización</button>
                    @endunless
                @else
                <div class="grid grid-cols-2 gap-2" x-show="!isReadOnly">
                    <button @click="submitPos('pendiente')" class="w-full bg-amber-500 text-white font-bold text-xs py-2.5 rounded-lg shadow">Guardar Pendiente</button>
                    <button @click="submitPos('completado')" class="w-full bg-emerald-600 text-white font-bold text-xs py-2.5 rounded-lg shadow">Cobrar / Finalizar</button>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
function posApp(products, initialOrder, isReadOnly, printers, recommendations, isPortal, submitUrl) {
    return {
        products: products,
        printers: printers,
        search: '',
        selectedCategory: '',
        selectedCustomerId: initialOrder.customer_id ?? @js($isPortal ? $customers->first()->id : ''),
        paymentMethod: initialOrder.payment_method ?? '',
        saving: false,
        isPortal,
        suggestionApplied: false,
        serviceNotes: initialOrder ? initialOrder.service_notes : '',
        cart: [],
        discountPercent: Number(initialOrder.discount_percent ?? (Number(initialOrder.subtotal) > 0
            ? (100 * Number(initialOrder.discount) / Number(initialOrder.subtotal)).toFixed(2)
            : 0)),
        discountAmount: 0,
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
                        name: item.product?.name ?? prodMatch?.name ?? 'Producto',
                        price: parseFloat(isPortal && !isReadOnly && prodMatch ? prodMatch.price : item.unit_price),
                        quantity: parseInt(item.quantity),
                        stock: prodMatch ? prodMatch.stock : 9999,
                        type: prodMatch ? prodMatch.type : 'PRODUCTO',
                        isPrinting: Boolean(prodMatch?.is_printing || item.printer),
                        printer: item.printer ?? '',
                        print_mode: item.print_mode ?? ''
                    };
                });
            }
            this.updateTotals();
        },

        get filteredProducts() {
            const normalize = value => (value ?? '').normalize('NFD').replace(/\p{Diacritic}/gu, '').toLowerCase();
            const terms = normalize(this.search).trim().split(/\s+/);
            return this.products.filter(p => {
                const description = normalize([p.name, p.code, p.category].join(' '));
                const matchesSearch = terms.every(term => description.includes(term));
                const matchesCategory = this.selectedCategory === '' || p.category === this.selectedCategory;
                return matchesSearch && matchesCategory;
            });
        },

        get customerRecommendations() {
            return recommendations[this.selectedCustomerId] ?? [];
        },

        applySuggestedDiscount(percent) {
            this.discountPercent = percent;
            this.suggestionApplied = true;
            this.updateTotals();
        },

        clearSuggestedDiscount() {
            if (this.suggestionApplied) {
                this.discountPercent = 0;
                this.suggestionApplied = false;
                this.updateTotals();
            }
        },

        addToCart(product, separateLine = false) {
            if (this.isReadOnly || !product) return;
            
            // Validación estricta para no rebasar el stock disponible
            if (product.type !== 'SERVICIO') {
                const currentInCart = this.cart.find(item => item.id === product.id)?.quantity || 0;
                if (currentInCart + 1 > product.stock) {
                    alert('¡Stock insuficiente! No hay suficientes unidades en inventario.');
                    return;
                }
            }

            const existing = separateLine ? null : this.cart.find(item => item.id === product.id);
            if (existing) {
                existing.quantity++;
            } else {
                this.cart.push({ 
                    id: product.id, 
                    name: product.name, 
                    price: parseFloat(product.price), 
                    quantity: 1, 
                    stock: product.stock, 
                    type: product.type,
                    isPrinting: product.is_printing,
                    printer: '',
                    print_mode: ''
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
            const subtotalCents = Math.round((rawSubtotal + Number.EPSILON) * 100);
            const percentage = Math.min(100, Math.max(0, Number(this.discountPercent) || 0));
            const discountCents = isPortal && initialOrder.discount_percent == null
                ? Math.min(subtotalCents, Math.round(Number(initialOrder.discount || 0) * 100))
                : Math.round(subtotalCents * Math.round(percentage * 100) / 10000);
            this.discountAmount = this.isReadOnly
                ? Number(initialOrder.discount || 0)
                : discountCents / 100;
            this.total = this.isReadOnly ? Number(initialOrder.total) : (subtotalCents - discountCents) / 100;
            this.subtotal = this.total / 1.16;
            this.iva = this.total - this.subtotal;
        },

        submitPos(status) {
            if (this.cart.length === 0 || this.isReadOnly) return;
            if (status === 'completado' && !this.paymentMethod) {
                alert('Selecciona el método de pago para finalizar la venta.');
                return;
            }
            if (status === 'completado' && this.cart.some(item => item.isPrinting && (!item.printer || !item.print_mode))) {
                alert('Elige la impresora y el tipo de impresión para cada servicio de impresión.');
                return;
            }

            fetch(submitUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    customer_id: this.selectedCustomerId,
                    cart: this.cart.map(item => ({
                        id: item.id,
                        quantity: item.quantity,
                        price: item.price,
                        printer: item.printer || null,
                        print_mode: item.print_mode || null
                    })),
                    discount_percent: this.discountPercent || 0,
                    payment_method: this.paymentMethod || null,
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
            })
            .catch(() => alert('No se pudo contactar al servidor para guardar la venta.'));
        },

        async submitQuote() {
            if (this.saving || this.isReadOnly) return;
            if (this.cart.length && !this.paymentMethod) {
                alert('Selecciona el método de pago para guardar la cotización.');
                return;
            }
            const quantities = Object.fromEntries(this.cart.map(item => [item.id, 0]));
            this.cart.forEach(item => quantities[item.id] += item.quantity);
            this.saving = true;
            try {
                const response = await fetch(submitUrl, {
                    method: initialOrder.id ? 'PUT' : 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': @js(csrf_token())
                    },
                    body: JSON.stringify({quantities, payment_method: this.paymentMethod})
                });
                const data = await response.json();
                if (!response.ok) throw new Error(data.message || 'No se pudo guardar la cotización.');
                window.location.href = data.redirect;
            } catch (error) {
                alert(error.message);
            } finally {
                this.saving = false;
            }
        }
    }
}
</script>
@endsection
