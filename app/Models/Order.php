<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = ['branch_id', 'created_at'];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function product_added()
    {
        return $this->hasMany(ProductAdded::class, 'order_id', 'id');
    }
}
