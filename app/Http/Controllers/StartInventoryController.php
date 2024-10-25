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
        foreach ($request->start as $index => $start) {
            if ($start == null) {
                $start = 0;
            }
            $start_row = Start_Inventory::where('product_id', $request->product_id[$index])->where('month', date('Y-m') . '-01')->first();
            if (empty($start_row)   ) {
                Start_Inventory::create([
                    'product_id' => $request->product_id[$index],
                    'month' => date('Y-m') . '-01',
                    'qty' => $start,
                ]);
            } else {
                $start_row->update([
                    'qty' => $start,
                    'month' => date('Y-m') . '-01',
                ]);
            }
        }
        return redirect()->route('product inventory')->with('success', 'تم تعديل بداية المدة بنجاح.');
    }
    public function qty_store()
    {
        $products = Product::all();
        foreach ($products as $product) {
            $product_start = Start_Inventory::where('product_id', $product->id)->first();
            if (!empty($product_start)) {
                $product->update([
                    'stock' => $product_start->qty,
                ]);
            }
        }
        return redirect()->route('product inventory')->with('success', 'تم تعديل بداية المدة بنجاح.');
    }
}
