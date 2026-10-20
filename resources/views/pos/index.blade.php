<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Punto de Venta / Carrito (POS)') }}
        </h2>
    </x-slot>

    <div class="py-6" x-data="posSystem()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- COLUMNA IZQUIERDA: Selección de Productos / Servicios -->
                <div class="lg:col-span-2 bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Selección de Productos / Servicios</h3>
                    
                    <!-- Buscador -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Código de Barras o Nombre de Producto/Servicio:</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <input type="text" x-model="search" placeholder="Buscar producto..." class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-3 pr-12 sm:text-sm border-gray-300 rounded-md">
                        </div>
                    </div>

                    <!-- Filtros por Categoría / Tipo -->
                    <div class="flex flex-wrap gap-2 mb-6">
                        <button @click="selectedFilter = 'ALL'" :class="selectedFilter === 'ALL' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700'" class="px-3 py-1 rounded-full text-sm font-medium">Todos</button>
                        <button @click="selectedFilter = 'PRODUCTO'" :class="selectedFilter === 'PRODUCTO' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700'" class="px-3 py-1 rounded-full text-sm font-medium">Papelería</button>
                        <button @click="selectedFilter = 'SERVICIO'" :class="selectedFilter === 'SERVICIO' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700'" class="px-3 py-1 rounded-full text-sm font-medium">Servicios</button>
                    </div>

                    <!-- Cuadrícula de Productos -->
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 max-h-[500px] overflow-y-auto p-1">
                        <template x-for="product in filteredProducts" :key="product.id">
                            <div class="border rounded-lg p-4 flex flex-col justify-between hover:shadow-md transition bg-gray-50">
                                <div>
                                    <div class="h-20 bg-gray-200 rounded mb-2 flex items-center justify-center text-gray-400 text-xs">Imagen</div>
                                    <h4 class="font-semibold text-sm text-gray-800" x-text="product.name"></h4>
                                    <p class="text-indigo-600 font-bold text-sm mt-1" x-text="'$' + parseFloat(product.price).toFixed(2)"></p>
                                </div>
                                <button @click="addToCart(product)" class="mt-3 w-full bg-teal-600 text-white text-xs font-semibold py-2 rounded hover:bg-teal-700 transition">
                                    Agregar
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- COLUMNA DERECHA: Carrito y Resumen de Pago -->
                <div class="bg-white shadow-sm sm:rounded-lg p-6 flex flex-col justify-between">
                    <div>
                        <!-- Selector de Cliente -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Cliente:</label>
                            <select x-model="customerId" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                <option value="">Venta General / Mostrador</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Tabla del Carrito -->
                        <div class="border rounded-lg overflow-hidden mb-4">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-medium text-gray-500">Cant.</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-500">Descripción</th>
                                        <th class="px-3 py-2 text-right font-medium text-gray-500">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <template x-for="(item, index) in cart" :key="index">
                                        <tr>
                                            <td class="px-3 py-2">
                                                <div class="flex items-center space-x-1">
                                                    <button @click="decreaseQty(index)" class="text-gray-500 hover:text-red-600">-</button>
                                                    <span x-text="item.quantity"></span>
                                                    <button @click="increaseQty(index)" class="text-gray-500 hover:text-green-600">+</button>
                                                </div>
                                            </td>
                                            <td class="px-3 py-2" x-text="item.name"></td>
                                            <td class="px-3 py-2 text-right" x-text="'$' + (item.price * item.quantity).toFixed(2)"></td>
                                        </tr>
                                    </template>
                                    <template x-if="cart.length === 0">
                                        <tr>
                                            <td colspan="3" class="px-3 py-4 text-center text-gray-400 text-xs">El carrito está vacío</td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>

                        <!-- Detalle / Especificaciones del Servicio -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Detalle / Especificaciones del servicio:</label>
                            <textarea x-model="serviceNotes" rows="2" placeholder="Ej. Engargolado espiral negro, 150 hojas..." class="mt-1 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"></textarea>
                        </div>
                    </div>

                    <!-- Resumen de Pagos y Acciones -->
                    <div class="border-t pt-4">
                        <div class="bg-gray-50 p-3 rounded-lg mb-4 text-sm space-y-1">
                            <div class="flex justify-between">
                                <span>Subtotal:</span>
                                <span x-text="'$' + subtotal.toFixed(2)"></span>
                            </div>
                            <div class="flex justify-between">
                                <span>Descuento:</span>
                                <span><input type="number" x-model.number="discount" class="w-20 text-right text-xs py-0.5 border-gray-300 rounded"></span>
                            </div>
                            <div class="flex justify-between font-bold text-base text-gray-900 border-t pt-1">
                                <span>TOTAL:</span>
                                <span x-text="'$' + total.toFixed(2)"></span>
                            </div>
                        </div>

                        <div class="flex space-x-2">
                            <button @click="checkout('pendiente')" class="w-1/2 bg-amber-500 text-white text-xs font-semibold py-2.5 rounded hover:bg-amber-600 transition">
                                Guardar pendiente
                            </button>
                            <button @click="checkout('completado')" class="w-1/2 bg-indigo-600 text-white text-xs font-semibold py-2.5 rounded hover:bg-indigo-700 transition">
                                Cobrar / Imprimir
                            </button>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>

    <!-- Script de Alpine.js para la lógica del POS -->
    <script>
        function posSystem() {
            return {
                products: @json($products),
                search: '',
                selectedFilter: 'ALL',
                cart: [],
                customerId: '',
                serviceNotes: '',
                discount: 0,

                get filteredProducts() {
                    let temp = this.products;
                    if (this.selectedFilter !== 'ALL') {
                        temp = temp.filter(p => p.type === this.selectedFilter);
                    }
                    if (this.search.trim() !== '') {
                        temp = temp.filter(p => p.name.toLowerCase().includes(this.search.toLowerCase()));
                    }
                    return temp;
                },

                addToCart(product) {
                    let existing = this.cart.find(item => item.id === product.id);
                    if (existing) {
                        existing.quantity++;
                    } else {
                        this.cart.push({
                            id: product.id,
                            name: product.name,
                            price: parseFloat(product.price),
                            quantity: 1
                        });
                    }
                },

                increaseQty(index) {
                    this.cart[index].quantity++;
                },

                decreaseQty(index) {
                    if (this.cart[index].quantity > 1) {
                        this.cart[index].quantity--;
                    } else {
                        this.cart.splice(index, 1);
                    }
                },

                get subtotal() {
                    return this.cart.reduce((acc, item) => acc + (item.price * item.quantity), 0);
                },

                get total() {
                    let t = this.subtotal - (this.discount || 0);
                    return t > 0 ? t : 0;
                },

                checkout(status) {
                    if (this.cart.length === 0) {
                        alert('El carrito está vacío');
                        return;
                    }

                    fetch('{{ route("pos.store") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            customer_id: this.customerId || null,
                            cart: this.cart,
                            discount: this.discount,
                            status: status,
                            service_notes: this.serviceNotes
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            alert(data.message);
                            window.location.reload();
                        } else {
                            alert('Error: ' + data.message);
                        }
                    })
                    .catch(err => console.error(err));
                }
            }
        }
    </script>
</x-app-layout>
