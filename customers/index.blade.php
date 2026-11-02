<!-- Botón para ir al formulario de creación -->
<a href="{{ route('customers.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs px-4 py-2.5 rounded-lg shadow transition flex items-center">
    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
    Agregar cliente
</a>

<!-- Botón para ir a la edición en la tabla -->
<a href="{{ route('customers.edit', $cust->id) }}" class="inline-flex items-center bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 px-2.5 py-1.5 rounded-lg text-xs font-bold transition shadow-sm" title="Editar">
    ✏️ Editar
</a>
