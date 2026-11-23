<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Kopion CRM') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Tailwind CSS & Alpine.js -->
        <script src="https://cdn.tailwindcss.com"></script>
        <style>[x-cloak] { display: none !important; }</style>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <link rel="stylesheet" href="{{ asset('css/kopion-glass.css') }}">
        <script src="{{ asset('js/scroll-reveal.js') }}" defer></script>
    </head>
    <body class="kopion-glass font-sans antialiased selection:bg-indigo-600 selection:text-white flex flex-col justify-between">
        
        <!-- Barra de Navegación Superior con la paleta de colores del CRM (Slate-900 / Blue-400) -->
        <nav class="kopion-bar px-6 py-3.5 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 rounded-lg bg-white p-0.5 flex items-center justify-center shadow-sm">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo Kopion" class="w-full h-full object-contain">
                </div>
                <span class="font-extrabold tracking-wider text-base text-blue-400">KOPION CRM</span>
            </div>
            <div class="flex items-center space-x-2">
                <span class="text-xs text-slate-600 font-medium hidden sm:inline">Papelería KOPION</span>
                <span class="kopion-identity text-xs px-2.5 py-1 rounded border">Punto de Venta</span>
            </div>
        </nav>

        <!-- Contenedor Principal para Tarjeta Minimalista -->
        <main data-scroll-reveal class="flex-1 flex flex-col justify-center items-center px-4 py-8 sm:py-12">
            <div class="kopion-auth w-full {{ request()->routeIs('register') ? 'sm:max-w-2xl' : 'sm:max-w-md' }}">
                {{ $slot }}
            </div>
        </main>

        <!-- Pie de Página Minimalista -->
        <footer class="py-4 text-center text-xs text-slate-500">
            &copy; {{ date('Y') }} Papelería KOPION &bull; CRM &bull; Todos los derechos reservados.
        </footer>

    </body>
</html>
