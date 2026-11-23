<div class="mb-6 space-y-4">
    @include('orders.column-chart', [
        'title' => 'Clientes que más compran',
        'unit' => 'Los cinco primeros por importe histórico de ventas completadas en MXN',
        'rows' => $topCustomers->map(fn ($customer) => [
            'label' => $customer->name,
            'type' => trans_choice('{1} :count compra|[2,*] :count compras', $customer->purchase_count),
            'values' => [$customer->purchase_total],
        ])->all(),
        'series' => ['Importe comprado' => '#4f46e5'],
        'decimals' => 2,
        'empty' => 'Aún no hay ventas completadas con cliente asignado.',
    ])

    <section aria-labelledby="campaign-title" class="min-w-0 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <h2 id="campaign-title" class="font-bold text-slate-900">Campañas de marketing por temporada</h2>
        <p class="mt-1 text-xs text-slate-600">Durante la vigencia, el descuento se sugiere en el carrito para los clientes del público seleccionado.</p>

        @include('customers.campaign-form', ['campaign' => null])

        <div class="mt-5 border-t border-slate-200 pt-4">
            @forelse ($campaigns as $campaign)
                @php($status = $campaign->status())
                <article class="mb-2 flex flex-wrap items-center justify-between gap-3 rounded-lg bg-slate-50 p-3 text-sm">
                    <div>
                        <h3 class="font-semibold text-slate-800">{{ $campaign->name }} <span class="ml-1 text-xs font-medium text-indigo-700">{{ $status }}</span></h3>
                        <p class="mt-1 text-xs text-slate-600">{{ $campaign->starts_on->format('d/m/Y') }} al {{ $campaign->ends_on->format('d/m/Y') }} · {{ $audiences[$campaign->audience] }} · {{ $campaign->discount_percent }} %</p>
                        @if ($campaign->ended_at)
                            <p class="mt-1 text-xs text-slate-600">Terminada el {{ $campaign->ended_at->format('d/m/Y') }}</p>
                        @endif
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('customers.campaigns.edit', $campaign) }}" class="rounded border border-indigo-200 bg-white px-3 py-1.5 text-xs font-semibold text-indigo-700">Editar</a>
                        @if ($status === 'Activa')
                            <form method="POST" action="{{ route('customers.campaigns.end', $campaign) }}">
                                @csrf
                                @method('PATCH')
                                <button class="rounded border border-amber-200 bg-white px-3 py-1.5 text-xs font-semibold text-amber-800">Terminar antes</button>
                            </form>
                        @endif
                        <form method="POST" action="{{ route('customers.campaigns.destroy', $campaign) }}" onsubmit="return confirm('¿Eliminar esta campaña?')">
                            @csrf
                            @method('DELETE')
                            <button class="rounded border border-red-200 bg-white px-3 py-1.5 text-xs font-semibold text-red-700">Eliminar</button>
                        </form>
                    </div>
                </article>
            @empty
                <p class="text-sm text-slate-600">Aún no hay campañas registradas.</p>
            @endforelse
            {{ $campaigns->links() }}
        </div>
    </section>
</div>
