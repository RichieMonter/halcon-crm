<label for="preferred-discount" class="block text-sm font-medium text-slate-700">
    Descuento personalizado (%)
    <input id="preferred-discount" name="preferred_discount" type="number" min="0" max="100" step="0.01"
        value="{{ old('preferred_discount', $preferredDiscount) }}" class="mt-1 block w-full max-w-xs rounded-lg border border-slate-300 p-2">
    <span class="mt-1 block text-xs text-slate-500">Se sugiere en el carrito cuando seleccionas a este cliente.</span>
</label>
@error('preferred_discount')
    <p role="alert" class="text-sm text-red-700">{{ $message }}</p>
@enderror
