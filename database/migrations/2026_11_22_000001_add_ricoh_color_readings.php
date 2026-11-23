<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('printer_readings')->insert([
            ['printer' => 'ricoh', 'color' => 'cian', 'level' => random_int(10, 30)],
            ['printer' => 'ricoh', 'color' => 'magenta', 'level' => random_int(31, 70)],
            ['printer' => 'ricoh', 'color' => 'amarillo', 'level' => random_int(71, 100)],
        ]);
    }

    public function down(): void
    {
        DB::table('printer_readings')
            ->where('printer', 'ricoh')
            ->whereIn('color', ['cian', 'magenta', 'amarillo'])
            ->whereNull('recorded_on')
            ->delete();
    }
};
