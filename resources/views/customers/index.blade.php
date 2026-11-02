@extends('layouts.app')

@section('content')
<div class="bg-white rounded-xl shadow-md border border-gray-200 p-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center pb-6 mb-6 border-b border-gray-100 gap-4">
        <div>
            <h2 class="text-xl font-extrabold text-gray-800">Catálogo de Clientes</h2>
            <p class="text-xs text-gray-500">Gestión completa de contactos, datos fiscales y direcciones</p>
        </div>
        
        <a href="{{ route('customers.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs px-4 py-2.5 rounded-lg shadow transition flex items-center">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Agregar cliente
        </a>
    </div>

    <!-- BUSCADOR MODULAR DE CLIENTES -->
    <form action="{{ route('customers.index') }}" method="GET" class="mb-6 flex gap-2">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="🔍 Buscar cliente por nombre, empresa o RFC..." 
               class="w-full md:w-1/3 bg-gray-50 border border-gray-300 rounded-lg p-2.5 text-xs focus:ring-2 focus:ring-indigo-500">
        <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white px-4 py-2.5 rounded-lg text-xs font-bold transition">Buscar</button>
        @if(request('search'))
            <a href="{{ route('customers.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-3 py-2.5 rounded-lg text-xs font-bold flex items-center">Limpiar Filtro</a>
        @endif
    </form>

    <!-- TABLA DE CLIENTES -->
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200 text-xs font-bold text-gray-500 uppercase tracking-wider">
                    <th class="py-3 px-4">#</th>
                    <th class="py-3 px-4">Nombre / Contacto</th>
                    <th class="py-3 px-4">Razón Social / RFC</th>
                    <th class="py-3 px-4">Régimen Fiscal</th>
                    <th class="py-3 px-4">Contacto (Tel / Email)</th>
                    <th class="py-3 px-4">Dirección Fiscal / Entrega</th>
                    <th class="py-3 px-4 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-sm">
                @forelse($customers as $cust)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="py-3 px-4 font-bold text-indigo-600 text-xs">#{{ $cust->id }}</td>
                        <td class="py-3 px-4 font-semibold text-gray-800">{{ $cust->name }}</td>
                        <td class="py-3 px-4 text-xs text-gray-600">
                            <span class="font-bold block text-gray-900">
                                {{ ($cust->company_name && strtoupper($cust->company_name) !== 'NA') ? $cust->company_name : $cust->name }}
                            </span>
                            <span class="text-[11px] text-indigo-600 font-mono">{{ $cust->rfc ?? 'Sin RFC' }}</span>
                        </td>
                        <td class="py-3 px-4 text-xs">
                            @if($cust->tax_regime)
                                <span class="bg-slate-100 text-slate-700 px-2 py-0.5 rounded border border-slate-200 font-bold">
                                    {{ $cust->tax_regime }}
                                </span>
                            @else
                                <span class="text-gray-400 italic">No especificado</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-xs text-gray-600">
                            <span class="block">{{ $cust->phone ?? 'Sin teléfono' }}</span>
                            <span class="text-gray-400">{{ $cust->email ?? 'Sin correo' }}</span>
                        </td>
                        <td class="py-3 px-4 text-xs text-gray-500 max-w-xs truncate" title="{{ $cust->address }}">
                            <span class="block font-semibold text-gray-700">Fiscal:</span> {{ $cust->address ?? 'No registrada' }}
                            @if($cust->delivery_address)
                                <span class="block font-semibold text-gray-700 mt-1">Entrega:</span> {{ $cust->delivery_address }}
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center space-x-1 whitespace-nowrap">
                            <a href="{{ route('customers.edit', $cust->id) }}" class="inline-flex items-center bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 px-2.5 py-1.5 rounded-lg text-xs font-bold transition shadow-sm" title="Editar">
                                ✏️ Editar
                            </a>

                            <form action="{{ route('customers.destroy', $cust->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Seguro que deseas eliminar este cliente?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 px-2.5 py-1.5 rounded-lg text-xs font-bold transition shadow-sm" title="Eliminar">
                                    🗑️ Eliminar
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-gray-400 text-sm">
                            No se encontraron clientes coincidentes.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
