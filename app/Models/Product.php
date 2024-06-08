<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category_id',
        'unit_id',
        'stock',
        'code',
        'price',
        'min_stock',
        'max_stock',
    ];

    public function sub_category()
    {
        return $this->belongsTo(SubCategory::class,  'category_id', 'id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function start()
    {
        return $this->hasMany(Start_Inventory::class, 'product_id', 'id');
    }

    public function product_added()
    {
        return $this->hasMany(IncreasedProduct::class, 'product_id', 'id');
    }

    public function sell()
    {
        return $this->hasMany(ProductAdded::class, 'product_id', 'id');
    }

    public function qty($date)
    {
        $added = $this->product_added()->whereDate('created_at', '>=', $date . '-01')->whereDate('created_at', '<=', $date . '-31')->sum('qty');
        $start = $this->start()->whereDate('month', $date . '-01')->first();
        $sell = $this->sell()->whereDate('created_at', '>=', $date . '-01')->whereDate('created_at', '<=', $date . '-31')->sum('qty');

        if (empty($start)) {
            $total = $added - $sell;
        } else {
            $total = $start->qty + $added - $sell;
        }
        return $total;
    }
}
