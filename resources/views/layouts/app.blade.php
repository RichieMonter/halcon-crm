<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kopion CRM</title>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('css/kopion-glass.css') }}">
    <script src="{{ asset('js/scroll-reveal.js') }}" defer></script>
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="kopion-glass font-sans leading-normal tracking-normal">
    @include('layouts.account-header')

    <main data-scroll-reveal class="container mx-auto px-4 py-8">
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
