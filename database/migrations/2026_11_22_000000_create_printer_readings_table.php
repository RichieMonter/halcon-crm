<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('printer_readings', function (Blueprint $table) {
            $table->id();
            $table->string('printer', 30);
            $table->string('color', 12);
            $table->unsignedTinyInteger('level');
            $table->date('recorded_on')->nullable();
            $table->boolean('is_refill')->default(false);
            $table->index(['printer', 'color', 'recorded_on']);
        });

        DB::table('printer_readings')->insert([
            ['printer' => 'esca', 'color' => 'negro', 'level' => random_int(10, 30)],
            ['printer' => 'zurah', 'color' => 'negro', 'level' => random_int(31, 70)],
            ['printer' => 'epson', 'color' => 'negro', 'level' => random_int(71, 100)],
            ['printer' => 'epson', 'color' => 'cian', 'level' => random_int(10, 30)],
            ['printer' => 'epson', 'color' => 'magenta', 'level' => random_int(31, 70)],
            ['printer' => 'epson', 'color' => 'amarillo', 'level' => random_int(71, 100)],
            ['printer' => 'ricoh', 'color' => 'negro', 'level' => random_int(71, 100)],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('printer_readings');
    }
};
