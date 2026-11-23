<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'type',
        'category',
        'price',
        'cost',
        'stock',
        'min_stock',
        'is_active'
    ];

    public function isPrintingService(): bool
    {
        return $this->type === 'SERVICIO' && Str::contains(Str::lower(Str::ascii($this->category ?? '')), 'impresion');
    }
}
