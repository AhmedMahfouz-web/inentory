<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\Product_branch;
use App\Models\Start;
use App\Models\Start_Inventory;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GenerateMonthlyStarts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:generate-monthly-starts {--month= : Specific month (Y-m format)} {--type= : Type (main|branch|both)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate monthly start quantities for all products in main inventory and branches';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $month = $this->option('month') ?? Carbon::now()->format('Y-m');
        $type = $this->option('type') ?? 'both';
        
        $this->info("Generating monthly starts for {$month}...");
        
        DB::beginTransaction();
        
        try {
            if ($type === 'main' || $type === 'both') {
                $this->generateMainInventoryStarts($month);
            }
            
            if ($type === 'branch' || $type === 'both') {
                $this->generateBranchInventoryStarts($month);
            }
            
            DB::commit();
            $this->info('Monthly starts generated successfully!');
            
        } catch (\Exception $e) {
            DB::rollback();
            $this->error('Error generating monthly starts: ' . $e->getMessage());
            Log::error('Monthly starts generation failed', [
                'month' => $month,
                'type' => $type,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Generate monthly starts for main inventory
     */
    private function generateMainInventoryStarts($month)
    {
        $this->info('Generating main inventory starts...');
        
        $startDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $previousMonth = $startDate->copy()->subMonth()->format('Y-m');
        
        $products = Product::all();
        $processed = 0;
        
        foreach ($products as $product) {
            // Calculate ending quantity from previous month
            $endingQty = $this->calculateMainInventoryEndingQty($product, $previousMonth);
            
            // Check if start already exists for this month
            $existingStart = Start_Inventory::where('product_id', $product->id)
                ->where('month', $startDate->format('Y-m-d'))
                ->first();
            
            if ($existingStart) {
                $existingStart->update(['qty' => $endingQty]);
                $this->line("Updated main inventory start for product {$product->name}: {$endingQty}");
            } else {
                Start_Inventory::create([
                    'product_id' => $product->id,
                    'month' => $startDate->format('Y-m-d'),
                    'qty' => $endingQty
                ]);
                $this->line("Created main inventory start for product {$product->name}: {$endingQty}");
            }
            
            $processed++;
        }
        
        $this->info("Processed {$processed} products for main inventory");
    }

    /**
     * Generate monthly starts for branch inventory
     */
    private function generateBranchInventoryStarts($month)
    {
        $this->info('Generating branch inventory starts...');
        
        $startDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $previousMonth = $startDate->copy()->subMonth()->format('Y-m');
        
        $productBranches = Product_branch::with(['product', 'branch'])->get();
        $processed = 0;
        
        foreach ($productBranches as $productBranch) {
            // Calculate ending quantity from previous month
            $endingQty = $this->calculateBranchInventoryEndingQty($productBranch, $previousMonth);
            
            // Check if start already exists for this month
            $existingStart = Start::where('product_branch_id', $productBranch->id)
                ->where('month', $startDate->format('Y-m-d'))
                ->first();
            
            if ($existingStart) {
                $existingStart->update(['qty' => $endingQty]);
                $this->line("Updated branch start for {$productBranch->product->name} in {$productBranch->branch->name}: {$endingQty}");
            } else {
                Start::create([
                    'product_branch_id' => $productBranch->id,
                    'month' => $startDate->format('Y-m-d'),
                    'qty' => $endingQty
                ]);
                $this->line("Created branch start for {$productBranch->product->name} in {$productBranch->branch->name}: {$endingQty}");
            }
            
            $processed++;
        }
        
        $this->info("Processed {$processed} product-branch combinations");
    }

    /**
     * Calculate ending quantity for main inventory product
     */
    private function calculateMainInventoryEndingQty($product, $month)
    {
        // Get start quantity for the month
        $startQty = Start_Inventory::where('product_id', $product->id)
            ->where('month', $month . '-01')
            ->value('qty') ?? 0;
        
        // Get added quantities (assuming you have a product_addeds or similar table)
        $addedQty = DB::table('product_addeds')
            ->where('product_id', $product->id)
            ->whereYear('created_at', Carbon::createFromFormat('Y-m', $month)->year)
            ->whereMonth('created_at', Carbon::createFromFormat('Y-m', $month)->month)
            ->sum('qty') ?? 0;
        
        // Get sold quantities (from sells table through product_branches)
        $soldQty = DB::table('sells')
            ->join('product_branches', 'sells.product_branch_id', '=', 'product_branches.id')
            ->where('product_branches.product_id', $product->id)
            ->whereYear('sells.created_at', Carbon::createFromFormat('Y-m', $month)->year)
            ->whereMonth('sells.created_at', Carbon::createFromFormat('Y-m', $month)->month)
            ->sum('sells.qty') ?? 0;
        
        return max(0, $startQty + $addedQty - $soldQty);
    }

    /**
     * Calculate ending quantity for branch inventory product
     */
    private function calculateBranchInventoryEndingQty($productBranch, $month)
    {
        // Get start quantity for the month
        $startQty = Start::where('product_branch_id', $productBranch->id)
            ->where('month', $month . '-01')
            ->value('qty') ?? 0;
        
        // Get added quantities for this specific branch
        $addedQty = DB::table('product_addeds')
            ->where('product_id', $productBranch->product_id)
            ->where('branch_id', $productBranch->branch_id)
            ->whereYear('created_at', Carbon::createFromFormat('Y-m', $month)->year)
            ->whereMonth('created_at', Carbon::createFromFormat('Y-m', $month)->month)
            ->sum('qty') ?? 0;
        
        // Get sold quantities for this specific product-branch
        $soldQty = DB::table('sells')
            ->where('product_branch_id', $productBranch->id)
            ->whereYear('created_at', Carbon::createFromFormat('Y-m', $month)->year)
            ->whereMonth('created_at', Carbon::createFromFormat('Y-m', $month)->month)
            ->sum('qty') ?? 0;
        
        return max(0, $startQty + $addedQty - $soldQty);
    }
}
