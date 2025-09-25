<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Product_branch;
use Illuminate\Http\Request;

class ProductBranchController extends Controller
{
    public function index(Branch $branch_id, Request $request)
    {
        if (!empty($request->start_date)) {
            $start = explode('-', $request->start_date, 3)[0] . '-' . explode('-', $request->start_date, 3)[1] . '-01';
            $end = explode('-', $request->start_date, 3)[0] . '-' . explode('-', $request->start_date, 3)[1] . '-31';
            $products = Product_branch::where('branch_id', $branch_id->id)
                ->with([
                    'product',
                    'start' => function ($q) use ($start) {
                        $q->where('month', $start);
                    }
                ])->withSum(
                    [
                        'product_added' => function ($q) use ($start, $end, $branch_id) {
                            $q->whereBetween('created_at', [$start, $end])->where('branch_id', $branch_id->id);
                        }
                    ],
                    'qty'
                )->withSum(
                    [
                        'sell' => function ($q) use ($start, $end) {
                            $q->whereRaw('sells.created_at BETWEEN ? AND ?', [$start, $end]);
                        }
                    ],
                    'qty'
                )->get();
        } else {

            $start = date('Y-m') . '-01';
            $products = Product_branch::where('branch_id', $branch_id->id)
                ->with([
                    'product',
                    'start' => function ($q) use ($start) {
                        $q->where('month', $start);
                    },
                ])
                ->withSum(
                    [
                        'product_added' => function ($q) use ($branch_id) {
                            $q->where('branch_id', $branch_id->id)->whereBetween('created_at', [date('Y-m') . '-01', date('Y-m') . '-31']);
                        }
                        // 'product_added' => function ($q) use ($branch_id) {
                        //     $q->where('branch_id', $branch_id->id);
                        // }
                    ],
                    'qty'
                )->withSum(
                    [
                        'sell' => function ($q) {
                            $q->whereRaw('sells.created_at BETWEEN ? AND ?', [date('Y-m') . '-01', date('Y-m') . '-31']);
                        }
                    ],
                    'qty'
                )->get();
        }
        $date = date('Y-m');
        $qty = $products->map(function ($product) use ($date, $branch_id) {
            return [
                'product' => $product,
                'qty' => $product->qty($date, $branch_id->id),
            ];
        });
        return view('pages.product_branch.inventory', compact('products', 'branch_id', 'start', 'qty'));
    }
}
