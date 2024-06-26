<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductAdded extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'product_id',
        'qty',
        'price',
        'order_id',
        'created_at', 'created_by', 'updated_by'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
