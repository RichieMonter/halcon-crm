<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'Administrador',
            'Ventas',
            'Soporte',
            'Gerencia',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['department_name' => $role]);
        }
    }
}