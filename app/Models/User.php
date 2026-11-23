<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    public const CUSTOMER_ROLE = 4;

    public const STAFF_ROLES = [1 => 'Administrador', 2 => 'Editor', 3 => 'Colaborador'];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role_id',
        'customer_registration',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'customer_registration',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'customer_registration' => 'array',
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function homeRoute(): string
    {
        return (int) $this->role_id === self::CUSTOMER_ROLE ? 'portal.home' : 'orders.index';
    }

    public function roleLabel(): string
    {
        return self::STAFF_ROLES[$this->role_id] ?? 'Usuario Final';
    }
}
