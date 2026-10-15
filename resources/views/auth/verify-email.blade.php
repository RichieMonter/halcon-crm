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

            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Verifica tu Correo</h1>
            <p class="text-xs text-slate-500 mt-1">
                ¡Gracias por registrarte en Papelería KOPION! Antes de comenzar, haz clic en el enlace de verificación que enviamos a tu correo electrónico.
            </p>
        </div>

        @if (session('status') == 'verification-link-sent')
            <div class="mb-5 p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-700 text-center font-medium">
                Se ha enviado un nuevo enlace de verificación a tu correo electrónico.
            </div>
        @endif

        <div class="space-y-4">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" 
                        class="w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-semibold rounded-xl text-sm transition-all duration-200 shadow-md shadow-indigo-600/20 hover:shadow-lg hover:shadow-indigo-600/30 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-600">
                    Reenviar Correo de Verificación
                </button>
            </form>

            <form method="POST" action="{{ route('logout') }}" class="text-center">
                @csrf
                <button type="submit" class="text-xs font-semibold text-slate-500 hover:text-red-600 transition">
                    Cerrar Sesión
                </button>
            </form>
        </div>
    </div>
</x-guest-layout>
