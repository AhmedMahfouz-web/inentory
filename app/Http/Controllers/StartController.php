<?php

namespace App\Http\Controllers;

use App\Models\Product_branch;
use App\Models\Start;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StartController extends Controller
{
    public function index($branch_id)
    {
        $start = date('Y-m') . '-01';
        $products = Product_branch::where('branch_id', $branch_id)
            ->with([
                'product',
                'start' => function ($q) use ($start) {
                    $q->where('month', $start);
                },
            ])->get();
        return view('pages.start.create', compact('products', 'branch_id'));
    }

    public function store(Request $request, $branch_id)
    {
        foreach ($request->start as $index => $start) {
            DB::beginTransaction();
            if ($start == null) {
                $start = 0;
            }
            $start_row = Start::where('product_branch_id', $request->product_id[$index])->where('month', date('Y-m') . '-01')->first();
            if (empty($start_row)) {
                Start::create([
                    'product_branch_id' => $request->product_id[$index],
                    'month' => date('Y-m') . '-01',
                    'qty' => $start,
                ]);
            } else {
                $start_row->update([
                    'qty' => $start,
                    'month' => date('Y-m') . '-01',
                ]);
            }
            DB::commit();
        }
        return redirect()->route('inventory', $branch_id)->with('success', 'تم تعديل بداية المدة بنجاح.');
    }

    public function store_auto()
    {
        try {
            DB::beginTransaction();

            $date = Carbon::now()->subMonth()->year . '-' . Carbon::now()->subMonth()->month;
            $products = Product_branch::all();
            $processed = 0;
            
            foreach ($products as $product) {
                $qty = $product->qty($date, $product->branch_id);
                
                // Use updateOrCreate to avoid duplicates
                Start::updateOrCreate(
                    [
                        'product_branch_id' => $product->id,
                        'month' => date('Y-m') . '-01',
                    ],
                    ['qty' => $qty]
                );
                
                $processed++;
            }
            
            DB::commit();

            return redirect()->route('home')->with('success', "تم تعديل بداية المدة بنجاح. تم معالجة {$processed} منتج.");
            
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('home')->with('error', 'حدث خطأ أثناء تعديل بداية المدة: ' . $e->getMessage());
        }
    }

    /**
     * Auto-generate starts using MySQL stored procedure (faster for large datasets)
     */
    public function store_auto_mysql()
    {
        try {
            $targetMonth = date('Y-m') . '-01';
            
            // Call MySQL stored procedure for better performance
            DB::statement('CALL GenerateBranchInventoryStarts(?)', [$targetMonth]);
            
            // Get summary using MySQL function
            $summary = DB::select('SELECT GetMonthlyInventorySummary(?) as summary', [$targetMonth]);
            $summaryData = json_decode($summary[0]->summary, true);
            
            $branchCount = $summaryData['branch_inventory']['count'] ?? 0;
            
            return redirect()->route('home')->with('success', "تم تعديل بداية المدة بنجاح باستخدام MySQL. تم معالجة {$branchCount} منتج.");
            
        } catch (\Exception $e) {
            return redirect()->route('home')->with('error', 'حدث خطأ أثناء تعديل بداية المدة: ' . $e->getMessage());
        }
    }
}
