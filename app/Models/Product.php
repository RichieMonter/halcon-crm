<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}