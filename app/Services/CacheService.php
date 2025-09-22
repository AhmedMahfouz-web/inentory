<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class CacheService
{
    protected $defaultTtl = 3600; // 1 hour
    protected $keyPrefix = 'inventory_';

    /**
     * Get cached data or execute callback and cache result
     */
    public function remember($key, $callback, $ttl = null)
    {
        $ttl = $ttl ?? $this->defaultTtl;
        $fullKey = $this->getFullKey($key);

        try {
            return Cache::remember($fullKey, $ttl, $callback);
        } catch (\Exception $e) {
            // If cache fails, execute callback directly
            return $callback();
        }
    }

    /**
     * Cache products with their relationships
     */
    public function cacheProducts($includeInactive = false)
    {
        $key = $includeInactive ? 'products_all' : 'products_active';
        
        return $this->remember($key, function () use ($includeInactive) {
            $query = \App\Models\Product::with(['sub_category.category', 'unit']);
            
            if (!$includeInactive) {
                $query->active();
            }
            
            return $query->get();
        }, 1800); // 30 minutes
    }

    /**
     * Cache branches with their products
     */
    public function cacheBranches()
    {
        return $this->remember('branches_with_products', function () {
            return \App\Models\Branch::with(['product_branches.product'])->get();
        }, 1800);
    }

    /**
     * Cache categories with subcategories
     */
    public function cacheCategories()
    {
        return $this->remember('categories_with_subs', function () {
            return \App\Models\Category::with('sub_category')->get();
        }, 3600);
    }

    /**
     * Cache low stock products
     */
    public function cacheLowStockProducts()
    {
        return $this->remember('low_stock_products', function () {
            return \App\Models\Product::lowStock()
                ->with(['sub_category.category', 'unit'])
                ->get();
        }, 900); // 15 minutes
    }

    /**
     * Cache monthly statistics
     */
    public function cacheMonthlyStats($month = null)
    {
        $month = $month ?? Carbon::now()->format('Y-m');
        $key = "monthly_stats_{$month}";
        
        return $this->remember($key, function () use ($month) {
            $monthlyStartService = app(MonthlyStartService::class);
            return $monthlyStartService->getMonthlyStartReport($month);
        }, 7200); // 2 hours
    }

    /**
     * Cache inventory valuation
     */
    public function cacheInventoryValuation()
    {
        return $this->remember('inventory_valuation', function () {
            $inventoryService = app(InventoryService::class);
            return $inventoryService->getInventoryValuation();
        }, 1800); // 30 minutes
    }

    /**
     * Cache dashboard statistics
     */
    public function cacheDashboardStats()
    {
        return $this->remember('dashboard_stats', function () {
            $currentMonth = date('Y-m');
            $currentMonthStart = $currentMonth . '-01';
            $currentMonthEnd = $currentMonth . '-31';
            
            return [
                'total_products' => \App\Models\Product::count(),
                'total_branches' => \App\Models\Branch::count(),
                'total_categories' => \DB::table('categories')->count(),
                'monthly_transactions' => \App\Models\Sell::whereBetween('created_at', [$currentMonthStart, $currentMonthEnd])->count(),
                'monthly_additions' => \App\Models\ProductAdded::whereBetween('created_at', [$currentMonthStart, $currentMonthEnd])->count(),
                'total_inventory_value' => $this->calculateTotalInventoryValue(),
                'low_stock_count' => \App\Models\Product::lowStock()->count(),
                'out_of_stock_count' => \App\Models\Product::outOfStock()->count()
            ];
        }, 1800); // 30 minutes
    }

    /**
     * Cache branch performance data
     */
    public function cacheBranchPerformance($month = null)
    {
        $month = $month ?? date('Y-m');
        $key = "branch_performance_{$month}";
        
        return $this->remember($key, function () use ($month) {
            $currentMonthStart = $month . '-01';
            $currentMonthEnd = $month . '-31';
            
            return \App\Models\Branch::withCount(['product_branches'])
                ->with(['product_branches' => function ($query) use ($currentMonthStart, $currentMonthEnd) {
                    $query->withSum(['sell' => function ($q) use ($currentMonthStart, $currentMonthEnd) {
                        $q->whereRaw('sells.created_at BETWEEN ? AND ?', [$currentMonthStart, $currentMonthEnd]);
                    }], 'qty');
                }])
                ->get()
                ->map(function ($branch) {
                    $totalSales = $branch->product_branches->sum('sell_sum_qty') ?? 0;
                    $totalValue = $branch->product_branches->sum(function ($pb) {
                        return ($pb->sell_sum_qty ?? 0) * $pb->price;
                    });
                    
                    return [
                        'id' => $branch->id,
                        'name' => $branch->name,
                        'products_count' => $branch->product_branches_count,
                        'monthly_sales_qty' => $totalSales,
                        'monthly_sales_value' => $totalValue,
                    ];
                });
        }, 3600); // 1 hour
    }

    /**
     * Cache recent activity
     */
    public function cacheRecentActivity($limit = 20)
    {
        return $this->remember("recent_activity_{$limit}", function () use ($limit) {
            $recentSells = \App\Models\Sell::with(['product_branch.product', 'product_branch.branch'])
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get()
                ->map(function ($sell) {
                    return [
                        'type' => 'sell',
                        'description' => 'بيع ' . ($sell->product_branch->product->name ?? 'منتج') . ' من ' . ($sell->product_branch->branch->name ?? 'فرع'),
                        'quantity' => $sell->qty,
                        'created_at' => $sell->created_at,
                        'icon' => 'ti-shopping-cart',
                        'color' => 'danger'
                    ];
                });

            $recentAdditions = \App\Models\ProductAdded::with(['product', 'branch'])
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get()
                ->map(function ($addition) {
                    return [
                        'type' => 'addition',
                        'description' => 'تحويل ' . ($addition->product->name ?? 'منتج') . ' إلى ' . ($addition->branch->name ?? 'فرع'),
                        'quantity' => $addition->qty,
                        'created_at' => $addition->created_at,
                        'icon' => 'ti-transfer-out',
                        'color' => 'info'
                    ];
                });

            return $recentSells->concat($recentAdditions)
                ->sortByDesc('created_at')
                ->take($limit);
        }, 600); // 10 minutes
    }

    /**
     * Invalidate specific cache keys
     */
    public function invalidate($keys)
    {
        if (!is_array($keys)) {
            $keys = [$keys];
        }

        foreach ($keys as $key) {
            $fullKey = $this->getFullKey($key);
            Cache::forget($fullKey);
        }
    }

    /**
     * Invalidate product-related caches
     */
    public function invalidateProductCaches()
    {
        $this->invalidate([
            'products_all',
            'products_active',
            'low_stock_products',
            'dashboard_stats',
            'inventory_valuation'
        ]);
    }

    /**
     * Invalidate branch-related caches
     */
    public function invalidateBranchCaches()
    {
        $this->invalidate([
            'branches_with_products',
            'branch_performance_' . date('Y-m'),
            'dashboard_stats'
        ]);
    }

    /**
     * Invalidate monthly caches
     */
    public function invalidateMonthlyCaches($month = null)
    {
        $month = $month ?? date('Y-m');
        $this->invalidate([
            "monthly_stats_{$month}",
            "branch_performance_{$month}"
        ]);
    }

    /**
     * Clear all inventory caches
     */
    public function clearAll()
    {
        $keys = [
            'products_all',
            'products_active',
            'branches_with_products',
            'categories_with_subs',
            'low_stock_products',
            'dashboard_stats',
            'inventory_valuation',
            'recent_activity_20'
        ];

        // Add monthly caches for current and previous months
        $currentMonth = date('Y-m');
        $previousMonth = Carbon::now()->subMonth()->format('Y-m');
        
        $keys[] = "monthly_stats_{$currentMonth}";
        $keys[] = "monthly_stats_{$previousMonth}";
        $keys[] = "branch_performance_{$currentMonth}";
        $keys[] = "branch_performance_{$previousMonth}";

        $this->invalidate($keys);
    }

    /**
     * Get full cache key with prefix
     */
    protected function getFullKey($key)
    {
        return $this->keyPrefix . $key;
    }

    /**
     * Calculate total inventory value
     */
    protected function calculateTotalInventoryValue()
    {
        $mainInventoryValue = \App\Models\Product::sum(\DB::raw('stock * price'));
        $branchInventoryValue = \App\Models\Product_branch::sum(\DB::raw('qty * price'));
        
        return $mainInventoryValue + $branchInventoryValue;
    }

    /**
     * Warm up essential caches
     */
    public function warmUp()
    {
        // Cache essential data that's frequently accessed
        $this->cacheProducts();
        $this->cacheBranches();
        $this->cacheCategories();
        $this->cacheLowStockProducts();
        $this->cacheDashboardStats();
        $this->cacheInventoryValuation();
        $this->cacheBranchPerformance();
        $this->cacheRecentActivity();
    }

    /**
     * Get cache statistics
     */
    public function getStats()
    {
        $keys = [
            'products_active',
            'branches_with_products',
            'categories_with_subs',
            'low_stock_products',
            'dashboard_stats',
            'inventory_valuation'
        ];

        $stats = [
            'cached_items' => 0,
            'cache_hits' => 0,
            'cache_misses' => 0
        ];

        foreach ($keys as $key) {
            $fullKey = $this->getFullKey($key);
            if (Cache::has($fullKey)) {
                $stats['cached_items']++;
            }
        }

        return $stats;
    }
}
