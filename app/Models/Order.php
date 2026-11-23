<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    public const PAYMENT_METHODS = [
        'efectivo' => 'Efectivo',
        'transferencia' => 'Transferencia',
        'debito' => 'Tarjeta de débito',
        'credito' => 'Tarjeta de crédito',
    ];

    protected $fillable = [
        'customer_id',
        'created_by_user_id',
        'order_type',
        'order_number',
        'invoice_number',
        'subtotal',
        'discount',
        'discount_percent',
        'payment_method',
        'total',
        'status',
        'service_notes',
        'interest_level',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    // <-- Agregamos la relación que el controlador de tu amigo estaba exigiendo
    public function user()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
