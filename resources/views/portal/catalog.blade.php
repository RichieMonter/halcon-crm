@extends('portal.layout')

@section('title', 'Catálogo')

@section('content')
    <section class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <h1 class="text-2xl font-extrabold">Catálogo</h1>
        <form method="GET" action="{{ route('portal.catalog') }}" class="mt-4 flex flex-wrap items-end gap-3">
            @if (!empty($filters['categoria']))
                <input type="hidden" name="categoria" value="{{ $filters['categoria'] }}">
            @endif
            <label class="w-full text-sm font-medium text-slate-700 sm:min-w-56 sm:flex-1">
                Buscar
                <input type="search" name="buscar" value="{{ $filters['buscar'] ?? '' }}" maxlength="100" placeholder="Nombre, código o categoría"
                    class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </label>
            <label class="text-sm font-medium text-slate-700">
                Tipo
                <select name="tipo" class="mt-1 block rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                    <option value="">Todos</option>
                    <option value="PRODUCTO" @selected(($filters['tipo'] ?? '') === 'PRODUCTO')>Productos</option>
                    <option value="SERVICIO" @selected(($filters['tipo'] ?? '') === 'SERVICIO')>Servicios</option>
                </select>
            </label>
            <label class="text-sm font-medium text-slate-700">
                Ordenar por
                <select name="orden" class="mt-1 block rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                    <option value="">Nombre (A–Z)</option>
                    <option value="precio-asc" @selected(($filters['orden'] ?? '') === 'precio-asc')>Precio: menor a mayor</option>
                    <option value="precio-desc" @selected(($filters['orden'] ?? '') === 'precio-desc')>Precio: mayor a menor</option>
                </select>
            </label>
            <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Aplicar filtros</button>
            <a href="{{ route('portal.catalog') }}" class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">Limpiar filtros</a>
        </form>

        @php($categoryFilters = array_filter(\Illuminate\Support\Arr::except($filters, ['categoria']), fn ($value) => $value !== null && $value !== ''))
        <nav aria-label="Categorías del catálogo" class="mt-4 flex flex-wrap gap-2">
            @foreach ($categories->prepend(null) as $category)
                @php($active = ($filters['categoria'] ?? null) === $category)
                <a href="{{ route('portal.catalog', $category === null ? $categoryFilters : $categoryFilters + ['categoria' => $category]) }}"
                    @if ($active) aria-current="true" @endif
                    class="rounded-full border px-3 py-1.5 text-xs font-semibold {{ $active ? 'border-indigo-600 bg-indigo-600 text-white' : 'border-slate-200 bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                    {{ $category ?? 'Todas las categorías' }}
                </a>
            @endforeach
        </nav>
    </section>
    <div data-reveal-group class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($products as $product)
            <article class="flex flex-col rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase text-indigo-700">{{ $product->category ?: ($product->type === 'SERVICIO' ? 'Servicio' : 'Producto') }}</p>
                <h2 class="mt-1 flex-1 font-bold">{{ $product->name }}</h2>
                <p class="mt-1 text-xs text-slate-500">{{ $product->code }}</p>
                <div class="mt-4 flex flex-wrap items-center justify-between gap-2">
                    <strong class="tabular-nums">${{ number_format($product->price, 2) }}</strong>
                    <a href="{{ route('portal.orders.new', ['product_id' => $product->id]) }}" class="rounded-lg bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white">Cotizar</a>
                </div>
            </article>
        @empty
            <p class="text-sm text-slate-600">No encontramos productos o servicios.</p>
        @endforelse
    </div>
    {{ $products->links() }}
@endsection
