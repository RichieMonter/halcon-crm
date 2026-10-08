@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto p-6 bg-slate-800 text-white rounded-lg shadow-md">
    <h2 class="text-2xl font-bold mb-6">Nuevo Cliente</h2>

    @if ($errors->any())
        <div class="bg-red-600 text-white p-4 rounded mb-6">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('customers.store') }}" method="POST" novalidate>
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block mb-1">Nombre de Contacto *</label>
                <input type="text" name="name" value="{{ old('name') }}" required 
                       class="w-full bg-slate-700 p-2 rounded text-white border border-slate-600 focus:outline-none focus:border-blue-500">
            </div>

            <div>
                <label class="block mb-1">Correo Electrónico *</label>
                <input type="email" name="email" value="{{ old('email') }}" required 
                       class="w-full bg-slate-700 p-2 rounded text-white border border-slate-600 focus:outline-none focus:border-blue-500">
            </div>

            <div>
                <label class="block mb-1">Teléfono</label>
                <input type="text" name="phone" value="{{ old('phone') }}" 
                       placeholder="+52 5512345678"
                       class="w-full bg-slate-700 p-2 rounded text-white border border-slate-600 focus:outline-none focus:border-blue-500">
            </div>

            <div>
                <label class="block mb-1">Razón Social / Empresa</label>
                <input type="text" name="company_name" value="{{ old('company_name') }}" 
                       class="w-full bg-slate-700 p-2 rounded text-white border border-slate-600 focus:outline-none focus:border-blue-500">
            </div>

            <div>
                <label class="block mb-1">RFC</label>
                <input type="text" name="rfc" value="{{ old('rfc') }}" maxlength="13" 
                       oninput="this.value = this.value.toUpperCase()" 
                       placeholder="XAXX010101000"
                       class="w-full bg-slate-700 p-2 rounded text-white uppercase border border-slate-600 focus:outline-none focus:border-blue-500">
            </div>

            <div>
                <label class="block mb-1">Régimen Fiscal *</label>
                <select name="tax_regime" required class="w-full bg-slate-700 p-2 rounded text-white border border-slate-600 focus:outline-none focus:border-blue-500">
                    <option value="">Seleccionar régimen fiscal</option>
                    <option value="601" {{ old('tax_regime') == '601' ? 'selected' : '' }}>601 - General de Ley Personas Morales</option>
                    <option value="612" {{ old('tax_regime') == '612' ? 'selected' : '' }}>612 - Personas Físicas con Actividades Empresariales</option>
                    <option value="626" {{ old('tax_regime') == '626' ? 'selected' : '' }}>626 - Régimen Simplificado de Confianza (RESICO)</option>
                </select>
            </div>
        </div>

        <div class="mb-4">
            <label class="block mb-1">Dirección Particular / Fiscal</label>
            <textarea name="address" rows="2" class="w-full bg-slate-700 p-2 rounded text-white border border-slate-600 focus:outline-none focus:border-blue-500">{{ old('address') }}</textarea>
        </div>

        <div class="mb-6">
            <label class="block mb-1">Dirección de Entrega</label>
            <textarea name="delivery_address" rows="2" class="w-full bg-slate-700 p-2 rounded text-white border border-slate-600 focus:outline-none focus:border-blue-500">{{ old('delivery_address') }}</textarea>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('customers.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded">Cancelar</a>
            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded font-semibold">Crear Cliente</button>
        </div>
    </form>
</div>
@endsection
