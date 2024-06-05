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
                ]);
            }
            DB::commit();
        }
        return redirect()->route('inventory', $branch_id)->with('success', 'تم تعديل بداية المدة بنجاح.');
    }

    public function store_auto()
    {

        // DB::beginTransaction();

        $date = Carbon::now()->subMonth()->year . '-' . Carbon::now()->subMonth()->month;
        $products = Product_branch::all();
        $qty = $products->map(function ($product) use ($date) {
            return [
                'product' => $product,
                'qty' => $product->qty($date, $product->branch_id),
            ];
        });
        foreach ($qty as $product) {
            Start::create([
                'product_branch_id' => $product['product']->id,
                'month' => date('Y-m') . '-01',
                'qty' => $product['qty'],
            ]);
        }
        // DB::commit();

        return redirect()->route('home')->with('success', 'تم تعديل بداية المدة بنجاح.');
    }
}
