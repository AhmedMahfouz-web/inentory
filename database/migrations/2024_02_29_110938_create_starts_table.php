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
        Schema::create('starts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_branch_id')->references('id')->on('product_branches');
            $table->unsignedMediumInteger('qty');
            $table->timestamp('month');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('starts');
    }
};
