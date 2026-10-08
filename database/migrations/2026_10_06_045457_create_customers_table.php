<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id('customer_number'); // Clave primaria
            $table->string('name');                      // Nombre del contacto principal
            $table->string('email')->nullable();         // Correo de contacto
            $table->string('phone')->nullable();         // Teléfono de contacto
            $table->text('address')->nullable();         // Dirección fiscal/de contacto
            $table->text('delivery_address');            // Dirección de entrega
            
            // Datos fiscales SAT
            $table->string('company_name')->nullable();  // Razón Social
            $table->string('rfc', 13)->nullable();       // RFC
            $table->string('tax_regime')->nullable();    // Régimen Fiscal
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};