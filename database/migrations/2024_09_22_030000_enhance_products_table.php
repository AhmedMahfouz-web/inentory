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
        Schema::table('products', function (Blueprint $table) {
            // Add missing columns if they don't exist
            if (!Schema::hasColumn('products', 'description')) {
                $table->text('description')->nullable()->after('name');
            }
            
            if (!Schema::hasColumn('products', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('max_stock');
            }
            
            if (!Schema::hasColumn('products', 'deleted_at')) {
                $table->softDeletes();
            }
            
            // Add indexes for better performance
            $table->index(['is_active']);
            $table->index(['category_id', 'is_active']);
            $table->index(['stock', 'min_stock']);
            $table->index(['name']);
            $table->index(['code']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Drop indexes
            $table->dropIndex(['is_active']);
            $table->dropIndex(['category_id', 'is_active']);
            $table->dropIndex(['stock', 'min_stock']);
            $table->dropIndex(['name']);
            $table->dropIndex(['code']);
            $table->dropIndex(['created_at']);
            
            // Drop columns
            if (Schema::hasColumn('products', 'description')) {
                $table->dropColumn('description');
            }
            
            if (Schema::hasColumn('products', 'is_active')) {
                $table->dropColumn('is_active');
            }
            
            if (Schema::hasColumn('products', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};
