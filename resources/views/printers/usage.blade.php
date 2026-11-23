<section class="mt-6 rounded-xl border border-slate-200 bg-white p-5 shadow-sm" aria-label="Uso diario de impresoras">
    <form method="GET" action="{{ route('printers.index') }}" class="mb-3 flex flex-wrap items-end gap-2">
        <label class="text-sm font-medium text-slate-700">Último día del periodo
            <input type="date" name="hasta" value="{{ $usageEnd->toDateString() }}" max="{{ today()->toDateString() }}" class="mt-1 block rounded-lg border border-slate-300 px-2 py-1.5">
        </label>
        <button class="rounded-lg bg-indigo-600 px-3 py-2 text-sm font-semibold text-white">Ver 7 días</button>
    </form>
    @error('hasta')
        <p role="alert" class="text-sm text-red-700">{{ $message }}</p>
    @enderror

    @include('orders.column-chart', [
        'title' => 'Uso diario por impresora',
        'unit' => 'Páginas impresas en ventas completadas',
        'rows' => $usageRows,
        'series' => ['Lexmark Esca' => '#4f46e5', 'Lexmark Zurah' => '#0369a1', 'Epson' => '#047857', 'Ricoh' => '#a21caf'],
        'compact' => true,
    ])

    <h2 class="mb-3 text-base font-bold text-slate-900">Detalle por cliente y venta</h2>
    @if ($usage->isNotEmpty())
        <div class="max-h-96 overflow-auto">
            <table class="w-full text-left text-sm">
                <thead class="sticky top-0 bg-slate-50 text-slate-700">
                    <tr>
                        <th scope="col" class="p-3">Fecha</th>
                        <th scope="col" class="p-3">Impresora</th>
                        <th scope="col" class="p-3">Cliente</th>
                        <th scope="col" class="p-3">Folio</th>
                        <th scope="col" class="p-3 text-right">Páginas</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($usage as $entry)
                        <tr class="border-t border-slate-200">
                            <td class="whitespace-nowrap p-3">{{ \Carbon\CarbonImmutable::parse($entry->print_day)->format('d/m/Y') }}</td>
                            <td class="p-3">{{ $printers[$entry->printer]['name'] }}</td>
                            <td class="p-3">{{ $entry->order->customer?->name ?? 'Sin cliente registrado' }}</td>
                            <td class="p-3">
                                <a href="{{ route('orders.pos', $entry->order_id) }}" class="font-medium text-indigo-700 hover:underline">{{ $entry->order->invoice_number ?: $entry->order->order_number }}</a>
                            </td>
                            <td class="p-3 text-right tabular-nums">{{ number_format($entry->pages) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="text-sm text-slate-600">No hay impresiones registradas en ventas completadas durante estos días.</p>
    @endif
</section>
