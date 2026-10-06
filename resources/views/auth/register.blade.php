<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Halcón System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-slate-800 rounded-xl shadow-lg border border-slate-700 p-8">
        <h1 class="text-2xl font-bold text-center text-indigo-400 mb-6">Crear Cuenta</h1>
        
        @if ($errors->any())
            <div class="bg-red-500/20 border border-red-500/50 text-red-200 p-3 rounded-lg mb-6 text-sm">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>• {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('register') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium mb-1">Nombre de Usuario</label>
                <input type="text" name="username" value="{{ old('username') }}" required class="w-full px-4 py-2 bg-slate-900 border border-slate-700 rounded-lg focus:outline-none focus:border-indigo-500 text-slate-100">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Departamento / Rol</label>
                <select name="role_id" required class="w-full px-4 py-2 bg-slate-900 border border-slate-700 rounded-lg focus:outline-none focus:border-indigo-500 text-slate-100">
                    <option value="" disabled selected>Selecciona un rol</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}">{{ $role->department_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Contraseña</label>
                <input type="password" name="password" required class="w-full px-4 py-2 bg-slate-900 border border-slate-700 rounded-lg focus:outline-none focus:border-indigo-500 text-slate-100">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Confirmar Contraseña</label>
                <input type="password" name="password_confirmation" required class="w-full px-4 py-2 bg-slate-900 border border-slate-700 rounded-lg focus:outline-none focus:border-indigo-500 text-slate-100">
            </div>
            <button type="submit" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold rounded-lg transition-colors mt-2">
                Registrarse
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-slate-400">
            ¿Ya tienes cuenta? <a href="{{ route('login') }}" class="text-indigo-400 hover:underline">Inicia sesión</a>
        </p>
    </div>
</body>
</html>