@if ($errors->getBag('campaign')->any())
    <ul role="alert" class="mt-3 text-sm text-red-700">
        @foreach ($errors->getBag('campaign')->all() as $message)
            <li>{{ $message }}</li>
        @endforeach
    </ul>
@endif

<form method="POST" action="{{ $campaign ? route('customers.campaigns.update', $campaign) : route('customers.campaigns.store') }}"
    enctype="multipart/form-data"
    class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
    @csrf
    @if ($campaign)
        @method('PUT')
    @endif
    <label class="text-sm font-medium text-slate-700 sm:col-span-2 xl:col-span-4">Nombre de la campaña
        <input name="name" value="{{ old('name', $campaign?->name) }}" required maxlength="120" placeholder="Regreso a clases"
            class="mt-1 block w-full rounded-lg border border-slate-300 p-2">
    </label>
    <label class="text-sm font-medium text-slate-700">Inicio
        <input type="date" name="starts_on" value="{{ old('starts_on', $campaign?->starts_on->toDateString() ?? today()->toDateString()) }}" required
            class="mt-1 block w-full rounded-lg border border-slate-300 p-2">
    </label>
    <label class="text-sm font-medium text-slate-700">Fin
        <input type="date" name="ends_on" value="{{ old('ends_on', $campaign?->ends_on->toDateString()) }}" required
            class="mt-1 block w-full rounded-lg border border-slate-300 p-2">
    </label>
    <label class="text-sm font-medium text-slate-700">Público
        <select name="audience" class="mt-1 block w-full rounded-lg border border-slate-300 p-2">
            @foreach ($audiences as $value => $label)
                <option value="{{ $value }}" @selected(old('audience', $campaign?->audience) === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </label>
    <label class="text-sm font-medium text-slate-700">Descuento (%)
        <input type="number" name="discount_percent" value="{{ old('discount_percent', $campaign?->discount_percent) }}" min="1" max="100" step="0.01" required
            class="mt-1 block w-full rounded-lg border border-slate-300 p-2">
    </label>
    @if ($campaign)
        <div class="space-y-2 sm:col-span-2 xl:col-span-4">
            @if ($campaign->image_path)
                <img src="{{ route('campaigns.image', $campaign) }}" alt="Imagen actual de la campaña"
                    class="h-28 max-w-xs rounded-lg border border-slate-200 object-contain">
            @endif
            <label for="campaign-image" class="block text-sm font-medium text-slate-700">
                {{ $campaign->image_path ? 'Reemplazar imagen de la campaña' : 'Agregar imagen a la campaña' }}
            </label>
            <input id="campaign-image" type="file" name="image" accept="image/jpeg,image/png,image/webp"
                class="block w-full max-w-md rounded-lg border border-slate-300 p-2 text-sm">
            <p class="text-xs text-slate-500">JPG, PNG o WebP. Máximo 2 MB.</p>
        </div>
    @endif
    <button class="justify-self-start rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white sm:col-span-2 xl:col-span-4">{{ $campaign ? 'Guardar cambios' : 'Guardar campaña' }}</button>
</form>
