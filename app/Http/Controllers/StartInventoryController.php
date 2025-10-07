<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Start_Inventory;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StartInventoryController extends Controller
{
    public function index()
    {
        $start = date('Y-m') . '-01';
        $products = Product::with([
            'start' => function ($q) use ($start) {
                $q->where('month', $start);
            },
        ])->get();
        return view('pages.start_inventory.create', compact('products'));
    }
    public function store(Request $request)
    {
        DB::beginTransaction();
        
        try {
            $currentMonth = date('Y-m') . '-01';
            $updatedCount = 0;
            
            foreach ($request->start as $index => $start) {
                // Ensure start quantity is not null
                if ($start == null) {
                    $start = 0;
                }
                
                // Always update or create the start record - never skip
                Start_Inventory::updateOrCreate(
                    [
                        'product_id' => $request->product_id[$index],
                        'month' => $currentMonth,
                    ],
                    [
                        'qty' => $start,
                    ]
                );
                
                $updatedCount++;
            }
            
            DB::commit();
            
            return redirect()->route('product inventory')->with('success', "تم تعديل بداية المدة بنجاح. تم تحديث {$updatedCount} منتج.");
            
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('product inventory')->with('error', 'حدث خطأ أثناء تعديل بداية المدة: ' . $e->getMessage());
        }
    }
    public function qty_store()
    {
        DB::beginTransaction();
        
        try {
            $currentMonth = date('Y-m') . '-01';
            $products = Product::all();
            $processed = 0;
            $created = 0;
            
            foreach ($products as $product) {
                // First, ensure start record exists - create it automatically if it doesn't
                $product_start = Start_Inventory::updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'month' => $currentMonth,
                    ],
                    [
                        'qty' => $product->stock ?? 0, // Use current stock as default if no start exists
                    ]
                );
                
                // Check if this was a newly created record
                if ($product_start->wasRecentlyCreated) {
                    $created++;
                }
                
                // Update product stock with the start quantity
                $product->update([
                    'stock' => $product_start->qty,
                ]);
                
                $processed++;
            }
            
            DB::commit();
            
            $message = "تم تعديل بداية المدة بنجاح. تم معالجة {$processed} منتج.";
            if ($created > 0) {
                $message .= " تم إنشاء {$created} بداية جديدة تلقائياً.";
            }
            
            return redirect()->route('product inventory')->with('success', $message);
            
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('product inventory')->with('error', 'حدث خطأ أثناء تعديل بداية المدة: ' . $e->getMessage());
        }
    }

    /**
     * Auto-generate main inventory starts - always updates existing records
     */
    public function auto_generate()
    {
        DB::beginTransaction();
        
        try {
            $currentMonth = date('Y-m') . '-01';
            $previousMonth = Carbon::createFromFormat('Y-m-d', $currentMonth)->subMonth()->format('Y-m');
            
            $products = Product::all();
            $processed = 0;
            $created = 0;
            $updated = 0;
            
            foreach ($products as $product) {
                // Calculate ending quantity from previous month
                $endingQty = $this->calculateMainInventoryEndingQty($product, $previousMonth);
                
                // Always update or create the start record - never skip
                $startRecord = Start_Inventory::updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'month' => $currentMonth,
                    ],
                    [
                        'qty' => $endingQty,
                    ]
                );
                
                // Track if this was created or updated
                if ($startRecord->wasRecentlyCreated) {
                    $created++;
                } else {
                    $updated++;
                }
                
                $processed++;
            }
            
            DB::commit();
            
            $message = "تم إنشاء بداية المدة تلقائياً. تم معالجة {$processed} منتج.";
            if ($created > 0) {
                $message .= " تم إنشاء {$created} بداية جديدة.";
            }
            if ($updated > 0) {
                $message .= " تم تحديث {$updated} بداية موجودة.";
            }
            
            return redirect()->route('product inventory')->with('success', $message);
            
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('product inventory')->with('error', 'حدث خطأ أثناء إنشاء بداية المدة: ' . $e->getMessage());
        }
    }
    
    /**
     * Calculate ending quantity for main inventory product
     */
    private function calculateMainInventoryEndingQty($product, $month)
    {
        $monthStart = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $monthEnd = Carbon::createFromFormat('Y-m', $month)->endOfMonth();
        
        // Get start quantity for the month
        $startQty = Start_Inventory::where('product_id', $product->id)
            ->where('month', $monthStart->format('Y-m-d'))
            ->value('qty') ?? 0;
        
        // Get added quantities
        $addedQty = DB::table('product_addeds')
            ->where('product_id', $product->id)
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->sum('qty') ?? 0;
        
        // Get sold quantities (aggregate from all branches)
        $soldQty = DB::table('sells')
            ->join('product_branches', 'sells.product_branch_id', '=', 'product_branches.id')
            ->where('product_branches.product_id', $product->id)
            ->whereBetween('sells.created_at', [$monthStart, $monthEnd])
            ->sum('sells.qty') ?? 0;
        
        return max(0, $startQty + $addedQty - $soldQty);
    }
}
