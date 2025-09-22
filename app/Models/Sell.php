<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sell extends Model
{
    use HasFactory;

    protected $fillable = ['product_branch_id', 'qty', 'created_at'];

    /**
     * Get the product branch that owns the sell
     */
    public function product_branch(): BelongsTo
    {
        return $this->belongsTo(Product_branch::class, 'product_branch_id');
    }

    /**
     * Get the product through product_branch relationship
     */
    public function product()
    {
        return $this->hasOneThrough(Product::class, Product_branch::class, 'id', 'id', 'product_branch_id', 'product_id');
    }

    /**
     * Get the branch through product_branch relationship
     */
    public function branch()
    {
        return $this->hasOneThrough(Branch::class, Product_branch::class, 'id', 'id', 'product_branch_id', 'branch_id');
    }
}
