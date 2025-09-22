<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'category_id',
        'unit_id',
        'stock',
        'code',
        'price',
        'min_stock',
        'max_stock',
        'description',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'decimal:2',
        'min_stock' => 'decimal:2',
        'max_stock' => 'decimal:2',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $dates = ['deleted_at'];

    // Relationships
    public function sub_category()
    {
        return $this->belongsTo(SubCategory::class, 'category_id', 'id');
    }

    public function category()
    {
        return $this->hasOneThrough(Category::class, SubCategory::class, 'id', 'id', 'category_id', 'category_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function start_inventories()
    {
        return $this->hasMany(Start_Inventory::class, 'product_id', 'id');
    }

    public function product_branches()
    {
        return $this->hasMany(Product_branch::class, 'product_id', 'id');
    }

    public function increased_products()
    {
        return $this->hasMany(IncreasedProduct::class, 'product_id', 'id');
    }

    public function product_addeds()
    {
        return $this->hasMany(ProductAdded::class, 'product_id', 'id');
    }

    public function product_added()
    {
        return $this->hasMany(ProductAdded::class, 'product_id', 'id');
    }

    // Fixed relationship - sells should be through product_branches
    public function sells()
    {
        return $this->hasManyThrough(Sell::class, Product_branch::class, 'product_id', 'product_branch_id', 'id', 'id');
    }

    public function sell()
    {
        return $this->hasManyThrough(Sell::class, Product_branch::class, 'product_id', 'product_branch_id', 'id', 'id');
    }

    // Scopes
    public function scopeActive(Builder $query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLowStock(Builder $query)
    {
        return $query->whereColumn('stock', '<=', 'min_stock')
                    ->whereNotNull('min_stock')
                    ->where('min_stock', '>', 0);
    }

    public function scopeOutOfStock(Builder $query)
    {
        return $query->where('stock', '<=', 0);
    }

    public function scopeByCategory(Builder $query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeSearch(Builder $query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('code', 'like', "%{$search}%");
        });
    }

    // Accessors & Mutators
    public function getIsLowStockAttribute()
    {
        return $this->min_stock && $this->stock <= $this->min_stock;
    }

    public function getIsOutOfStockAttribute()
    {
        return $this->stock <= 0;
    }

    public function getStockStatusAttribute()
    {
        if ($this->is_out_of_stock) {
            return 'out_of_stock';
        } elseif ($this->is_low_stock) {
            return 'low_stock';
        } elseif ($this->max_stock && $this->stock >= $this->max_stock) {
            return 'overstock';
        }
        return 'normal';
    }

    public function getFormattedPriceAttribute()
    {
        return number_format($this->price, 2) . ' ج';
    }

    public function getTotalValueAttribute()
    {
        return $this->stock * $this->price;
    }

    // Enhanced quantity calculation method
    public function getQuantityForMonth($month = null)
    {
        $month = $month ?? Carbon::now()->format('Y-m');
        $startDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $endDate = Carbon::createFromFormat('Y-m', $month)->endOfMonth();

        // Get starting quantity
        $start = $this->start_inventories()
            ->where('month', $startDate->format('Y-m-d'))
            ->first();
        $startQty = $start ? $start->qty : 0;

        // Get added quantity for the month
        $addedQty = $this->increased_products()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('qty');

        // Get sold quantity for the month (through product_branches)
        $soldQty = $this->sells()
            ->whereBetween('sells.created_at', [$startDate, $endDate])
            ->sum('sells.qty');

        return max(0, $startQty + $addedQty - $soldQty);
    }

    // Get current stock across all branches
    public function getTotalBranchStock()
    {
        return $this->product_branches()->sum('qty');
    }

    // Get total value across all branches
    public function getTotalBranchValue()
    {
        return $this->product_branches()->sum(DB::raw('qty * price'));
    }

    // Check if product needs reorder
    public function needsReorder()
    {
        return $this->min_stock && $this->stock <= $this->min_stock;
    }

    // Get stock movement history
    public function getStockMovements($limit = 10)
    {
        $movements = collect();

        // Add increased products
        $increases = $this->increased_products()
            ->with('branch')
            ->latest()
            ->limit($limit)
            ->get()
            ->map(function ($increase) {
                return [
                    'type' => 'increase',
                    'quantity' => $increase->qty,
                    'branch' => $increase->branch->name ?? 'المخزن الرئيسي',
                    'date' => $increase->created_at,
                    'description' => 'زيادة مخزون'
                ];
            });

        // Add sales
        $sales = $this->sells()
            ->with('product_branch.branch')
            ->latest('sells.created_at')
            ->limit($limit)
            ->get()
            ->map(function ($sell) {
                return [
                    'type' => 'sale',
                    'quantity' => -$sell->qty,
                    'branch' => $sell->product_branch->branch->name ?? 'غير محدد',
                    'date' => $sell->created_at,
                    'description' => 'بيع'
                ];
            });

        return $movements->concat($increases)
            ->concat($sales)
            ->sortByDesc('date')
            ->take($limit);
    }

    // Legacy method for backward compatibility (improved)
    public function qty($date)
    {
        return $this->getQuantityForMonth($date);
    }
}
