<section class="h-full rounded-xl border border-gray-200 bg-white p-5 shadow-sm" aria-label="Servicio de impresiones">
    <h2 class="font-bold text-slate-900">Servicio de impresiones</h2>
    <p class="mb-4 text-sm text-slate-600">{{ number_format(array_sum($printCounts)) }} páginas en total en ventas completadas</p>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        @include('orders.print-pie', [
            'title' => 'Por modo de impresión',
            'counts' => $printCounts,
            'colors' => ['Blanco y negro' => '#334155', 'A color' => '#4f46e5', 'Sin modo registrado' => '#94a3b8'],
            'unknown' => 'Sin modo registrado',
        ])
        @include('orders.print-pie', [
            'title' => 'Por tamaño de papel',
            'counts' => $printSizeCounts,
            'colors' => ['Carta' => '#4f46e5', 'Oficio' => '#0f766e', 'Doble carta' => '#a21caf', 'Sin tamaño registrado' => '#94a3b8'],
            'unknown' => 'Sin tamaño registrado',
        ])
    </div>
</section>
