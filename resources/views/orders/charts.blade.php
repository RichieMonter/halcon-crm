@php
    $charts = [
        [
            'title' => 'Ventas del día',
            'unit' => 'Importe en MXN',
            'rows' => [
                ['label' => 'Hoy', 'values' => [$todaySales]],
                ['label' => 'Ayer', 'values' => [$yesterdaySales]],
            ],
            'series' => ['Ventas completadas' => '#4f46e5'],
            'decimals' => 2,
        ],
        [
            'title' => 'Productos más vendidos',
            'unit' => 'Unidades en ventas completadas',
            'rows' => $topProductsList->take(5)->map(fn ($item) => [
                'label' => $item->product?->name ?? 'Producto',
                'values' => [$item->total_sold],
            ])->all(),
            'series' => ['Unidades vendidas' => '#4f46e5'],
            'empty' => 'Aún no hay productos en ventas completadas.',
        ],
        [
            'title' => 'Alertas de inventario',
            'unit' => 'Piezas de productos e insumos con nivel bajo',
            'rows' => $lowStockProducts->map(fn ($item) => [
                'label' => $item->name,
                'type' => $item->type === 'INSUMO' ? 'Insumo' : 'Producto',
                'values' => [$item->stock, max(5, $item->min_stock)],
            ])->all(),
            'series' => ['Existencias' => '#0f766e', 'Umbral de alerta' => '#818cf8'],
            'empty' => 'No hay productos ni insumos con alerta de inventario.',
        ],
        [
            'title' => 'Insumos internos',
            'unit' => 'Piezas disponibles en inventario',
            'rows' => $internalSupplies->map(fn ($item) => [
                'label' => $item->name,
                'values' => [$item->stock],
            ])->all(),
            'series' => ['Existencias' => '#0f766e'],
            'empty' => 'Aún no hay insumos internos en el catálogo.',
        ],
        [
            'title' => 'Servicios',
            'unit' => 'Unidades de servicio en ventas completadas',
            'rows' => $servicesSold->map(fn ($quantity, $service) => [
                'label' => $service,
                'values' => [$quantity],
            ])->values()->all(),
            'series' => ['Servicios vendidos' => '#0f766e'],
        ],
        ['view' => 'orders.print-sales-chart'],
    ];
    $chartCount = count($charts);
    $lastPage = (int) ceil($chartCount / 2) - 1;
@endphp

<section class="mb-8" aria-labelledby="dashboard-charts-title"
    x-data="{ page: 0, get start() { return Math.min(this.page * 2, {{ $chartCount - 2 }}) } }">
    <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
        <h2 id="dashboard-charts-title" class="text-lg font-bold text-slate-900">Gráficas del dashboard</h2>
        <div class="flex items-center gap-2">
            <button type="button" @click="page--" :disabled="page === 0" disabled aria-label="Ver gráficas anteriores"
                class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 font-bold text-slate-800 hover:bg-slate-100 disabled:opacity-40">←</button>
            <span aria-live="polite" class="min-w-16 text-center text-sm text-slate-600"
                x-text="`${page + 1} de {{ $lastPage + 1 }}`">1 de {{ $lastPage + 1 }}</span>
            <button type="button" @click="page++" :disabled="page === {{ $lastPage }}" aria-label="Ver gráficas siguientes"
                class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 font-bold text-slate-800 hover:bg-slate-100 disabled:opacity-40">→</button>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        @foreach ($charts as $index => $chart)
            <div class="min-w-0" x-show="{{ $index }} >= start && {{ $index }} < start + 2" @if ($index >= 2) x-cloak @endif>
                @include($chart['view'] ?? 'orders.column-chart', $chart)
            </div>
        @endforeach
    </div>
</section>
