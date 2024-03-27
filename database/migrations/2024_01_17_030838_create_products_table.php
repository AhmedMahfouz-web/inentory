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
            $table->string('name');
            $table->string('code');
            $table->unsignedInteger('stock')->default(0);
            $table->unsignedDecimal('price', $scale = 20)->default(0);
            $table->unsignedInteger('min_stock')->nullable();
            $table->unsignedInteger('max_stock')->nullable();
            $table->foreignId('category_id')->references('id')->on('categories');
            $table->foreignId('unit_id')->references('id')->on('units');
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
