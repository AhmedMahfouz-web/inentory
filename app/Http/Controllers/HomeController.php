<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Product;
use App\Models\Product_branch;
use Illuminate\Http\Request;

class HomeController extends Controller
{

    public function index()
    {
        $branches = Branch::select('id', 'name')->get();
        $products = Product::withSum(
            [
                'product_added' => function ($q) {
                    $q->whereBetween('created_at', [date('Y-m') . '-01', date('Y-m') . '-31']);
                }
            ],
            'qty'
        )->withSum(
            [
                'sell' => function ($q) {
                    $q->whereBetween('created_at', [date('Y-m') . '-01', date('Y-m') . '-31']);
                }
            ],
            'qty'
        )->get();
        $branches = Branch::with(['product_branches' => function ($q) {
            $q->with('product')->withSum(['product_added' => function ($q) {
                $q->whereBetween('created_at', [date('Y-m') . '-01', date('Y-m') . '-31']);
            }], 'qty');
        }])->get();
        $total_income = 0;
        $total_sells = 0;
        $total_income_branch = [];
        foreach ($products as $product) {
            $total_income += $product->price * $product->product_added_sum_qty;
            $total_sells += $product->price * $product->sell_sum_qty;
        }
        // foreach ($branches as $index => $branch) {
        //     $total_income_branch += array()
        // }



        return view('welcome', compact('branches', 'total_income', 'total_sells'));
    }
}
