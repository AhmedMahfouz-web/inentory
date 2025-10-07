<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Start_Inventory;
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
        try {
            $products = Product::all();
            $processed = 0;
            
            foreach ($products as $product) {
                $product_start = Start_Inventory::where('product_id', $product->id)
                    ->where('month', date('Y-m') . '-01')
                    ->first();
                    
                if (!empty($product_start)) {
                    $product->update([
                        'stock' => $product_start->qty,
                    ]);
                    $processed++;
                }
            }
            
            return redirect()->route('product inventory')->with('success', "تم تعديل بداية المدة بنجاح. تم معالجة {$processed} منتج.");
            
        } catch (\Exception $e) {
            return redirect()->route('product inventory')->with('error', 'حدث خطأ أثناء تعديل بداية المدة: ' . $e->getMessage());
        }
    }

    /**
     * Auto-generate main inventory starts using MySQL stored procedure
     */
    public function auto_generate()
    {
        try {
            $targetMonth = date('Y-m') . '-01';
            
            // Call MySQL stored procedure for better performance
            DB::statement('CALL GenerateMainInventoryStarts(?)', [$targetMonth]);
            
            // Get summary using MySQL function
            $summary = DB::select('SELECT GetMonthlyInventorySummary(?) as summary', [$targetMonth]);
            $summaryData = json_decode($summary[0]->summary, true);
            
            $mainCount = $summaryData['main_inventory']['count'] ?? 0;
            
            return redirect()->route('product inventory')->with('success', "تم إنشاء بداية المدة تلقائياً. تم معالجة {$mainCount} منتج.");
            
        } catch (\Exception $e) {
            return redirect()->route('product inventory')->with('error', 'حدث خطأ أثناء إنشاء بداية المدة: ' . $e->getMessage());
        }
    }
}
