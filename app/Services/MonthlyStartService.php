<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Product_branch;
use App\Models\Start;
use App\Models\Start_Inventory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MonthlyStartService
{
    /**
     * Generate monthly starts for all products and branches
     */
    public function generateMonthlyStarts($month = null, $type = 'both')
    {
        $month = $month ?? Carbon::now()->format('Y-m');
        
        DB::beginTransaction();
        
        try {
            $results = [
                'main_inventory' => 0,
                'branch_inventory' => 0,
                'errors' => []
            ];
            
            if ($type === 'main' || $type === 'both') {
                $results['main_inventory'] = $this->generateMainInventoryStarts($month);
            }
            
            if ($type === 'branch' || $type === 'both') {
                $results['branch_inventory'] = $this->generateBranchInventoryStarts($month);
            }
            
            DB::commit();
            
            Log::info('Monthly starts generated successfully', [
                'month' => $month,
                'type' => $type,
                'results' => $results
            ]);
            
            return $results;
            
        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Monthly starts generation failed', [
                'month' => $month,
                'type' => $type,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Generate monthly starts for main inventory - uses previous month ending quantities
     */
    public function generateMainInventoryStarts($month)
    {
        $startDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $previousMonth = $startDate->copy()->subMonth()->format('Y-m');
        
        $products = Product::all();
        $processed = 0;
        
        foreach ($products as $product) {
            try {
                // Use previous month ending quantity as current month start (standard accounting practice)
                $endingQty = $this->calculateMainInventoryEndingQty($product, $previousMonth);
                
                Start_Inventory::updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'month' => $startDate->format('Y-m-d')
                    ],
                    ['qty' => $endingQty]
                );
                
                $processed++;
                
            } catch (\Exception $e) {
                Log::error('Error processing main inventory start', [
                    'product_id' => $product->id,
                    'month' => $month,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        return $processed;
    }

    /**
     * Generate monthly starts for branch inventory - uses previous month ending quantities
     */
    public function generateBranchInventoryStarts($month)
    {
        $startDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $previousMonth = $startDate->copy()->subMonth()->format('Y-m');
        
        $productBranches = Product_branch::with(['product', 'branch'])->get();
        $processed = 0;
        
        foreach ($productBranches as $productBranch) {
            try {
                // Use previous month ending quantity as current month start (standard accounting practice)
                $endingQty = $this->calculateBranchInventoryEndingQty($productBranch, $previousMonth);
                
                Start::updateOrCreate(
                    [
                        'product_branch_id' => $productBranch->id,
                        'month' => $startDate->format('Y-m-d')
                    ],
                    ['qty' => $endingQty]
                );
                
                $processed++;
                
            } catch (\Exception $e) {
                Log::error('Error processing branch inventory start', [
                    'product_branch_id' => $productBranch->id,
                    'month' => $month,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        return $processed;
    }

    /**
     * Calculate ending quantity for main inventory product - uses Product::qty() method directly
     */
    private function calculateMainInventoryEndingQty($product, $month)
    {
        // Use the Product model's qty() method directly - it already has the correct logic
        return $product->qty($month);
    }

    /**
     * Calculate ending quantity for branch inventory product - uses Product_branch::qty() method directly
     */
    private function calculateBranchInventoryEndingQty($productBranch, $month)
    {
        // Use the Product_branch model's qty() method directly - it already has the correct logic
        return $productBranch->qty($month, $productBranch->branch_id);
    }

    /**
     * Get monthly start report for a specific month
     */
    public function getMonthlyStartReport($month = null)
    {
        $month = $month ?? Carbon::now()->format('Y-m');
        $startDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        
        $mainInventoryStarts = Start_Inventory::with(['product.sub_category.category', 'product.unit'])
            ->where('month', $startDate->format('Y-m-d'))
            ->get();
        
        $branchInventoryStarts = Start::with(['product_branch.product.sub_category.category', 'product_branch.product.unit', 'product_branch.branch'])
            ->where('month', $startDate->format('Y-m-d'))
            ->get();
        
        return [
            'month' => $month,
            'main_inventory' => $mainInventoryStarts,
            'branch_inventory' => $branchInventoryStarts,
            'summary' => [
                'main_products_count' => $mainInventoryStarts->count(),
                'branch_products_count' => $branchInventoryStarts->count(),
                'total_main_qty' => $mainInventoryStarts->sum('qty'),
                'total_branch_qty' => $branchInventoryStarts->sum('qty')
            ],
            'category_analysis' => $this->getCategoryAnalysis($month),
            'branch_analysis' => $this->getBranchAnalysis($month)
        ];
    }

    /**
     * Get category-based analysis for monthly starts
     */
    public function getCategoryAnalysis($month = null)
    {
        $month = $month ?? Carbon::now()->format('Y-m');
        $startDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        
        // Main inventory category analysis
        $mainCategoryAnalysis = DB::table('start__inventories as si')
            ->join('products as p', 'si.product_id', '=', 'p.id')
            ->join('sub_categories as sc', 'p.category_id', '=', 'sc.id')
            ->join('categories as c', 'sc.category_id', '=', 'c.id')
            ->where('si.month', $startDate->format('Y-m-d'))
            ->select(
                'c.id as category_id',
                'c.name as category_name',
                DB::raw('COUNT(p.id) as products_count'),
                DB::raw('SUM(si.qty) as total_qty'),
                DB::raw('SUM(si.qty * p.price) as total_value')
            )
            ->groupBy('c.id', 'c.name')
            ->get();

        // Branch inventory category analysis
        $branchCategoryAnalysis = DB::table('starts as s')
            ->join('product_branches as pb', 's.product_branch_id', '=', 'pb.id')
            ->join('products as p', 'pb.product_id', '=', 'p.id')
            ->join('sub_categories as sc', 'p.category_id', '=', 'sc.id')
            ->join('categories as c', 'sc.category_id', '=', 'c.id')
            ->join('branches as b', 'pb.branch_id', '=', 'b.id')
            ->where('s.month', $startDate->format('Y-m-d'))
            ->select(
                'c.id as category_id',
                'c.name as category_name',
                'b.id as branch_id',
                'b.name as branch_name',
                DB::raw('COUNT(p.id) as products_count'),
                DB::raw('SUM(s.qty) as total_qty'),
                DB::raw('SUM(s.qty * pb.price) as total_value')
            )
            ->groupBy('c.id', 'c.name', 'b.id', 'b.name')
            ->get();

        return [
            'main_inventory' => $mainCategoryAnalysis,
            'branch_inventory' => $branchCategoryAnalysis->groupBy('category_name'),
            'category_totals' => $this->calculateCategoryTotals($mainCategoryAnalysis, $branchCategoryAnalysis)
        ];
    }

    /**
     * Get branch-based analysis for monthly starts
     */
    public function getBranchAnalysis($month = null)
    {
        $month = $month ?? Carbon::now()->format('Y-m');
        $startDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        
        $branchAnalysis = DB::table('starts as s')
            ->join('product_branches as pb', 's.product_branch_id', '=', 'pb.id')
            ->join('products as p', 'pb.product_id', '=', 'p.id')
            ->join('branches as b', 'pb.branch_id', '=', 'b.id')
            ->where('s.month', $startDate->format('Y-m-d'))
            ->select(
                'b.id as branch_id',
                'b.name as branch_name',
                DB::raw('COUNT(DISTINCT p.id) as products_count'),
                DB::raw('SUM(s.qty) as total_qty'),
                DB::raw('SUM(s.qty * pb.price) as total_value'),
                DB::raw('AVG(pb.price) as avg_price')
            )
            ->groupBy('b.id', 'b.name')
            ->get();

        return $branchAnalysis;
    }

    /**
     * Calculate category totals across main and branch inventories
     */
    private function calculateCategoryTotals($mainCategories, $branchCategories)
    {
        $totals = [];
        
        // Process main inventory categories
        foreach ($mainCategories as $category) {
            $totals[$category->category_name] = [
                'category_id' => $category->category_id,
                'category_name' => $category->category_name,
                'main_products_count' => $category->products_count,
                'main_total_qty' => $category->total_qty,
                'main_total_value' => $category->total_value,
                'branch_products_count' => 0,
                'branch_total_qty' => 0,
                'branch_total_value' => 0
            ];
        }
        
        // Process branch inventory categories
        foreach ($branchCategories as $category) {
            $categoryName = $category->category_name;
            
            if (!isset($totals[$categoryName])) {
                $totals[$categoryName] = [
                    'category_id' => $category->category_id,
                    'category_name' => $categoryName,
                    'main_products_count' => 0,
                    'main_total_qty' => 0,
                    'main_total_value' => 0,
                    'branch_products_count' => 0,
                    'branch_total_qty' => 0,
                    'branch_total_value' => 0
                ];
            }
            
            $totals[$categoryName]['branch_products_count'] += $category->products_count;
            $totals[$categoryName]['branch_total_qty'] += $category->total_qty;
            $totals[$categoryName]['branch_total_value'] += $category->total_value;
        }
        
        // Calculate grand totals
        foreach ($totals as &$total) {
            $total['total_products_count'] = $total['main_products_count'] + $total['branch_products_count'];
            $total['total_qty'] = $total['main_total_qty'] + $total['branch_total_qty'];
            $total['total_value'] = $total['main_total_value'] + $total['branch_total_value'];
        }
        
        return collect($totals)->values();
    }

    /**
     * Auto-generate starts for current month based on previous month ending
     */
    public function autoGenerateCurrentMonth()
    {
        $currentMonth = Carbon::now()->format('Y-m');
        return $this->generateMonthlyStarts($currentMonth);
    }

    /**
     * Check if monthly starts exist for a given month
     */
    public function monthlyStartsExist($month)
    {
        $startDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        
        $mainExists = Start_Inventory::where('month', $startDate->format('Y-m-d'))->exists();
        $branchExists = Start::where('month', $startDate->format('Y-m-d'))->exists();
        
        return [
            'main_inventory' => $mainExists,
            'branch_inventory' => $branchExists,
            'any_exists' => $mainExists || $branchExists
        ];
    }
}
