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
        Schema::create('product_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique(); // Auto-generated request number
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('requested_by'); // User who made the request
            $table->enum('status', ['pending', 'approved', 'rejected', 'partially_approved', 'fulfilled', 'cancelled'])
                  ->default('pending');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->text('notes')->nullable(); // Branch notes
            $table->text('warehouse_notes')->nullable(); // Warehouse keeper notes
            $table->unsignedBigInteger('approved_by')->nullable(); // Warehouse keeper who approved
            $table->unsignedBigInteger('fulfilled_by')->nullable(); // Who fulfilled the request
            $table->timestamp('requested_at');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('fulfilled_at')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            $table->foreign('requested_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('fulfilled_by')->references('id')->on('users')->onDelete('set null');

            // Indexes
            $table->index(['branch_id', 'status']);
            $table->index(['status', 'priority']);
            $table->index(['requested_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_requests');
    }
};
