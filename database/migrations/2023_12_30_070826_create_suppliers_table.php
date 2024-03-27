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
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('desc')->nullable();
            $table->string('address')->nullable();
            $table->string('segel_togary')->nullable();
            $table->string('segel_togary_image')->nullable();
            $table->string('betaqa_drebya')->nullable();
            $table->string('betaqa_drebya_image')->nullable();
            $table->boolean('has_delivery')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
