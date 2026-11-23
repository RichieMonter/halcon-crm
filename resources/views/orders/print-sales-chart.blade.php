<section class="h-full rounded-xl border border-gray-200 bg-white p-5 shadow-sm" aria-label="Servicio de impresiones">
    <h2 class="font-bold text-slate-900">Servicio de impresiones</h2>
    <p class="mb-4 text-sm text-slate-600">{{ number_format(array_sum($printCounts)) }} páginas en total en ventas completadas</p>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        @include('orders.print-pie', [
            'title' => 'Por modo de impresión',
            'counts' => $printCounts,
            'colors' => ['Blanco y negro' => '#a1afb7', 'A color' => '#5cb9db', 'Sin modo registrado' => '#f0b25f'],
            'unknown' => 'Sin modo registrado',
        ])
        @include('orders.print-pie', [
            'title' => 'Por tamaño de papel',
            'counts' => $printSizeCounts,
            'colors' => ['Carta' => '#5cb9db', 'Oficio' => '#f0b25f', 'Doble carta' => '#f96a7c', 'Sin tamaño registrado' => '#a1afb7'],
            'unknown' => 'Sin tamaño registrado',
        ])
    </div>
</section>
