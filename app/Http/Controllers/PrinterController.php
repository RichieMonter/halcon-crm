<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use App\Models\PrinterReading;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PrinterController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->validate([
            'hasta' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:today'],
        ], [
            'hasta.date_format' => 'Ingresa una fecha válida.',
            'hasta.before_or_equal' => 'El periodo debe terminar hoy o antes.',
        ]);
        $end = CarbonImmutable::parse($date['hasta'] ?? today()->toDateString())->startOfDay();
        $start = $end->subDays(6);
        $usage = OrderItem::query()
            ->select('order_id', 'printer')
            ->selectRaw('DATE(printed_at) as print_day, SUM(quantity) as pages')
            ->whereIn('printer', array_keys(PrinterReading::PRINTERS))
            ->where('printed_at', '>=', $start)
            ->where('printed_at', '<', $end->addDay())
            ->whereHas('order', fn ($query) => $query->where('status', 'completado'))
            ->with('order.customer')
            ->groupByRaw('DATE(printed_at), printer, order_id')
            ->orderByDesc('print_day')
            ->orderByDesc('order_id')
            ->get();

        $dailyTotals = $usage->groupBy('print_day');
        $usageRows = collect(range(0, 6))->map(function (int $offset) use ($start, $dailyTotals) {
            $day = $start->addDays($offset);
            $entries = $dailyTotals->get($day->toDateString(), collect());

            return [
                'label' => $day->format('d/m'),
                'values' => array_map(fn ($printer) => $entries->where('printer', $printer)->sum('pages'), array_keys(PrinterReading::PRINTERS)),
            ];
        })->all();

        return view('printers.index', [
            'printers' => PrinterReading::levels(),
            'usage' => $usage,
            'usageRows' => $usageRows,
            'usageEnd' => $end,
            'refills' => PrinterReading::where('is_refill', true)
                ->orderByDesc('recorded_on')->orderByDesc('id')->paginate(10),
        ]);
    }

    public function store(Request $request, string $printer)
    {
        $device = PrinterReading::PRINTERS[$printer] ?? null;
        abort_if($device === null, 404);

        $data = $request->validateWithBag($printer, [
            'color' => ['required', Rule::in($device['colors'])],
            'level' => ['required', 'integer', 'min:0', 'max:100'],
            'recorded_on' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'is_refill' => ['required', 'boolean'],
        ], [
            'recorded_on.date_format' => 'Ingresa una fecha válida.',
            'recorded_on.before_or_equal' => 'La fecha debe ser hoy o anterior.',
            'level.max' => 'El nivel máximo es 100 %.',
            'is_refill.boolean' => 'Selecciona el tipo de registro.',
        ], ['color' => 'consumible', 'level' => 'nivel', 'recorded_on' => 'fecha', 'is_refill' => 'tipo de registro']);

        PrinterReading::create([
            'printer' => $printer,
            'captured_at' => $data['recorded_on'] === today()->toDateString()
                ? now()->format('Y-m-d H:i:s.u')
                : null,
        ] + $data);

        return redirect()->route('printers.index')->with('success', 'Registro guardado para '.$device['name'].'.');
    }
}
