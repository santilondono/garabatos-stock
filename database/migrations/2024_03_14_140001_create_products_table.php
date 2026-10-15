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
            $table->integer('product_id')->autoIncrement();
            $table->string('product_reference', 30);
            $table->string('image', 300)->nullable();
            $table->string('list_description', 255)->nullable();
            $table->string('product_description', 255)->nullable();
            $table->decimal('purchase_price', 10, 3);
            $table->decimal('sale_price', 10, 3);
            $table->decimal('weight', 10, 3);
            $table->decimal('length', 10, 2);
            $table->decimal('width', 10, 2);
            $table->decimal('height', 10, 2);
            $table->decimal('cubic_meter', 10, 10);
            $table->integer('quantity');
            $table->integer('stock');
            $table->decimal('gross_revenue', 20, 3)->nullable();
            $table->boolean('active');
            $table->integer('comming')->default(0);
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
