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
            $table->string('invoice_number')->unique(); 
            
            // Definimos el ID del cliente y del usuario sin restricciones estrictas de MySQL
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('created_by_user_id');
            
            $table->string('order_type')->default('General'); 
            $table->decimal('total_amount', 10, 2)->default(0.00);
            $table->enum('interest_level', ['ALTO', 'MEDIO', 'BAJO'])->default('MEDIO');
            $table->date('last_contact_date')->nullable();
            $table->string('current_status')->default('PENDIENTE'); 
            $table->text('notes')->nullable();
            
            $table->softDeletes(); 
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};