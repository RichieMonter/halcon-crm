<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', fn (Blueprint $table) => $table->decimal('preferred_discount', 5, 2)->nullable());
        Schema::table('orders', fn (Blueprint $table) => $table->string('payment_method', 20)->nullable());

        Schema::create('marketing_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->date('starts_on');
            $table->date('ends_on');
            $table->string('audience', 20);
            $table->decimal('discount_percent', 5, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_campaigns');
        Schema::table('orders', fn (Blueprint $table) => $table->dropColumn('payment_method'));
        Schema::table('customers', fn (Blueprint $table) => $table->dropColumn('preferred_discount'));
    }
};
