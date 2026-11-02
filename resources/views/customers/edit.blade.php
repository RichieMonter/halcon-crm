@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto bg-white rounded-xl shadow-md border border-gray-200 p-6">
    <div class="flex justify-between items-center mb-6 pb-3 border-b border-gray-100">
        <div>
            <h2 class="text-xl font-extrabold text-gray-800">Editar Cliente: {{ $customer->name }}</h2>
            <p class="text-xs text-gray-500">Actualiza los datos generales y fiscales</p>
        </div>
        <a href="{{ route('customers.index') }}" class="text-gray-500 hover:text-gray-700 text-sm font-semibold">← Volver al listado</a>
    </div>

    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-xl mb-6 text-sm">
            <span class="font-bold block mb-1">⚠️ Por favor corrige los siguientes errores:</span>
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('customers.update', $customer->id) }}" method="POST" class="space-y-4">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Nombre de Contacto *</label>
                <input type="text" name="name" value="{{ old('name', $customer->name) }}" required 
                       class="w-full bg-gray-50 border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:bg-white transition">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Correo Electrónico *</label>
                <input type="email" name="email" value="{{ old('email', $customer->email) }}" required 
                       class="w-full bg-gray-50 border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:bg-white transition">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Teléfono</label>
                <input type="text" name="phone" value="{{ old('phone', $customer->phone) }}" 
                       class="w-full bg-gray-50 border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:bg-white transition">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Razón Social / Empresa</label>
                <input type="text" name="company_name" value="{{ old('company_name', $customer->company_name) }}" 
                       class="w-full bg-gray-50 border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:bg-white transition">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">RFC</label>
                <input type="text" name="rfc" value="{{ old('rfc', $customer->rfc ?? $customer->tax_id) }}" maxlength="13" 
                       oninput="this.value = this.value.toUpperCase()" 
                       class="w-full bg-gray-50 border border-gray-300 rounded-lg p-2.5 text-sm uppercase focus:ring-2 focus:ring-indigo-500 focus:bg-white transition">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Régimen Fiscal</label>
                <select name="tax_regime" class="w-full bg-gray-50 border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:bg-white transition">
                    <option value="">Seleccionar régimen fiscal</option>
                    <option value="601" {{ old('tax_regime', $customer->tax_regime) == '601' ? 'selected' : '' }}>601 - General de Ley Personas Morales</option>
                    <option value="612" {{ old('tax_regime', $customer->tax_regime) == '612' ? 'selected' : '' }}>612 - Personas Físicas con Actividades Empresariales</option>
                    <option value="626" {{ old('tax_regime', $customer->tax_regime) == '626' ? 'selected' : '' }}>626 - Régimen Simplificado de Confianza (RESICO)</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Dirección Particular / Fiscal</label>
            <textarea name="address" rows="2" class="w-full bg-gray-50 border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:bg-white transition">{{ old('address', $customer->address) }}</textarea>
        </div>

        <div>
            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Dirección de Entrega</label>
            <textarea name="delivery_address" rows="2" class="w-full bg-gray-50 border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:bg-white transition">{{ old('delivery_address', $customer->delivery_address) }}</textarea>
        </div>

        <div class="pt-4 flex justify-end gap-3 border-t border-gray-100">
            <a href="{{ route('customers.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-5 py-2.5 rounded-lg text-sm font-semibold transition">Cancelar</a>
            <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow transition">Actualizar Cliente</button>
        </div>
    </form>
</div>
@endsection
