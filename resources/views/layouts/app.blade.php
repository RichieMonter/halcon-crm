<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Halcón System - CRM</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex flex-col">
    <!-- Navbar -->
    <nav class="bg-slate-800 border-b border-slate-700 px-6 py-4 flex justify-between items-center">
        <div class="flex items-center space-x-3">
            <span class="text-xl font-bold text-indigo-400">Halcón System</span>
            <span class="text-xs bg-indigo-500/20 text-indigo-300 px-2.5 py-1 rounded-full border border-indigo-500/30">
                {{ auth()->user()->role->department_name ?? 'Sin Rol' }}
            </span>
        </div>
        <div class="flex items-center space-x-4">
            <span class="text-sm text-slate-300">Usuario: <strong class="text-white">{{ auth()->user()->username }}</strong></span>
            <form action="{{ route('logout') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="text-sm bg-red-600/80 hover:bg-red-600 text-white px-3 py-1.5 rounded-lg transition-colors">
                    Cerrar Sesión
                </button>
            </form>
        </div>
    </nav>

    <!-- Contenido Principal -->
    <main class="flex-1 p-6 max-w-7xl w-full mx-auto">
        @if (session('success'))
            <div class="bg-emerald-500/20 border border-emerald-500/50 text-emerald-200 p-4 rounded-lg mb-6">
                {{ session('success') }}
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
