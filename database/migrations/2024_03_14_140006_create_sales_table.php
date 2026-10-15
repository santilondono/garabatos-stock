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
        Schema::create('sales', function (Blueprint $table) {
            $table->integer('sale_id')->autoIncrement();
            $table->dateTime('sale_date');
            $table->integer('user_id');
            $table->integer('client_id');
            $table->boolean('is_cancelled')->default(false);

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('client_id')->references('client_id')->on('clients');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
