@extends('layouts.app')

@section('content')
    <header class="mb-6">
        <h1 class="text-2xl font-extrabold text-slate-900">Predicción de tóner</h1>
        <p class="mt-1 text-sm text-slate-600">Registra el nivel que ves en cada equipo. La estimación usa lecturas de fechas distintas y el ritmo de consumo observado.</p>
        <p class="mt-1 text-sm text-slate-600">Las ventas de impresión descuentan un porcentaje estimado desde la última lectura o recarga. El consumo real depende de la cobertura de cada página.</p>
        <p class="mt-2 text-sm text-slate-600">Semáforo: bajo de 0 a 30 %, medio de 31 a 70 % y alto de 71 a 100 %.</p>
    </header>

    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
        @foreach ($printers as $key => $printer)
            @php($formErrors = $errors->getBag($key))
            <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex items-center gap-4">
                    <img src="{{ asset('images/printer-illustration.png') }}" alt="" width="96" height="96" class="h-24 w-24 rounded-lg object-cover">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">{{ $printer['name'] }}</h2>
                        <p class="text-xs text-slate-600">{{ number_format($printer['pages']) }} páginas registradas en ventas</p>
                        @isset($printer['sku'])
                            <p class="text-xs text-slate-600">SKU {{ $printer['sku'] }}</p>
                        @endisset
                    </div>
                </div>

                <dl class="mb-5 space-y-3">
                    @foreach ($printer['levels'] as $color => $level)
                        @php($reading = $level['reading'])
                        @if ($reading)
                            <div class="rounded-lg bg-slate-50 p-3">
                                <dt class="text-sm font-semibold text-slate-800">{{ \App\Models\PrinterReading::COLORS[$color] }}</dt>
                                <dd class="mt-1">
                                    <span class="text-sm font-bold" style="color: {{ $reading->indicatorColor() }}">{{ round($reading->level, 2) }} % · {{ $reading->status() }}</span>
                                    <div aria-hidden="true" class="mt-1 h-2 overflow-hidden rounded-full bg-slate-200">
                                        <div class="h-full rounded-full" style="width: {{ $reading->level }}%; background-color: {{ $reading->indicatorColor() }}"></div>
                                    </div>
                                    <p class="mt-1 text-xs text-slate-600">
                                        @if (!$reading->recorded_on)
                                            Nivel de ejemplo · Registra una lectura real.
                                        @elseif ($reading->status() === 'Bajo')
                                            Nivel bajo · Lectura del {{ $reading->recorded_on->format('d/m/Y') }}.
                                        @elseif ($level['days'] === 0)
                                            El nivel podría haber llegado al umbral bajo. Registra otra lectura.
                                        @elseif ($level['days'] !== null)
                                            Nivel bajo estimado en {{ $level['days'] }} {{ $level['days'] === 1 ? 'día' : 'días' }}.
                                        @else
                                            Lectura del {{ $reading->recorded_on->format('d/m/Y') }} · Aún sin datos para estimar.
                                        @endif
                                    </p>
                                </dd>
                            </div>
                        @endif
                    @endforeach
                </dl>

                <form method="POST" action="{{ route('printers.readings.store', $key) }}" class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    @csrf
                    @if ($formErrors->any())
                        <ul role="alert" class="text-sm text-red-700 sm:col-span-2">
                            @foreach ($formErrors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    @endif

                    <label class="text-sm font-medium text-slate-700">Tipo de registro
                        <select name="is_refill" class="mt-1 block w-full rounded-lg border border-slate-300 p-2">
                            <option value="0">Lectura de nivel</option>
                            <option value="1" @selected($formErrors->any() && old('is_refill') === '1')>Recarga</option>
                        </select>
                    </label>
                    <label class="text-sm font-medium text-slate-700">Consumible
                        <select name="color" class="mt-1 block w-full rounded-lg border border-slate-300 p-2">
                            @foreach ($printer['colors'] as $color)
                                <option value="{{ $color }}" @selected($formErrors->any() && old('color') === $color)>{{ \App\Models\PrinterReading::COLORS[$color] }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="text-sm font-medium text-slate-700">Fecha
                        <input type="date" name="recorded_on" value="{{ $formErrors->any() ? old('recorded_on') : today()->toDateString() }}" max="{{ today()->toDateString() }}" required class="mt-1 block w-full rounded-lg border border-slate-300 p-2">
                    </label>
                    <label class="text-sm font-medium text-slate-700">Nivel después del registro (%)
                        <input type="number" name="level" min="0" max="100" value="{{ $formErrors->any() ? old('level') : '' }}" required class="mt-1 block w-full rounded-lg border border-slate-300 p-2">
                    </label>
                    <button class="justify-self-start rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 sm:col-span-2">Guardar registro</button>
                </form>
            </section>
        @endforeach
    </div>

    @include('printers.usage')

    <section class="mt-6 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="mb-4 text-lg font-bold text-slate-900">Historial de recargas</h2>
        @if ($refills->count())
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-slate-700">
                        <tr>
                            <th scope="col" class="p-3">Fecha</th>
                            <th scope="col" class="p-3">Impresora</th>
                            <th scope="col" class="p-3">Consumible</th>
                            <th scope="col" class="p-3">Nivel posterior</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($refills as $refill)
                            <tr class="border-t border-slate-200">
                                <td class="whitespace-nowrap p-3">{{ $refill->recorded_on->format('d/m/Y') }}</td>
                                <td class="p-3">{{ $printers[$refill->printer]['name'] }}</td>
                                <td class="p-3">{{ \App\Models\PrinterReading::COLORS[$refill->color] }}</td>
                                <td class="whitespace-nowrap p-3">{{ $refill->level }} %</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $refills->links() }}</div>
        @else
            <p class="text-sm text-slate-600">Todavía no hay recargas registradas.</p>
        @endif
    </section>
@endsection
