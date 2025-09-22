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
        Schema::create('product_request_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_request_id');
            $table->unsignedBigInteger('product_id');
            $table->decimal('requested_qty', 10, 2); // Quantity requested
            $table->decimal('approved_qty', 10, 2)->nullable(); // Quantity approved by warehouse
            $table->decimal('fulfilled_qty', 10, 2)->default(0); // Quantity actually sent
            $table->decimal('unit_price', 10, 2)->nullable(); // Price at time of request
            $table->text('notes')->nullable(); // Item-specific notes
            $table->enum('status', ['pending', 'approved', 'rejected', 'fulfilled'])->default('pending');
            $table->timestamps();

            // Foreign keys
            $table->foreign('product_request_id')->references('id')->on('product_requests')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');

            // Indexes
            $table->index(['product_request_id']);
            $table->index(['product_id']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_request_items');
    }
};
