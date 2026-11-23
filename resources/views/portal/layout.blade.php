<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Mi cuenta') | Papelería Kopion</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/kopion-glass.css') }}">
    <script src="{{ asset('js/scroll-reveal.js') }}" defer></script>
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="kopion-glass font-sans">
    @include('layouts.account-header')
    <main data-scroll-reveal class="mx-auto {{ request()->routeIs('portal.orders.*') ? 'max-w-screen-2xl' : 'max-w-6xl' }} space-y-8 px-4 py-8 sm:px-6">
        @if(session('success'))
            <p role="status" class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</p>
        @endif
        @if($errors->any())
            <ul role="alert" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                @foreach($errors->all() as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        @endif
        @yield('content')
    </main>
</body>
</html>
