<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrinterReading extends Model
{
    public $timestamps = false;

    protected $fillable = ['printer', 'color', 'level', 'recorded_on', 'is_refill', 'captured_at'];

    protected $casts = ['level' => 'float', 'recorded_on' => 'date', 'is_refill' => 'boolean'];

    public const PRINTERS = [
        'esca' => ['name' => 'Lexmark MX711 Esca', 'short' => 'Esca', 'colors' => ['negro']],
        'zurah' => ['name' => 'Lexmark MX711 Zurah', 'short' => 'Zurah', 'colors' => ['negro']],
        'epson' => ['name' => 'Epson EcoTank L4150', 'short' => 'Epson', 'colors' => ['negro', 'cian', 'magenta', 'amarillo']],
        'ricoh' => ['name' => 'Ricoh IM C2510 a color', 'short' => 'Ricoh IM C2510', 'sku' => '419348', 'colors' => ['negro', 'cian', 'magenta', 'amarillo']],
    ];

    public const COLORS = ['negro' => 'Negro', 'cian' => 'Cian (azul)', 'magenta' => 'Magenta (rosa)', 'amarillo' => 'Amarillo'];

    private const PAGE_YIELDS = [
        'esca' => ['mono' => 25000],
        'zurah' => ['mono' => 25000],
        'epson' => ['mono' => 7500, 'color' => 6000],
        'ricoh' => ['mono' => 16500, 'color' => 10500],
    ];

    public static function levels(): array
    {
        $printers = self::PRINTERS;

        foreach ($printers as $key => &$printer) {
            $printed = OrderItem::where('printer', $key)->whereNotNull('printed_at');
            $printer['pages'] = (clone $printed)->sum('quantity');

            foreach ($printer['colors'] as $color) {
                $readings = self::where('printer', $key)->where('color', $color)
                    ->orderByDesc('recorded_on')->orderByDesc('id')->limit(2)->get();
                $reading = $readings->first();
                $printer['levels'][$color] = ['reading' => $reading, 'days' => $reading?->daysUntilLow($readings->get(1))];

                if ($reading) {
                    $cutoff = $reading->captured_at ?? $reading->recorded_on?->endOfDay()->format('Y-m-d H:i:s.u');
                    $pages = (clone $printed)->when($cutoff, fn ($query) => $query->where('printed_at', '>', $cutoff))
                        ->selectRaw('print_mode, SUM(quantity) AS pages')->groupBy('print_mode')->pluck('pages', 'print_mode');
                    $spent = 0;
                    foreach (self::PAGE_YIELDS[$key] as $mode => $yield) {
                        if ($color === 'negro' || $mode === 'color') {
                            $spent += 100 * ($pages[$mode] ?? 0) / $yield;
                        }
                    }
                    $reading->level = round(max(0, $reading->level - $spent), 4);
                }
            }
        }

        return $printers;
    }

    public function status(): string
    {
        return match (true) {
            $this->level <= 30 => 'Bajo',
            $this->level <= 70 => 'Medio',
            default => 'Alto',
        };
    }

    public function indicatorColor(): string
    {
        return match ($this->status()) {
            'Bajo' => '#b91c1c',
            'Medio' => '#a16207',
            'Alto' => '#047857',
        };
    }

    private function daysUntilLow(?self $previous): ?int
    {
        if (!$previous?->recorded_on || !$this->recorded_on || $this->is_refill || $this->level >= $previous->level) {
            return null;
        }

        $elapsed = $previous->recorded_on->diffInDays($this->recorded_on);

        if ($elapsed <= 0 || $this->level <= 30) {
            return null;
        }

        $daysFromReading = ($this->level - 30) * $elapsed / ($previous->level - $this->level);

        return max(0, (int) ceil($daysFromReading - $this->recorded_on->diffInDays(today())));
    }
}
