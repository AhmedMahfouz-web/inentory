<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Product_branch;
use App\Models\Branch;
use App\Models\Sell;
use App\Models\IncreasedProduct;
use App\Models\ProductAdded;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InventoryService
{
    /**
     * Get comprehensive inventory report
     */
    public function getInventoryReport($branchId = null, $categoryId = null, $includeInactive = false)
    {
        try {
            $query = Product::with(['sub_category.category', 'unit']);
            
            if (!$includeInactive) {
                $query->active();
            }
            
            if ($categoryId) {
                $query->byCategory($categoryId);
            }
            
            $products = $query->get();
            
            if ($branchId) {
                return $this->getBranchInventoryReport($branchId, $products);
            }
            
            return $this->getMainInventoryReport($products);
            
        } catch (\Exception $e) {
            Log::error('Error generating inventory report', [
                'branch_id' => $branchId,
                'category_id' => $categoryId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get main inventory report
     */
    private function getMainInventoryReport($products)
    {
        $report = [
            'products' => $products,
            'summary' => [
                'total_products' => $products->count(),
                'active_products' => $products->where('is_active', true)->count(),
                'low_stock_products' => $products->where('is_low_stock', true)->count(),
                'out_of_stock_products' => $products->where('is_out_of_stock', true)->count(),
                'total_value' => $products->sum('total_value'),
                'total_quantity' => $products->sum('stock')
            ],
            'categories' => $this->getCategoryBreakdown($products),
            'stock_alerts' => $this->getStockAlerts($products)
        ];

        return $report;
    }

    /**
     * Get branch inventory report
     */
    private function getBranchInventoryReport($branchId, $products)
    {
        $branch = Branch::findOrFail($branchId);
        $branchProducts = Product_branch::where('branch_id', $branchId)
            ->with(['product.sub_category.category', 'product.unit'])
            ->get();

        $report = [
            'branch' => $branch,
            'products' => $branchProducts,
            'summary' => [
                'total_products' => $branchProducts->count(),
                'total_value' => $branchProducts->sum(function ($pb) {
                    return $pb->qty * $pb->price;
                }),
                'total_quantity' => $branchProducts->sum('qty'),
                'low_stock_products' => $branchProducts->filter(function ($pb) {
                    return $pb->product->min_stock && $pb->qty <= $pb->product->min_stock;
                })->count()
            ],
            'categories' => $this->getBranchCategoryBreakdown($branchProducts)
        ];

        return $report;
    }

    /**
     * Get category breakdown for main inventory
     */
    private function getCategoryBreakdown($products)
    {
        return $products->groupBy('sub_category.category.name')
            ->map(function ($categoryProducts, $categoryName) {
                return [
                    'name' => $categoryName,
                    'products_count' => $categoryProducts->count(),
                    'total_quantity' => $categoryProducts->sum('stock'),
                    'total_value' => $categoryProducts->sum('total_value'),
                    'low_stock_count' => $categoryProducts->where('is_low_stock', true)->count()
                ];
            })->values();
    }

    /**
     * Get category breakdown for branch inventory
     */
    private function getBranchCategoryBreakdown($branchProducts)
    {
        return $branchProducts->groupBy('product.sub_category.category.name')
            ->map(function ($categoryProducts, $categoryName) {
                return [
                    'name' => $categoryName,
                    'products_count' => $categoryProducts->count(),
                    'total_quantity' => $categoryProducts->sum('qty'),
                    'total_value' => $categoryProducts->sum(function ($pb) {
                        return $pb->qty * $pb->price;
                    })
                ];
            })->values();
    }

    /**
     * Get stock alerts
     */
    private function getStockAlerts($products)
    {
        return [
            'low_stock' => $products->where('is_low_stock', true)->values(),
            'out_of_stock' => $products->where('is_out_of_stock', true)->values(),
            'overstock' => $products->filter(function ($product) {
                return $product->max_stock && $product->stock >= $product->max_stock;
            })->values()
        ];
    }

    /**
     * Transfer product between branches
     */
    public function transferProduct($productId, $fromBranchId, $toBranchId, $quantity, $userId = null)
    {
        DB::beginTransaction();
        
        try {
            $product = Product::findOrFail($productId);
            
            // Handle main inventory to branch transfer
            if ($fromBranchId === null) {
                return $this->transferFromMainToBranch($product, $toBranchId, $quantity, $userId);
            }
            
            // Handle branch to branch transfer
            if ($toBranchId === null) {
                return $this->transferFromBranchToMain($product, $fromBranchId, $quantity, $userId);
            }
            
            return $this->transferBetweenBranches($product, $fromBranchId, $toBranchId, $quantity, $userId);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error transferring product', [
                'product_id' => $productId,
                'from_branch' => $fromBranchId,
                'to_branch' => $toBranchId,
                'quantity' => $quantity,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Transfer from main inventory to branch
     */
    private function transferFromMainToBranch($product, $toBranchId, $quantity, $userId)
    {
        if ($product->stock < $quantity) {
            throw new \Exception('الكمية المطلوبة غير متوفرة في المخزن الرئيسي');
        }

        // Reduce main inventory
        $product->decrement('stock', $quantity);

        // Add to branch or create new branch product
        $branchProduct = Product_branch::firstOrCreate(
            ['product_id' => $product->id, 'branch_id' => $toBranchId],
            ['qty' => 0, 'price' => $product->price]
        );
        
        $branchProduct->increment('qty', $quantity);

        // Log the transfer
        ProductAdded::create([
            'product_id' => $product->id,
            'branch_id' => $toBranchId,
            'qty' => $quantity,
            'user_id' => $userId,
            'notes' => 'تحويل من المخزن الرئيسي'
        ]);

        DB::commit();
        
        return [
            'success' => true,
            'message' => 'تم تحويل المنتج بنجاح من المخزن الرئيسي إلى الفرع'
        ];
    }

    /**
     * Transfer from branch to main inventory
     */
    private function transferFromBranchToMain($product, $fromBranchId, $quantity, $userId)
    {
        $branchProduct = Product_branch::where('product_id', $product->id)
            ->where('branch_id', $fromBranchId)
            ->first();

        if (!$branchProduct || $branchProduct->qty < $quantity) {
            throw new \Exception('الكمية المطلوبة غير متوفرة في الفرع');
        }

        // Reduce branch inventory
        $branchProduct->decrement('qty', $quantity);

        // Add to main inventory
        $product->increment('stock', $quantity);

        // Log the transfer
        IncreasedProduct::create([
            'product_id' => $product->id,
            'qty' => $quantity,
            'user_id' => $userId,
            'notes' => 'تحويل من الفرع إلى المخزن الرئيسي'
        ]);

        DB::commit();
        
        return [
            'success' => true,
            'message' => 'تم تحويل المنتج بنجاح من الفرع إلى المخزن الرئيسي'
        ];
    }

    /**
     * Transfer between branches
     */
    private function transferBetweenBranches($product, $fromBranchId, $toBranchId, $quantity, $userId)
    {
        $fromBranchProduct = Product_branch::where('product_id', $product->id)
            ->where('branch_id', $fromBranchId)
            ->first();

        if (!$fromBranchProduct || $fromBranchProduct->qty < $quantity) {
            throw new \Exception('الكمية المطلوبة غير متوفرة في الفرع المصدر');
        }

        // Reduce from source branch
        $fromBranchProduct->decrement('qty', $quantity);

        // Add to destination branch
        $toBranchProduct = Product_branch::firstOrCreate(
            ['product_id' => $product->id, 'branch_id' => $toBranchId],
            ['qty' => 0, 'price' => $fromBranchProduct->price]
        );
        
        $toBranchProduct->increment('qty', $quantity);

        // Log the transfer
        ProductAdded::create([
            'product_id' => $product->id,
            'branch_id' => $toBranchId,
            'qty' => $quantity,
            'user_id' => $userId,
            'notes' => 'تحويل بين الفروع'
        ]);

        DB::commit();
        
        return [
            'success' => true,
            'message' => 'تم تحويل المنتج بنجاح بين الفروع'
        ];
    }

    /**
     * Process sale transaction
     */
    public function processSale($productBranchId, $quantity, $userId = null)
    {
        DB::beginTransaction();
        
        try {
            $productBranch = Product_branch::with('product')->findOrFail($productBranchId);
            
            if ($productBranch->qty < $quantity) {
                throw new \Exception('الكمية المطلوبة غير متوفرة');
            }

            // Reduce branch inventory
            $productBranch->decrement('qty', $quantity);

            // Record sale
            $sale = Sell::create([
                'product_branch_id' => $productBranchId,
                'qty' => $quantity,
                'user_id' => $userId
            ]);

            DB::commit();
            
            return [
                'success' => true,
                'sale' => $sale,
                'message' => 'تم تسجيل البيع بنجاح'
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing sale', [
                'product_branch_id' => $productBranchId,
                'quantity' => $quantity,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get inventory valuation
     */
    public function getInventoryValuation($date = null)
    {
        $date = $date ?? Carbon::now();
        
        try {
            $mainInventoryValue = Product::active()
                ->sum(DB::raw('stock * price'));

            $branchInventoryValue = Product_branch::join('products', 'product_branches.product_id', '=', 'products.id')
                ->where('products.is_active', true)
                ->sum(DB::raw('product_branches.qty * product_branches.price'));

            $totalValue = $mainInventoryValue + $branchInventoryValue;

            return [
                'date' => $date->format('Y-m-d'),
                'main_inventory_value' => $mainInventoryValue,
                'branch_inventory_value' => $branchInventoryValue,
                'total_inventory_value' => $totalValue,
                'breakdown' => $this->getValuationBreakdown()
            ];
            
        } catch (\Exception $e) {
            Log::error('Error calculating inventory valuation', [
                'date' => $date,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get valuation breakdown by category
     */
    private function getValuationBreakdown()
    {
        $mainBreakdown = DB::table('products')
            ->join('sub_categories', 'products.category_id', '=', 'sub_categories.id')
            ->join('categories', 'sub_categories.category_id', '=', 'categories.id')
            ->where('products.is_active', true)
            ->whereNull('products.deleted_at')
            ->select(
                'categories.name as category_name',
                DB::raw('SUM(products.stock * products.price) as value'),
                DB::raw('SUM(products.stock) as quantity')
            )
            ->groupBy('categories.id', 'categories.name')
            ->get();

        $branchBreakdown = DB::table('product_branches')
            ->join('products', 'product_branches.product_id', '=', 'products.id')
            ->join('sub_categories', 'products.category_id', '=', 'sub_categories.id')
            ->join('categories', 'sub_categories.category_id', '=', 'categories.id')
            ->where('products.is_active', true)
            ->whereNull('products.deleted_at')
            ->select(
                'categories.name as category_name',
                DB::raw('SUM(product_branches.qty * product_branches.price) as value'),
                DB::raw('SUM(product_branches.qty) as quantity')
            )
            ->groupBy('categories.id', 'categories.name')
            ->get();

        return [
            'main_inventory' => $mainBreakdown,
            'branch_inventory' => $branchBreakdown
        ];
    }

    /**
     * Get low stock alerts with recommendations
     */
    public function getLowStockAlerts($includeRecommendations = true)
    {
        $lowStockProducts = Product::lowStock()
            ->with(['sub_category.category', 'unit', 'product_branches'])
            ->get();

        $alerts = $lowStockProducts->map(function ($product) use ($includeRecommendations) {
            $alert = [
                'product' => $product,
                'current_stock' => $product->stock,
                'min_stock' => $product->min_stock,
                'total_branch_stock' => $product->getTotalBranchStock(),
                'status' => $product->stock_status
            ];

            if ($includeRecommendations) {
                $alert['recommendation'] = $this->getRestockRecommendation($product);
            }

            return $alert;
        });

        return $alerts;
    }

    /**
     * Get restock recommendation for a product
     */
    private function getRestockRecommendation($product)
    {
        // Calculate average monthly consumption
        $monthlySales = $product->sells()
            ->where('sells.created_at', '>=', Carbon::now()->subMonths(3))
            ->sum('sells.qty');

        $averageMonthlyConsumption = $monthlySales / 3;
        
        $recommendedOrder = max(
            $product->max_stock - $product->stock,
            $averageMonthlyConsumption * 2 // 2 months supply
        );

        return [
            'average_monthly_consumption' => round($averageMonthlyConsumption, 2),
            'recommended_order_quantity' => round($recommendedOrder, 2),
            'estimated_cost' => round($recommendedOrder * $product->price, 2),
            'urgency' => $product->stock <= 0 ? 'critical' : ($product->stock <= $product->min_stock * 0.5 ? 'high' : 'medium')
        ];
    }
}
