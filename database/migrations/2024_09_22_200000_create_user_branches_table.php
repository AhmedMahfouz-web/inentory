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
        Schema::create('user_branches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->boolean('can_request')->default(true); // Can create product requests
            $table->boolean('can_manage')->default(false); // Can manage branch inventory
            $table->timestamps();

            // Ensure unique user-branch combinations
            $table->unique(['user_id', 'branch_id']);
            
            // Add indexes for better performance
            $table->index(['user_id', 'can_request']);
            $table->index(['branch_id', 'can_manage']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_branches');
    }
};
