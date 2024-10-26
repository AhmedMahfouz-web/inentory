<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code'
    ];

    public function supplier()
    {
        return $this->belongsToMany(supplier::class, 'supplier_categories', 'categories_id', 'supplier_id');
    }

    public function product()
    {
        return $this->hasManyThrough(Product::class, SubCategory::class, 'sub_category_id', 'category_id', 'id');
    }

    public function sub_category()
    {
        return $this->hasMany(SubCategory::class);
    }

    public function soldProducts($date)
    {
        return $this->hasManyThrough(Sell::class, Product::class, 'category_id', 'product_id', 'id', 'id')
            ->whereDate('created_at', '>=', $date . '-01')
            ->whereDate('created_at', '<=', $date . '-31')
            ->sum('qty');
    }

    public function soldProductsByCategory($date)
    {
        return Sell::join('product_branches', 'sells.product_branch_id', '=', 'product_branches.id')
            ->join('products', 'product_branches.product_id', '=', 'products.id')
            ->where('products.category_id', $this->id) // Filter by the current category
            ->whereDate('sells.created_at', '>=', $date . '-01')
            ->whereDate('sells.created_at', '<=', $date . '-31')
            ->sum('sells.qty');
    }

    public function soldProductsSummary($branch_id, $date)
    {
        return Sell::join('product_branches', 'sells.product_branch_id', '=', 'product_branches.id')
            ->join('products', 'product_branches.product_id', '=', 'products.id')
            ->where('products.category_id', $this->id) // Filter by the current category
            ->where('product_branches.branch_id', $branch_id) // Filter by the specific branch
            ->whereDate('sells.created_at', '>=', $date . '-01')
            ->whereDate('sells.created_at', '<=', $date . '-31')
            ->selectRaw('SUM(sells.qty) as total_sold, SUM(sells.qty * product_branches.price) as total_price')
            ->get();
    }
}
