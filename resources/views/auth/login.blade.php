<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Halcón System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-slate-800 rounded-xl shadow-lg border border-slate-700 p-8">
        <h1 class="text-2xl font-bold text-center text-indigo-400 mb-6">Halcón System</h1>
        
        @if ($errors->any())
            <div class="bg-red-500/20 border border-red-500/50 text-red-200 p-3 rounded-lg mb-6 text-sm">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>• {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('login') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium mb-1">Nombre de Usuario</label>
                <input type="text" name="username" value="{{ old('username') }}" required class="w-full px-4 py-2 bg-slate-900 border border-slate-700 rounded-lg focus:outline-none focus:border-indigo-500 text-slate-100">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Contraseña</label>
                <input type="password" name="password" required class="w-full px-4 py-2 bg-slate-900 border border-slate-700 rounded-lg focus:outline-none focus:border-indigo-500 text-slate-100">
            </div>
            <button type="submit" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold rounded-lg transition-colors mt-2">
                Ingresar
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-slate-400">
            ¿No tienes cuenta? <a href="{{ route('register') }}" class="text-indigo-400 hover:underline">Regístrate aquí</a>
        </p>
    </div>
</body>
</html>