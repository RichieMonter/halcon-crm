<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kopion CRM</title>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal text-gray-900" x-data="{ openMenu: false }">

    <!-- Encabezado Superior Unificado -->
    <nav class="bg-slate-900 text-white px-6 py-3 flex justify-between items-center shadow-md sticky top-0 z-40">
        <div class="flex items-center space-x-4">
            <button @click="openMenu = !openMenu" class="text-gray-300 hover:text-white focus:outline-none transition">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>

            <!-- Barra de Búsqueda Global -->
            <div class="relative hidden sm:block w-64 md:w-96">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </span>
                <form action="{{ route('search.global') }}" method="GET" class="relative w-full max-w-md">
                    <div class="relative">
                        <input type="text" 
                            name="query" 
                            value="{{ request('query') }}"
                            placeholder="🔍 Buscar folio (COT-0001), cliente o producto..." 
                            class="w-full bg-slate-800 text-white placeholder-gray-400 border border-slate-700 rounded-full pl-4 pr-10 py-1.5 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none transition">
                        <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-white p-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="flex items-center space-x-4">
            <span class="font-bold tracking-wider text-sm md:text-base text-blue-400">KOPION CRM</span>
            @auth
                <span class="hidden md:inline text-xs text-gray-300 bg-slate-800 px-2.5 py-1 rounded border border-slate-700">{{ Auth::user()->name }}</span>
            @endauth
            <form action="{{ route('logout') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white text-xs px-3 py-1.5 rounded transition font-semibold">Cerrar Sesión</button>
            </form>
        </div>
    </nav>

    <!-- Menú Desplegable Lateral (Sidebar) -->
    <div x-show="openMenu" @click.away="openMenu = false" class="bg-slate-800 text-white w-64 fixed left-0 top-14 bottom-0 z-50 p-4 shadow-2xl border-t border-slate-700 transition-all" x-cloak>
        <p class="text-xs uppercase font-semibold text-gray-400 mb-4 tracking-wider">Módulos del Sistema</p>
        <ul class="space-y-2">
            <li>
                <a href="{{ route('orders.index') }}" class="flex items-center px-3 py-2.5 rounded text-sm transition {{ request()->routeIs('orders.*') ? 'bg-blue-600 font-bold text-white shadow' : 'hover:bg-slate-700 text-gray-300' }}">
                    📊 Dashboard (Cotizaciones)
                </a>
            </li>
            <li>
                <a href="{{ route('customers.index') }}" class="flex items-center px-3 py-2.5 rounded text-sm transition {{ request()->routeIs('customers.*') ? 'bg-blue-600 font-bold text-white shadow' : 'hover:bg-slate-700 text-gray-300' }}">
                    👥 Gestión de Clientes
                </a>
            </li>
            <li>
                <a href="{{ route('products.index') }}" class="flex items-center px-3 py-2.5 rounded text-sm transition {{ request()->routeIs('products.*') ? 'bg-blue-600 font-bold text-white shadow' : 'hover:bg-slate-700 text-gray-300' }}">
                    📦 Catálogo e Inventario
                </a>
            </li>
        </ul>
    </div>

    <!-- Contenido Principal Dinámico -->
    <main class="container mx-auto px-4 py-8">
        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-300 text-emerald-800 px-4 py-3 rounded-xl relative mb-6 shadow-sm flex items-center justify-between">
                <span class="font-bold text-sm">✅ {{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-50 border border-red-300 text-red-800 px-4 py-3 rounded-xl relative mb-6 shadow-sm flex items-center justify-between">
                <span class="font-bold text-sm">⚠️ {{ session('error') }}</span>
            </div>
        @endif

        @yield('content')
    </main>

</body>
</html>
