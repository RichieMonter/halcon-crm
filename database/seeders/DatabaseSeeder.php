<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::table('roles')->insert([
            ['department_name' => 'Administrador', 'created_at' => now(), 'updated_at' => now()],
            ['department_name' => 'Ventas', 'created_at' => now(), 'updated_at' => now()],
            ['department_name' => 'Almacén', 'created_at' => now(), 'updated_at' => now()],
            ['department_name' => 'Facturación', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}