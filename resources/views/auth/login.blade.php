<x-guest-layout>
    <div class="bg-white rounded-2xl shadow-xl shadow-slate-200/60 border border-slate-200/80 p-8 sm:p-10 transition-all duration-300" x-data="{ showPassword: false }">
        
        <!-- Logo y Encabezado Minimalista -->
        <div class="text-center mb-7">
            <div class="inline-flex items-center justify-center mb-3 transition-transform duration-300 hover:scale-105">
                <div class="w-28 h-28 rounded-2xl bg-white p-2 border border-slate-200/70 shadow-sm flex items-center justify-center">
                    <img src="{{ asset('images/logo.png') }}" alt="Papelería KOPION" class="w-full h-full object-contain">
                </div>
            </div>
            
            <div class="mb-2">
                <span class="inline-flex items-center px-3 py-0.5 rounded-full text-xs font-bold bg-slate-900 text-blue-400 tracking-wider shadow-sm border border-slate-800">
                    KOPION CRM
                </span>
            </div>

            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Bienvenido a Papelería KOPION</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Ingresa tus credenciales para acceder al sistema</p>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="mb-5" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            <!-- Correo Electrónico -->
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
                           autocomplete="username" 
                           placeholder="nombre@ejemplo.com"
                           class="w-full pl-10 pr-4 py-2.5 bg-slate-50 hover:bg-white focus:bg-white border border-slate-200 rounded-xl text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-transparent transition" />
                </div>
                <x-input-error :messages="$errors->get('email')" class="mt-1.5 text-xs text-red-600" />
            </div>

            <!-- Contraseña con botón de Ver/Ocultar -->
            <div>
                <label for="password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                    Contraseña
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </span>
                    <input id="password" 
                           :type="showPassword ? 'text' : 'password'" 
                           name="password" 
                           required 
                           autocomplete="current-password" 
                           placeholder="••••••••"
                           class="w-full pl-10 pr-10 py-2.5 bg-slate-50 hover:bg-white focus:bg-white border border-slate-200 rounded-xl text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-transparent transition" />
                    
                    <button type="button" 
                            @click="showPassword = !showPassword" 
                            class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none"
                            tabindex="-1">
                        <!-- Icono Ojo Abierto -->
                        <svg x-show="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <!-- Icono Ojo Cerrado -->
                        <svg x-show="showPassword" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                        </svg>
                    </button>
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-1.5 text-xs text-red-600" />
            </div>

            <!-- Recordarme & Olvidé contraseña -->
            <div class="flex items-center justify-between pt-1">
                <label for="remember_me" class="inline-flex items-center cursor-pointer select-none">
                    <input id="remember_me" 
                           type="checkbox" 
                           name="remember" 
                           class="w-4 h-4 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500 focus:ring-offset-0">
                    <span class="ms-2 text-xs text-slate-600 font-medium">Recordarme</span>
                </label>

                @if (Route::has('password.request'))
                    <a class="text-xs font-medium text-indigo-600 hover:text-indigo-700 hover:underline transition" href="{{ route('password.request') }}">
                        ¿Olvidaste tu contraseña?
                    </a>
                @endif
            </div>

            <!-- Botón de Iniciar Sesión (Estilo Índigo como + Nueva Cotización) -->
            <div class="pt-2">
                <button type="submit" 
                        class="w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-semibold rounded-xl text-sm transition-all duration-200 shadow-md shadow-indigo-600/20 hover:shadow-lg hover:shadow-indigo-600/30 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-600 flex items-center justify-center space-x-2 group">
                    <span>Iniciar Sesión</span>
                    <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </button>
            </div>
        </form>

        <!-- Registro -->
        @if (Route::has('register'))
            <div class="mt-6 pt-5 border-t border-slate-100 text-center">
                <p class="text-xs text-slate-500">
                    ¿Aún no tienes una cuenta? 
                    <a href="{{ route('register') }}" class="font-semibold text-indigo-600 hover:text-indigo-700 hover:underline ml-1">
                        Regístrate aquí
                    </a>
                </p>
            </div>
        @endif
    </div>
</x-guest-layout>
