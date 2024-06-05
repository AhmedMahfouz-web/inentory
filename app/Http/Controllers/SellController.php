<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Product_branch;
use App\Models\Sell;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SellController extends Controller
{
    public function index(Branch $branch_id)
    {
        $products = Product_branch::where('branch_id', $branch_id->id)
            ->with([
                'product',
            ])->get();
        return view('pages.sell.create', compact('products', 'branch_id'));
    }

    public function store(Request $request, $branch_id)
    {
        foreach ($request->product as $index => $product) {
            if (!empty($product['product_id'])) {
                DB::beginTransaction();
                $product_branch = Product_branch::where('id', $product['product_id'])->first()->qty(date('Y-m'));
                if ($product_branch >= $product['qty']) {
                    if ($product['qty'] != null) {
                        Sell::create([
                            'product_branch_id' => $product['product_id'],
                            'qty' => $product['qty'],
                        ]);
                    }
                }
                DB::commit();
            }
        }
        return redirect()->route('inventory', $branch_id)->with('success', 'تم صرف الاصناف بنجاح.');
    }
}
