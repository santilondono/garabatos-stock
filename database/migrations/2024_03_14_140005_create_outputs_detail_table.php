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
        Schema::create('outputs_detail', function (Blueprint $table) {
            $table->integer('output_detail_id')->autoIncrement();
            $table->integer('output_id');
            $table->integer('product_id');
            $table->integer('quantity_taken_out');

            $table->foreign('output_id')->references('output_id')->on('outputs');
            $table->foreign('product_id')->references('product_id')->on('products');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('outputs_detail');
    }
};
