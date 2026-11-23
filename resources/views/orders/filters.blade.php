<div class="mb-6 rounded-xl border border-slate-200 bg-slate-50 p-4">
    <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
        <h3 class="text-sm font-bold text-slate-800">Búsqueda avanzada</h3>
        <a href="{{ route('orders.index', array_replace($filters, ['estado' => 'pendiente'])) }}#cotizaciones"
            class="rounded-full border px-3 py-1.5 text-xs font-semibold {{ ($filters['estado'] ?? null) === 'pendiente' ? 'border-indigo-600 bg-indigo-600 text-white' : 'border-indigo-200 bg-white text-indigo-700 hover:bg-indigo-50' }}">
            Ver solo pendientes
        </a>
    </div>

    <form action="{{ route('orders.index') }}#cotizaciones" method="GET" class="grid grid-cols-1 items-end gap-3 sm:grid-cols-2 xl:grid-cols-6">
        <input type="hidden" name="orden" value="{{ $sort }}">
        <input type="hidden" name="direccion" value="{{ $direction }}">
        <label class="text-sm font-medium text-slate-700 xl:col-span-2">
            Folio, cliente o tipo de orden
            <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" maxlength="150" placeholder="Ej. COT-0001 o Copias"
                class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
        </label>
        <label class="text-sm font-medium text-slate-700">
            Estatus
            <select name="estado" class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                <option value="">Todos</option>
                @foreach (['pendiente' => 'Pendiente', 'completado' => 'Completado', 'cancelado' => 'Cancelado'] as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['estado'] ?? null) === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <label class="text-sm font-medium text-slate-700">
            Interés
            <select name="interes" class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                <option value="">Todos</option>
                @foreach (['ALTO', 'MEDIO', 'BAJO'] as $interest)
                    <option value="{{ $interest }}" @selected(($filters['interes'] ?? null) === $interest)>{{ ucfirst(strtolower($interest)) }}</option>
                @endforeach
            </select>
        </label>
        <label class="text-sm font-medium text-slate-700">
            Creada desde
            <input type="date" name="desde" value="{{ $filters['desde'] ?? '' }}" class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
        </label>
        <label class="text-sm font-medium text-slate-700">
            Creada hasta
            <input type="date" name="hasta" value="{{ $filters['hasta'] ?? '' }}" class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
        </label>
        <div class="flex flex-wrap items-center gap-2 sm:col-span-2 xl:col-span-6">
            <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">Aplicar filtros</button>
            <a href="{{ route('orders.index') }}#cotizaciones" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">Limpiar filtros</a>
        </div>
    </form>

    @foreach (['search', 'estado', 'interes', 'desde', 'hasta', 'orden', 'direccion'] as $field)
        @error($field)
            <p role="alert" class="mt-2 text-xs text-red-700">{{ $message }}</p>
        @enderror
    @endforeach
</div>
