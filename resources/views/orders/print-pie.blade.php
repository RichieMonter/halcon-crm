@php
    $total = array_sum($counts);
    $end = 0;
    $segments = [];
    foreach ($colors as $label => $color) {
        if ($total && $counts[$label] > 0) {
            $start = $end;
            $end += 100 * $counts[$label] / $total;
            $segments[] = "$color $start% $end%";
        }
    }
@endphp

<figure class="min-w-0 rounded-lg bg-slate-50 p-3">
    <figcaption class="mb-3 text-center text-sm font-semibold text-slate-800">{{ $title }}</figcaption>
    <div aria-hidden="true" class="mx-auto h-28 w-28 rounded-full bg-slate-200"
        @if ($segments) style="background: conic-gradient({{ implode(', ', $segments) }})" @endif></div>
    <ul class="mt-3 space-y-1 text-sm text-slate-700">
        @foreach ($colors as $label => $color)
            @continue($label === $unknown && !$counts[$label])
            <li class="flex items-center gap-2">
                <span aria-hidden="true" class="h-2.5 w-2.5 shrink-0 rounded-sm" style="background: {{ $color }}"></span>
                <span>{{ $label }}</span>
                <strong class="ml-auto tabular-nums">{{ number_format($counts[$label]) }}</strong>
            </li>
        @endforeach
    </ul>
</figure>
