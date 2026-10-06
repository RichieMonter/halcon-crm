<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $fillable = [
        'role_id',
        'username',
        'password_hash',
    ];

    protected $hidden = [
        'password_hash',
    ];

    public function getAuthPasswordName()
    {
        return 'password_hash';
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}