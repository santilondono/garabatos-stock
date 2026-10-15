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
        Schema::create('sales_detail', function (Blueprint $table) {
            $table->integer('sale_datail_id')->autoIncrement();
            $table->integer('sale_id');
            $table->integer('product_id');
            $table->integer('quantity_sold');
            $table->decimal('sale_price', 10, 3);

            $table->foreign('sale_id')->references('sale_id')->on('sales');
            $table->foreign('product_id')->references('product_id')->on('products');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_detail');
    }
};
