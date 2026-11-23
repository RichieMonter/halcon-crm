<section aria-label="Gráficas de ventas e inventario" class="mb-8 grid grid-cols-1 gap-4 xl:grid-cols-3">
    @include('orders.column-chart', [
        'title' => 'Ventas del día',
        'unit' => 'Importe en MXN',
        'rows' => [
            ['label' => 'Hoy', 'values' => [$todaySales]],
            ['label' => 'Ayer', 'values' => [$yesterdaySales]],
        ],
        'series' => ['Ventas completadas' => '#4f46e5'],
        'decimals' => 2,
    ])

    @include('orders.column-chart', [
        'title' => 'Alertas de inventario',
        'unit' => 'Piezas',
        'rows' => $lowStockProducts->map(fn ($item) => [
            'label' => $item->name,
            'type' => $item->type === 'INSUMO' ? 'Insumo' : 'Producto',
            'values' => [$item->stock, max(5, $item->min_stock)],
        ])->all(),
        'series' => ['Existencias' => '#0f766e', 'Umbral de alerta' => '#818cf8'],
        'empty' => 'No hay productos ni insumos con alerta de inventario.',
    ])

    @include('orders.column-chart', [
        'title' => 'Productos más vendidos',
        'unit' => 'Unidades en ventas completadas',
        'rows' => $topProductsList->take(5)->map(fn ($item) => [
            'label' => $item->product?->name ?? 'Producto',
            'values' => [$item->total_sold],
        ])->all(),
        'series' => ['Unidades vendidas' => '#4f46e5'],
        'empty' => 'Aún no hay productos en ventas completadas.',
    ])
</section>
