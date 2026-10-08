<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $primaryKey = 'customer_number';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'delivery_address',
        'company_name',
        'rfc',
        'tax_regime',
    ];
}
