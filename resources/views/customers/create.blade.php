@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto bg-slate-800 border border-slate-700 rounded-xl p-6 shadow-lg">
    <h1 class="text-2xl font-bold text-white mb-6">Registrar Nuevo Cliente</h1>

    @if ($errors->any())
        <div class="bg-red-500/20 border border-red-500/50 text-red-200 p-3 rounded-lg mb-6 text-sm">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>• {{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('customers.store') }}" method="POST" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium mb-1 text-slate-300">Nombre de la Empresa</label>
            <input type="text" name="company_name" value="{{ old('company_name') }}" required class="w-full px-4 py-2 bg-slate-900 border border-slate-700 rounded-lg focus:outline-none focus:border-indigo-500 text-slate-100">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1 text-slate-300">Datos Fiscales (RFC, Razón Social, etc.)</label>
            <textarea name="fiscal_data" rows="3" class="w-full px-4 py-2 bg-slate-900 border border-slate-700 rounded-lg focus:outline-none focus:border-indigo-500 text-slate-100">{{ old('fiscal_data') }}</textarea>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1 text-slate-300">Dirección de Entrega</label>
            <textarea name="delivery_address" rows="3" required class="w-full px-4 py-2 bg-slate-900 border border-slate-700 rounded-lg focus:outline-none focus:border-indigo-500 text-slate-100">{{ old('delivery_address') }}</textarea>
        </div>
        <div class="flex justify-end space-x-3 pt-4">
            <a href="{{ route('customers.index') }}" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-slate-200 rounded-lg transition-colors">
                Cancelar
            </a>
            <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold rounded-lg transition-colors">
                Guardar Cliente
            </button>
        </div>
    </form>
</div>
@endsection
