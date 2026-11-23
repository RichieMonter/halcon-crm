@php($orderFilters = array_diff_key($filters, $salesFilters))
<section class="h-full rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
    <form action="{{ route('orders.index') }}#graficas" method="GET" class="flex flex-wrap items-end gap-2">
        @foreach ($orderFilters as $field => $value)
            @continue($value === null)
            <input type="hidden" name="{{ $field }}" value="{{ $value }}">
        @endforeach
        <label class="text-xs font-medium text-slate-700">
            Desde
            <input type="date" name="ventas_desde" required max="{{ $salesToday->toDateString() }}"
                value="{{ old('ventas_desde', $salesStart->toDateString()) }}" class="mt-1 block rounded-lg border border-slate-300 px-2 py-1.5 text-sm">
        </label>
        <label class="text-xs font-medium text-slate-700">
            Hasta
            <input type="date" name="ventas_hasta" required max="{{ $salesToday->toDateString() }}"
                value="{{ old('ventas_hasta', $salesEnd->toDateString()) }}" class="mt-1 block rounded-lg border border-slate-300 px-2 py-1.5 text-sm">
        </label>
        <button type="submit" class="rounded-lg bg-indigo-600 px-3 py-2 text-xs font-semibold">Filtrar ventas</button>
        @if (array_filter($salesFilters))
            <a href="{{ route('orders.index', $orderFilters) }}#graficas" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-semibold">Últimos 7 días</a>
        @endif
    </form>
    @foreach (['ventas_desde', 'ventas_hasta'] as $field)
        @error($field)
            <p role="alert" class="mt-2 text-xs text-red-700">{{ $message }}</p>
        @enderror
    @endforeach

    @include('orders.column-chart', [
        'title' => 'Ventas por día',
        'unit' => 'Del '.$salesStart->format('d/m/Y').' al '.$salesEnd->format('d/m/Y').' · Importe en MXN',
        'rows' => $salesRows,
        'series' => ['Ventas completadas' => '#5cb9db'],
        'decimals' => 2,
        'compact' => true,
    ])
</section>
