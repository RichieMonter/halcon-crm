@php
    $peak = collect($rows)->flatMap(fn ($row) => $row['values'])->max() ?? 0;
    $step = isset($maximum) ? $maximum / 4 : max(1, ceil($peak / 4));
    $ceiling = 4 * $step;
    $colors = array_values($series);
    $names = array_keys($series);
@endphp

<figure class="min-w-0 {{ ($compact ?? false) ? 'p-4' : 'rounded-xl border border-gray-200 bg-white p-5 shadow-sm' }}">
    <figcaption>
        <h2 class="font-bold text-slate-900">{{ $title }}</h2>
        <p class="mb-5 text-xs text-gray-500">{{ $unit }}</p>
    </figcaption>

    @if ($rows)
        <div class="flex items-start gap-2">
            <div aria-hidden="true" class="relative h-40 shrink-0 text-right text-[11px] tabular-nums text-slate-600">
                <span class="invisible">{{ number_format($ceiling) }}</span>
                @foreach (range(4, 0) as $tick)
                    <span class="absolute right-0 translate-y-1/2" style="bottom: {{ $tick * 25 }}%">{{ number_format($tick * $step) }}</span>
                @endforeach
            </div>

            <div tabindex="0" aria-label="{{ $title }}" class="min-w-0 flex-1 overflow-x-auto">
                <div class="relative flex" style="min-width: {{ count($rows) * 64 }}px">
                    <div aria-hidden="true" class="pointer-events-none absolute inset-x-0 top-0 h-40 border-b border-l border-slate-500">
                        @foreach (range(1, 4) as $tick)
                            <div class="absolute inset-x-0 border-t border-slate-200" style="bottom: {{ $tick * 25 }}%"></div>
                        @endforeach
                    </div>

                    @foreach ($rows as $row)
                        <div class="relative min-w-0 flex-1 text-center">
                            <div aria-hidden="true" class="flex h-40 items-end justify-center gap-1 px-2">
                                @foreach ($row['values'] as $index => $value)
                                    <div class="w-full max-w-8 rounded-t-sm" style="height: {{ 100 * max(0, $value) / $ceiling }}%; background-color: {{ $row['color'] ?? $colors[$index] }}"></div>
                                @endforeach
                            </div>
                            <div class="px-1 pb-2 pt-2 text-[11px] leading-tight text-slate-700">
                                <span class="block break-words font-medium">{{ $row['label'] }}</span>
                                @isset($row['type'])
                                    <span class="block text-slate-500">{{ $row['type'] }}</span>
                                @endisset
                                <span class="mt-1 flex flex-wrap justify-center gap-x-1 font-semibold tabular-nums">
                                    @foreach ($row['values'] as $index => $value)
                                        <span class="kopion-chart-value" style="--kopion-data-color: {{ $row['color'] ?? $colors[$index] }}">
                                            @unless(isset($row['color']))
                                                <span class="sr-only">{{ $names[$index] }} </span>
                                            @endunless
                                            {{ number_format($value, $decimals ?? 0) }}{{ $suffix ?? '' }}
                                        </span>
                                    @endforeach
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <ul class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-600">
            @foreach ($series as $name => $color)
                <li class="flex items-center gap-1.5">
                    <span aria-hidden="true" class="h-2.5 w-2.5 rounded-sm" style="background-color: {{ $color }}"></span>
                    {{ $name }}
                </li>
            @endforeach
        </ul>
    @else
        <p class="text-sm text-slate-600">{{ $empty }}</p>
    @endif
</figure>
