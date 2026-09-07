<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = ['branch_id', 'created_at', 'created_by', 'updated_by'];

    protected static function booted()
    {
        static::creating(function ($order) {
            if (auth()->check()) {
                $order->created_by = $order->created_by ?? auth()->id();
                $order->updated_by = $order->updated_by ?? auth()->id();
            }
        });

        static::updating(function ($order) {
            if (auth()->check()) {
                $order->updated_by = auth()->id();
            }
        });
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function product_added()
    {
        return $this->hasMany(ProductAdded::class, 'order_id', 'id');
    }
}
