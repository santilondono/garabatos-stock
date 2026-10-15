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
        Schema::create('entries_detail', function (Blueprint $table) {
            $table->integer('entry_detail_id')->autoIncrement();
            $table->integer('entry_id');
            $table->integer('product_id');
            $table->integer('quantity_entered');
            $table->decimal('purchase_price', 10, 3);

            $table->foreign('entry_id')->references('entry_id')->on('entries');
            $table->foreign('product_id')->references('product_id')->on('products');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entries_detail');
    }
};
