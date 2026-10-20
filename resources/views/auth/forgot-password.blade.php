<x-guest-layout>
    <div class="bg-white rounded-2xl shadow-xl shadow-slate-200/60 border border-slate-200/80 p-8 sm:p-10 transition-all duration-300">
        
        <!-- Logo y Encabezado Minimalista -->
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center mb-3 transition-transform duration-300 hover:scale-105">
                <div class="w-20 h-20 rounded-2xl bg-white p-2 border border-slate-200/70 shadow-sm flex items-center justify-center">
                    <img src="{{ asset('images/logo.png') }}" alt="Papelería KOPION" class="w-full h-full object-contain">
                </div>
            </div>
            
            <div class="mb-2">
                <span class="inline-flex items-center px-3 py-0.5 rounded-full text-xs font-bold bg-slate-900 text-blue-400 tracking-wider shadow-sm border border-slate-800">
                    KOPION CRM
                </span>
            </div>

            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Recuperar Contraseña</h1>
            <p class="text-xs text-slate-500 mt-1">
                Ingresa tu correo y te enviaremos un enlace para restablecerla.
            </p>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
            @csrf

            <!-- Email Address -->
            <div>
                <label for="email" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                    Correo Electrónico
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206" />
                        </svg>
                    </span>
                    <input id="email" 
                           type="email" 
                           name="email" 
                           value="{{ old('email') }}" 
                           required 
                           autofocus 
                           placeholder="nombre@ejemplo.com"
                           class="w-full pl-10 pr-4 py-2.5 bg-slate-50 hover:bg-white focus:bg-white border border-slate-200 rounded-xl text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-transparent transition" />
                </div>
                <x-input-error :messages="$errors->get('email')" class="mt-1.5 text-xs text-red-600" />
            </div>

            <div class="pt-2">
                <button type="submit" 
                        class="w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-semibold rounded-xl text-sm transition-all duration-200 shadow-md shadow-indigo-600/20 hover:shadow-lg hover:shadow-indigo-600/30 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-600">
                    Enviar Enlace de Recuperación
                </button>
            </div>
        </form>

        <div class="mt-6 pt-5 border-t border-slate-100 text-center">
            <a href="{{ route('login') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700 hover:underline">
                &larr; Volver a Iniciar Sesión
            </a>
        </div>
    </div>
</x-guest-layout>
