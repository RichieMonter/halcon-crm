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
                <div class="relative">
                    <input type="password" id="password" name="password" required class="w-full px-4 py-2 bg-slate-900 border border-slate-700 rounded-lg focus:outline-none focus:border-indigo-500 text-slate-100 pr-10">
                    <button type="button" onclick="togglePassword('password', this)" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-200 focus:outline-none">
                        <!-- Icono de Ojo Abierto (Heroicons) -->
                        <svg id="eye-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.573 16.49 16.638 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                        <!-- Icono de Ojo Tachado (Oculto por defecto) -->
                        <svg id="eye-off-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 hidden">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold rounded-lg transition-colors mt-2">
                Ingresar
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-slate-400">
            ¿No tienes cuenta? <a href="{{ route('register') }}" class="text-indigo-400 hover:underline">Regístrate aquí</a>
        </p>
    </div>

    <script>
        function togglePassword(inputId, btn) {
            const input = document.getElementById(inputId);
            const eyeIcon = btn.querySelector('#eye-icon');
            const eyeOffIcon = btn.querySelector('#eye-off-icon');

            if (input.type === "password") {
                input.type = "text";
                eyeIcon.classList.add('hidden');
                eyeOffIcon.classList.remove('hidden');
            } else {
                input.type = "password";
                eyeIcon.classList.remove('hidden');
                eyeOffIcon.classList.add('hidden');
            }
        }
    </script>
</body>
</html>