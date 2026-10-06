@extends('layouts.app')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-white">Listado de Clientes</h1>
    <a href="{{ route('customers.create') }}" class="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded-lg font-medium transition-colors">
        + Nuevo Cliente
    </a>
</div>

<div class="bg-slate-800 border border-slate-700 rounded-xl overflow-hidden shadow-lg">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-slate-700/50 border-b border-slate-700 text-slate-300 text-sm">
                <th class="p-4">No. Cliente</th>
                <th class="p-4">Nombre de la Empresa</th>
                <th class="p-4">Datos Fiscales</th>
                <th class="p-4">Dirección de Entrega</th>
                <th class="p-4 text-center">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-700 text-slate-200 text-sm">
            @forelse ($customers as $customer)
                <tr class="hover:bg-slate-700/30 transition-colors">
                    <td class="p-4 font-mono text-indigo-300">#{{ $customer->customer_number }}</td>
                    <td class="p-4 font-semibold text-white">{{ $customer->company_name }}</td>
                    <td class="p-4 text-slate-400">{{ $customer->fiscal_data ?? 'N/A' }}</td>
                    <td class="p-4 text-slate-300">{{ $customer->delivery_address }}</td>
                    <td class="p-4 text-center">
                        <div class="flex items-center justify-center space-x-2">
                            <a href="{{ route('customers.edit', $customer) }}" class="bg-amber-600/80 hover:bg-amber-600 text-white px-3 py-1 rounded text-xs">
                                Editar
                            </a>
                            <form action="{{ route('customers.destroy', $customer) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar este cliente?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-600/80 hover:bg-red-600 text-white px-3 py-1 rounded text-xs">
                                    Eliminar
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="p-8 text-center text-slate-400">
                        No hay clientes registrados aún.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $customers->links() }}
</div>
@endsection
