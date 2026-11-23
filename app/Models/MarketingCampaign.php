<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MarketingCampaign extends Model
{
    public const AUDIENCES = ['all' => 'Todos los clientes', 'top' => 'Los cinco clientes que más compran'];

    protected $fillable = ['name', 'starts_on', 'ends_on', 'audience', 'discount_percent'];

    protected $casts = ['starts_on' => 'date', 'ends_on' => 'date', 'discount_percent' => 'float', 'ended_at' => 'datetime'];

    public function scopeActive(Builder $query): Builder
    {
        $today = today()->toDateString();

        return $query->whereNull('ended_at')->whereDate('starts_on', '<=', $today)->whereDate('ends_on', '>=', $today);
    }

    public function scopeVisibleInPortal(Builder $query, ?int $customerId): Builder
    {
        $eligible = $customerId !== null && in_array($customerId, Customer::rankedByPurchases()->limit(5)->get()->modelKeys(), true);

        return $query->whereNull('ended_at')->whereDate('ends_on', '>=', today())
            ->when(!$eligible, fn (Builder $campaigns) => $campaigns->where('audience', 'all'));
    }

    public function status(): string
    {
        return match (true) {
            $this->ended_at !== null => 'Terminada antes de tiempo',
            $this->starts_on->gt(today()) => 'Programada',
            $this->ends_on->lt(today()) => 'Finalizada',
            default => 'Activa',
        };
    }
}
