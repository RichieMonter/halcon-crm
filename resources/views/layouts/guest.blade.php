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
        <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
        <style>[x-cloak] { display: none !important; }</style>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-full font-sans antialiased text-slate-800 bg-slate-100 selection:bg-indigo-600 selection:text-white flex flex-col justify-between">
        
        <!-- Barra de Navegación Superior con la paleta de colores del CRM (Slate-900 / Blue-400) -->
        <nav class="bg-slate-900 text-white px-6 py-3.5 flex justify-between items-center shadow-md border-b border-slate-800">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 rounded-lg bg-white p-0.5 flex items-center justify-center shadow-sm">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo Kopion" class="w-full h-full object-contain">
                </div>
                <span class="font-extrabold tracking-wider text-base text-blue-400">KOPION CRM</span>
            </div>
            <div class="flex items-center space-x-2">
                <span class="text-xs text-slate-400 font-medium hidden sm:inline">Papelería KOPION</span>
                <span class="text-xs text-slate-300 bg-slate-800 px-2.5 py-1 rounded border border-slate-700">Punto de Venta</span>
            </div>
        </nav>

        <!-- Contenedor Principal para Tarjeta Minimalista -->
        <main class="flex-1 flex flex-col justify-center items-center px-4 py-8 sm:py-12">
            <div class="w-full sm:max-w-md">
                {{ $slot }}
            </div>
        </main>

        <!-- Pie de Página Minimalista -->
        <footer class="py-4 text-center text-xs text-slate-500">
            &copy; {{ date('Y') }} Papelería KOPION &bull; CRM &bull; Todos los derechos reservados.
        </footer>

    </body>
</html>
