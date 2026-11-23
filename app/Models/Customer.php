<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $table = 'customers';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'delivery_address',
        'company_name',
        'rfc',
        'tax_regime',
        'preferred_discount',
    ];

    protected $casts = ['preferred_discount' => 'float'];

    public function scopeRankedByPurchases(Builder $query): Builder
    {
        $completed = fn ($orders) => $orders->where('status', 'completado');

        return $query
            ->whereHas('orders', fn ($orders) => $completed($orders)->where('total', '>', 0))
            ->withSum(['orders as purchase_total' => $completed], 'total')
            ->withCount(['orders as purchase_count' => $completed])
            ->orderByDesc('purchase_total')
            ->orderByDesc('purchase_count')
            ->orderBy('id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'customer_id', 'id');
    }
}
