<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Product;
use App\Models\Product_branch;
use App\Models\Sell;
use App\Models\ProductAdded;
use App\Models\IncreasedProduct;
use App\Models\Start;
use App\Models\Start_Inventory;
use App\Services\MonthlyStartService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{

    public function index()
    {
        $currentMonth = date('Y-m');
        $currentMonthStart = $currentMonth . '-01';
        $currentMonthEnd = $currentMonth . '-31';
        
        // Existing data
        $branches = Branch::select('id', 'name')->get();
        $products = Product::withSum(
            [
                'product_added' => function ($q) use ($currentMonthStart, $currentMonthEnd) {
                    $q->whereBetween('product_addeds.created_at', [$currentMonthStart, $currentMonthEnd]);
                }
            ],
            'qty'
        )->withSum(
            [
                'sell' => function ($q) use ($currentMonthStart, $currentMonthEnd) {
                    $q->whereRaw('sells.created_at BETWEEN ? AND ?', [$currentMonthStart, $currentMonthEnd]);
                }
            ],
            'qty'
        )->get();
        
        $branches = Branch::with(['product_branches' => function ($q) use ($currentMonthStart, $currentMonthEnd) {
            $q->with('product')->withSum(['product_added' => function ($q) use ($currentMonthStart, $currentMonthEnd) {
                $q->whereBetween('product_addeds.created_at', [$currentMonthStart, $currentMonthEnd]);
            }], 'qty');
        }])->get();
        
        $total_income = 0;
        $total_sells = 0;
        foreach ($products as $product) {
            $total_income += $product->price * ($product->product_added_sum_qty ?? 0);
            $total_sells += $product->price * ($product->sell_sum_qty ?? 0);
        }

        // Enhanced dashboard data
        $dashboardStats = $this->getDashboardStats($currentMonth);
        $lowStockProducts = $this->getLowStockProducts();
        $recentActivity = $this->getRecentActivity();
        $branchPerformance = $this->getBranchPerformance($currentMonth);
        $monthlyStartsStatus = $this->getMonthlyStartsStatus($currentMonth);

        return view('welcome', compact(
            'branches', 
            'total_income', 
            'total_sells',
            'dashboardStats',
            'lowStockProducts',
            'recentActivity',
            'branchPerformance',
            'monthlyStartsStatus'
        ));
    }

    private function getDashboardStats($currentMonth)
    {
        $currentMonthStart = $currentMonth . '-01';
        $currentMonthEnd = $currentMonth . '-31';
        
        return [
            'total_products' => Product::count(),
            'total_branches' => Branch::count(),
            'total_categories' => DB::table('categories')->count(),
            'monthly_transactions' => Sell::whereBetween('created_at', [$currentMonthStart, $currentMonthEnd])->count(),
            'monthly_additions' => ProductAdded::whereBetween('created_at', [$currentMonthStart, $currentMonthEnd])->count(),
            'monthly_increases' => IncreasedProduct::whereBetween('created_at', [$currentMonthStart, $currentMonthEnd])->count(),
            'total_inventory_value' => $this->calculateTotalInventoryValue(),
        ];
    }

    private function getLowStockProducts($limit = 10)
    {
        return Product::where('stock', '>', 0)
            ->whereColumn('stock', '<=', 'min_stock')
            ->whereNotNull('min_stock')
            ->with(['unit', 'sub_category'])
            ->orderBy('stock', 'asc')
            ->limit($limit)
            ->get();
    }

    private function getRecentActivity($limit = 10)
    {
        $recentSells = Sell::with(['product_branch.product', 'product_branch.branch'])
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

        $recentAdditions = ProductAdded::with(['product', 'branch'])
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
    }

    private function getBranchPerformance($currentMonth)
    {
        $currentMonthStart = $currentMonth . '-01';
        $currentMonthEnd = $currentMonth . '-31';
        
        return Branch::withCount(['product_branches'])
            ->with(['product_branches' => function ($query) use ($currentMonthStart, $currentMonthEnd) {
                $query->withSum(['sell' => function ($q) use ($currentMonthStart, $currentMonthEnd) {
                    $q->whereBetween('created_at', [$currentMonthStart, $currentMonthEnd]);
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
    }

    private function getMonthlyStartsStatus($currentMonth)
    {
        $monthlyStartService = app(MonthlyStartService::class);
        $exists = $monthlyStartService->monthlyStartsExist($currentMonth);
        
        $mainStartsCount = Start_Inventory::where('month', $currentMonth . '-01')->count();
        $branchStartsCount = Start::where('month', $currentMonth . '-01')->count();
        
        return [
            'exists' => $exists,
            'main_starts_count' => $mainStartsCount,
            'branch_starts_count' => $branchStartsCount,
            'current_month' => $currentMonth
        ];
    }

    private function calculateTotalInventoryValue()
    {
        $mainInventoryValue = Product::sum(DB::raw('stock * price'));
        $branchInventoryValue = Product_branch::sum(DB::raw('qty * price'));
        
        return $mainInventoryValue + $branchInventoryValue;
    }
}
