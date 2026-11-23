@php
    $account = auth()->user();
    $isCustomer = (int) $account->role_id === \App\Models\User::CUSTOMER_ROLE;
    $home = route($account->homeRoute());
    $links = $isCustomer ? [
        ['🏠', 'Inicio', $home, 'portal.home'],
        ['🧾', 'Mis pedidos', $home.'#mis-pedidos', 'portal.orders.cart'],
        ['📦', 'Catálogo', route('portal.catalog'), 'portal.catalog'],
        ['🛒', 'Nueva cotización', route('portal.orders.new'), 'portal.orders.new'],
    ] : [
        ['📊', 'Dashboard (Cotizaciones)', $home, 'orders.*'],
        ['👥', 'Gestión de clientes', route('customers.index'), 'customers.*'],
        ['📦', 'Catálogo e Inventario', route('products.index'), 'products.*'],
        ['🖨️', 'Predicción de tóner', route('printers.index'), 'printers.*'],
    ];
    $searchName = $isCustomer ? 'buscar' : 'query';
    $searchValue = request()->query($searchName);
@endphp

<header class="kopion-bar sticky top-0 z-40"
    x-data="{ openMenu: false }" @click.outside="openMenu = false"
    @keydown.escape.window="if (openMenu) { openMenu = false; $refs.menuToggle.focus() }">
    <div class="flex flex-wrap items-center gap-x-3 gap-y-2 px-4 py-2.5 lg:flex-nowrap lg:gap-x-4 lg:px-6">
        <button type="button" x-ref="menuToggle" @click="openMenu = !openMenu" :aria-expanded="openMenu.toString()"
            aria-controls="account-sidebar" aria-label="Menú de navegación"
            class="shrink-0 rounded-md p-1 text-slate-700 hover:bg-sky-100 hover:text-sky-900">
            <svg aria-hidden="true" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>

        <a href="{{ $home }}" class="flex shrink-0 items-center gap-2 whitespace-nowrap text-sm font-bold">
            <img src="{{ asset('images/logo.png') }}" alt="" class="h-8 w-8 rounded-md bg-white object-contain">
            Papelería Kopion
        </a>

        <form action="{{ route($isCustomer ? 'portal.catalog' : 'search.global') }}" method="GET"
            class="relative w-full min-w-0 lg:w-auto lg:flex-1">
            <input type="search" name="{{ $searchName }}" value="{{ is_string($searchValue) ? $searchValue : '' }}"
                aria-label="{{ $isCustomer ? 'Buscar productos o servicios' : 'Buscar folio, cliente o producto' }}"
                placeholder="{{ $isCustomer ? 'Buscar productos o servicios…' : 'Buscar folio, cliente o producto…' }}"
                class="w-full rounded-full border px-3 py-1.5 pr-10 text-sm placeholder:text-slate-600">
            <button type="submit" aria-label="Buscar" class="absolute inset-y-0 right-2 px-1 text-slate-700 hover:text-sky-900">
                <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="10.5" cy="10.5" r="6.5"/><path stroke-linecap="round" d="m16 16 4 4"/>
                </svg>
            </button>
        </form>

        <span class="kopion-identity min-w-0 truncate rounded-md border px-2 py-1.5 text-xs"
            title="{{ $account->name }} · {{ $account->roleLabel() }}">{{ $account->name }} · {{ $account->roleLabel() }}</span>
        <form action="{{ route('logout') }}" method="POST" class="ml-auto shrink-0 lg:ml-0">
            @csrf
            <button class="rounded-lg bg-red-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-700">Cerrar sesión</button>
        </form>
    </div>

    <nav id="account-sidebar" aria-label="Módulos disponibles" x-show="openMenu" x-cloak
        class="kopion-sidebar absolute left-0 top-full max-h-[70vh] w-64 space-y-1 overflow-y-auto rounded-br-xl p-3">
        @foreach ($links as [$icon, $label, $url, $pattern])
            <a href="{{ $url }}" @click="openMenu = false"
                @if(request()->routeIs($pattern)) aria-current="page" @endif
                class="flex items-center gap-2 rounded-md px-3 py-2.5 text-sm {{ request()->routeIs($pattern) ? 'bg-blue-600 font-semibold' : 'text-slate-700 hover:bg-sky-100 hover:text-sky-900' }}">
                <span aria-hidden="true">{{ $icon }}</span>{{ $label }}
            </a>
        @endforeach
    </nav>
</header>
