<div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
    @foreach (['phone' => 'Teléfono', 'company_name' => 'Razón social / Empresa', 'rfc' => 'RFC'] as $field => $label)
        <div>
            <label class="block text-xs font-semibold uppercase text-slate-700">{{ $label }}
                <input type="{{ $field === 'phone' ? 'tel' : 'text' }}" name="{{ $field }}" value="{{ old($field) }}"
                    maxlength="{{ ['phone' => 50, 'company_name' => 255, 'rfc' => 13][$field] }}"
                    class="mt-1 block w-full rounded-xl border border-slate-200 bg-slate-50 p-2.5 text-sm">
            </label>
            <x-input-error :messages="$errors->get($field)" class="mt-1 text-xs text-red-600" />
        </div>
    @endforeach
    <div>
        <label class="block text-xs font-semibold uppercase text-slate-700">Régimen fiscal
            <select name="tax_regime" class="mt-1 block w-full rounded-xl border border-slate-200 bg-slate-50 p-2.5 text-sm">
                <option value="">Seleccionar régimen fiscal</option>
                @foreach (['601' => 'General de Ley Personas Morales', '612' => 'Personas Físicas con Actividades Empresariales', '626' => 'Régimen Simplificado de Confianza (RESICO)'] as $code => $label)
                    <option value="{{ $code }}" @selected((string) old('tax_regime') === (string) $code)>{{ $code }} - {{ $label }}</option>
                @endforeach
            </select>
        </label>
        <x-input-error :messages="$errors->get('tax_regime')" class="mt-1 text-xs text-red-600" />
    </div>
    @foreach (['address' => 'Dirección particular / fiscal', 'delivery_address' => 'Dirección de entrega'] as $field => $label)
        <div class="sm:col-span-2">
            <label class="block text-xs font-semibold uppercase text-slate-700">{{ $label }}
                <textarea name="{{ $field }}" rows="2" maxlength="1000" class="mt-1 block w-full rounded-xl border border-slate-200 bg-slate-50 p-2.5 text-sm">{{ old($field) }}</textarea>
            </label>
            <x-input-error :messages="$errors->get($field)" class="mt-1 text-xs text-red-600" />
        </div>
    @endforeach
</div>
