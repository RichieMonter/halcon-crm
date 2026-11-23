@extends('portal.layout')

@section('content')
    <section aria-labelledby="offers-title" x-data="{ page: 0 }">
        <div class="mb-4 flex items-center justify-between gap-4">
            <div>
                <h1 id="offers-title" class="text-2xl font-extrabold">Qué ofrece Kopion</h1>
                <p class="mt-1 text-sm text-slate-600">Consulta los tipos de servicio y sus precios en MXN.</p>
            </div>
            <div class="flex shrink-0 items-center gap-2">
                <button type="button" @click="page = 0" :disabled="page === 0" disabled aria-label="Ver servicios anteriores" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 disabled:opacity-40">←</button>
                <span aria-live="polite" x-text="(page + 1) + ' de 2'" class="text-sm text-slate-600">1 de 2</span>
                <button type="button" @click="page = 1" :disabled="page === 1" aria-label="Ver más servicios" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 disabled:opacity-40">→</button>
            </div>
        </div>
        <div data-reveal-group class="grid grid-cols-1 gap-4 md:grid-cols-2">
            @foreach ($offers as $key => $offer)
                <article x-show="page === {{ intdiv($loop->index, 2) }}" @if($loop->index >= 2) x-cloak @endif
                    class="kopion-offer overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                    <img src="{{ asset('images/portal-'.$key.'.jpg') }}" alt="" class="h-40 w-full object-cover sm:h-48">
                    <div class="flex h-72 flex-col p-4">
                        <h2 class="text-lg font-bold">{{ $offer['title'] }}</h2>
                        <p class="mb-3 text-sm text-slate-600">{{ $offer['description'] }}</p>
                        <ul class="min-h-0 flex-1 divide-y divide-slate-100 overflow-y-auto pr-1 text-sm">
                            @forelse ($key === 'papeleria' ? $offer['products']->take(5) : $offer['products'] as $product)
                                <li class="flex justify-between gap-3 py-1.5">
                                    <span>{{ $product->name }}</span>
                                    <strong class="shrink-0 tabular-nums text-indigo-700">${{ number_format($product->price, 2) }}</strong>
                                </li>
                            @empty
                                <li class="py-2 text-slate-600">Consulta disponibilidad con la papelería.</li>
                            @endforelse
                        </ul>
                        <a href="{{ route('portal.catalog') }}" class="mt-3 self-start text-sm font-semibold text-indigo-700 hover:underline">Ver catálogo →</a>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    <section aria-labelledby="portal-campaign-title" class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 id="portal-campaign-title" class="text-lg font-bold">Campañas activas y próximas</h2>
        <div data-reveal-group class="mt-4 grid gap-4 sm:grid-cols-2">
            @forelse ($campaigns as $campaign)
                <article class="flex gap-4 rounded-lg border border-slate-200 p-3">
                    <img src="{{ $campaign->image_path ? route('campaigns.image', $campaign) : asset('images/portal-buen-fin.jpg') }}"
                        alt="" loading="lazy" class="h-20 w-20 shrink-0 rounded object-contain">
                    <div>
                        <p class="text-xs font-semibold text-indigo-700">{{ $campaign->status() === 'Activa' ? 'Activa' : 'Próxima' }}</p>
                        <h3 class="font-bold">{{ $campaign->name }}</h3>
                        <p class="text-sm">{{ $campaign->discount_percent }} % de descuento</p>
                        <p class="mt-1 text-xs text-slate-600">{{ $campaign->starts_on->format('d/m/Y') }} al {{ $campaign->ends_on->format('d/m/Y') }}</p>
                    </div>
                </article>
            @empty
                <p class="text-sm text-slate-600">Por ahora no hay campañas disponibles para tu cuenta.</p>
            @endforelse
        </div>
    </section>

    <section id="mis-pedidos" class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-lg font-bold">Mis pedidos</h2>
            <a href="{{ route('portal.orders.new') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">Nueva cotización</a>
        </div>
        <div class="mt-4 overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-700">
                    <tr><th scope="col" class="p-3">Folio</th><th scope="col" class="p-3">Fecha</th><th scope="col" class="p-3">Estado</th><th scope="col" class="p-3">Total MXN</th><th scope="col" class="p-3">Acción</th></tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr class="border-t border-slate-200">
                            <td class="p-3 font-medium">{{ $order->invoice_number ?: $order->order_number }}</td>
                            <td class="whitespace-nowrap p-3">{{ $order->created_at->format('d/m/Y') }}</td>
                            <td class="p-3">{{ ucfirst($order->status) }}</td>
                            <td class="p-3 tabular-nums">${{ number_format($order->total, 2) }}</td>
                            <td class="whitespace-nowrap p-3"><a href="{{ route('portal.orders.cart', $order) }}" class="font-semibold text-indigo-700 hover:underline">{{ $order->status === 'pendiente' ? 'Abrir carrito' : 'Ver pedido' }}</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="p-4 text-slate-600">Aún no tienes cotizaciones ni pedidos.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $orders->links() }}</div>
    </section>
@endsection
