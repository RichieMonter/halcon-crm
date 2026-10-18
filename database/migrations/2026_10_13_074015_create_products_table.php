<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Código o SKU (ej. CUAD-01)
            $table->string('name'); // Nombre del producto, servicio o insumo
            $table->enum('type', ['PRODUCTO', 'SERVICIO', 'INSUMO'])->default('PRODUCTO'); // Tipo para control de stock
            $table->string('category')->nullable(); // Categoría (Papelería, Mercería, etc.)
            $table->decimal('price', 10, 2)->default(0.00); // Precio de venta
            $table->decimal('cost', 10, 2)->default(0.00); // Costo interno
            $table->integer('stock')->default(0); // Cantidad actual
            $table->integer('min_stock')->default(5); // Stock mínimo para alertas del Dashboard
            $table->boolean('is_active')->default(true); // Activo / Inactivo
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};