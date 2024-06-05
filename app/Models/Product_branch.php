<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product_branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'branch_id',
        'qty',
        'price',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function product_added()
    {
        return $this->hasMany(ProductAdded::class, 'product_id', 'product_id');
    }

    public function start()
    {
        return $this->hasMany(Start::class, 'product_branch_id', 'id');
    }

    public function sell()
    {
        return $this->hasMany(Sell::class, 'product_branch_id', 'id');
    }

    public function qty($date, $branch_id = null)
    {
        if ($branch_id != null) {
            $added = $this->product_added()->whereDate('created_at', '>=', $date . '-01')->whereDate('created_at', '<=', $date . '-31')->where('branch_id', $branch_id)->sum('qty');
        } else {
            $added = $this->product_added()->whereDate('created_at', '>=', $date . '-01')->whereDate('created_at', '<=', $date . '-31')->sum('qty');
        }
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
