@php
    $tonerPoint = function ($printer, $color, $label) {
        $reading = $printer['levels'][$color]['reading'];

        return $reading ? [
            'label' => $label,
            'type' => $reading->status(),
            'values' => [$reading->level],
            'color' => $reading->indicatorColor(),
        ] : null;
    };
    $tonerOptions = [
        'unit' => 'Nivel estimado del consumible',
        'series' => ['Bajo' => '#b91c1c', 'Medio' => '#a16207', 'Alto' => '#047857'],
        'maximum' => 100,
        'suffix' => '%',
        'decimals' => 2,
        'compact' => true,
        'empty' => 'Aún no hay lecturas.',
    ];
@endphp

<section aria-label="Niveles de tóner y tinta" class="mt-8 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
    <h2 class="mb-2 text-lg font-bold text-slate-900">Niveles de tóner y tinta</h2>
    <div class="grid grid-cols-1 divide-y divide-slate-200 lg:grid-cols-3 lg:divide-x lg:divide-y-0">
    @include('orders.column-chart', $tonerOptions + [
        'title' => 'Tóner de impresoras en blanco y negro',
        'rows' => array_filter([
            $tonerPoint($printerLevels['esca'], 'negro', 'Lexmark Esca'),
            $tonerPoint($printerLevels['zurah'], 'negro', 'Lexmark Zurah'),
        ]),
    ])

            @foreach (['epson', 'ricoh'] as $key)
                @php($printer = $printerLevels[$key])
                @include('orders.column-chart', $tonerOptions + [
                    'title' => $printer['name'],
                    'rows' => array_filter(array_map(
                        fn ($color) => $tonerPoint($printer, $color, ucfirst($color)),
                        $printer['colors'],
                    )),
                ])
            @endforeach
    </div>
    <a href="{{ route('printers.index') }}" class="mt-3 inline-block text-sm font-semibold text-indigo-700 hover:underline">Ver contadores y registrar recargas</a>
</section>
