<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('unit')->nullable();
            $table->string('product_id')->nullable();
            $table->decimal('buy_price', 12, 2);
            $table->decimal('stock');
            $table->decimal('sale_price', 12, 2);
            $table->decimal('discount_stock')->default(0);
            $table->decimal('discount_price')->default(0);
            $table->date('expire_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
