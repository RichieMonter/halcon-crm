<x-guest-layout>
    <div class="bg-white rounded-2xl shadow-xl shadow-slate-200/60 border border-slate-200/80 p-8 sm:p-10 transition-all duration-300">
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

            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Confirmar Contraseña</h1>
            <p class="text-xs text-slate-500 mt-1">
                Esta es un área protegida. Por favor confirma tu contraseña antes de continuar.
            </p>
        </div>

        <form method="POST" action="{{ route('password.confirm') }}" class="space-y-4">
            @csrf

            <!-- Password -->
            <div>
                <label for="password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                    Contraseña
                </label>
                <input id="password" 
                       type="password" 
                       name="password" 
                       required 
                       autocomplete="current-password" 
                       placeholder="••••••••"
                       class="w-full px-4 py-2.5 bg-slate-50 hover:bg-white focus:bg-white border border-slate-200 rounded-xl text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-transparent transition" />
                <x-input-error :messages="$errors->get('password')" class="mt-1.5 text-xs text-red-600" />
            </div>

            <div class="pt-2">
                <button type="submit" 
                        class="w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-semibold rounded-xl text-sm transition-all duration-200 shadow-md shadow-indigo-600/20 hover:shadow-lg hover:shadow-indigo-600/30 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-600">
                    Confirmar
                </button>
            </div>
        </form>
    </div>
</x-guest-layout>
