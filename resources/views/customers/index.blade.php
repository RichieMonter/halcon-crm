@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto p-6 bg-slate-800 text-white rounded-lg shadow-md">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">Listado de Clientes</h2>
        <a href="{{ route('customers.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded font-semibold">
            + Nuevo Cliente
        </a>
    </div>

    @if (session('success'))
        <div class="bg-green-600 text-white p-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-slate-700 text-gray-400 text-sm">
                    <th class="p-3"># CLIENTE</th>
                    <th class="p-3">CONTACTO</th>
                    <th class="p-3">RAZÓN SOCIAL / RFC</th>
                    <th class="p-3">TELÉFONO / EMAIL</th>
                    <th class="p-3 text-right">ACCIONES</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($customers as $customer)
                    <tr class="border-b border-slate-700 hover:bg-slate-750">
                        <td class="p-3 font-semibold text-blue-400">#{{ $customer->customer_number }}</td>
                        <td class="p-3 font-medium">{{ $customer->name }}</td>
                        <td class="p-3">
                            <div class="font-medium">
                                {{ !empty($customer->company_name) ? $customer->company_name : 'N/A' }}
                            </div>
                            <div class="text-xs text-gray-400">
                                {{ $customer->rfc ?? 'Sin RFC' }}
                            </div>
                        </td>
                        <td class="p-3">
                            <div>{{ $customer->phone ?? 'S/N' }}</div>
                            <div class="text-xs text-gray-400">{{ $customer->email }}</div>
                        </td>
                        <td class="p-3 text-right space-x-2">
                            <a href="{{ route('customers.edit', $customer) }}" class="text-blue-400 hover:underline">Editar</a>
                            <form action="{{ route('customers.destroy', $customer) }}" method="POST" class="inline" onsubmit="return confirm('¿Seguro que deseas eliminar este cliente?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-400 hover:underline">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-4 text-center text-gray-400">No hay clientes registrados aún.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $customers->links() }}
    </div>
</div>
@endsection