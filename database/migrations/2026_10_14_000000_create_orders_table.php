<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            
            // Campos de auditoría y tipo
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('order_type')->nullable();

            $table->string('order_number')->unique();
            $table->string('invoice_number')->nullable();
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            
            // ¡Aquí agregamos oficialmente el nivel de interés!
            $table->string('interest_level', 50)->nullable()->default('MEDIO');

            $table->enum('status', ['completado', 'pendiente', 'cancelado'])->default('completado');
            $table->text('service_notes')->nullable(); 
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
