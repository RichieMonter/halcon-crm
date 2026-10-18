<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('roles')->insert([
            ['id' => 1, 'department_name' => 'Administrador'],
            ['id' => 2, 'department_name' => 'Editor'],
            ['id' => 3, 'department_name' => 'Colaborador'],
            ['id' => 4, 'department_name' => 'Usuario Final'],
        ]);
    }
}