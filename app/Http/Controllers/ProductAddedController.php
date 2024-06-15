<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Order;
use App\Models\Product;
use App\Models\Product_branch;
use App\Models\ProductAdded;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductAddedController extends Controller
{
    public function index(Request $request)
    {
        if (!empty($request->start_date)) {
            $start_date = $request->start_date;
            if (!empty($request->end_date)) {
                $end_date = $request->end_date;
            } else {
                $end_date = date("Y-m-d");
            }

            $added_products = ProductAdded::whereDate('created_at', '>=', $start_date)
                ->whereDate('created_at', '<=', $end_date)
                ->with(['product' => function ($q) {
                    $q->with(['sub_category', 'unit']);
                }, 'branch'])->latest()->get();
            return view('pages.added_product.index', compact('added_products', 'start_date', 'end_date'));
        }

        return view('pages.added_product.index');
    }

    public function create()
    {
        $products = Product::orderBy('name', 'asc')->get();
        $branches = Branch::all();

        return view('pages.added_product.create', compact('products', 'branches'));
    }

    public function store(Request $request)
    {
        $errors = [];
        DB::beginTransaction();
        $order = Order::create([
            'branch_id' => $request->branch_id,
            'created_at' => $request->created_at
        ]);
        $order_id = $order->id;
        foreach ($request->product as $product_added) {
            if (!empty($product_added['product_id'])) {
                // if ($product_added['product_id'] != null && $product_added['qty'] != null && $product_added['qty'] != 0) {
                if ($product_added['product_id']) {
                    if ($product_added['qty'] == null) {
                        $qty = 0;
                    } else {
                        $qty = $product_added['qty'];
                    }
                    $product = Product::where('id', $product_added['product_id'])->first();
                    // if ($product->stock < $product_added['qty']) {
                    // $errors = "مخزون الـ" . $product->name . ' اقل من الكمية المنصرفة';
                    // } else {
                    $product_on_branch = Product_branch::where(['product_id' => $product_added['product_id'], 'branch_id' => $request['branch_id']])->first();
                    if (!empty($product_on_branch)) {
                        $product_on_branch->update(['price' => $product->price]);
                        $product_on_branch->increment('qty', $qty);
                    } else {
                        Product_branch::create([
                            'product_id' => $product_added['product_id'],
                            'branch_id' => $request->branch_id,
                            'qty' => $qty,
                            'price' => $product->price,
                            'created_at' => $request->created_at
                        ]);
                    }
                    productAdded::create([
                        'product_id' => $product_added['product_id'],
                        'price' => $product->price,
                        'branch_id' => $request->branch_id,
                        'qty' => $product_added['qty'],
                        'order_id' => $order_id,
                        'created_at' => $request->created_at
                    ]);
                    if ($product->stock > $product_added['qty']) {
                        $product->decrement('stock', $product_added['qty']);
                    }
                    // }
                }
            }
        }
        DB::commit();

        return redirect()->route('exchanged product')->with(['success' => 'تم تحويل الاصناف بنجاح', 'error' => $errors]);
    }

    public function edit(productAdded $productAdded)
    {
        return view('pages.productAdded.edit', compact('productAdded'));
    }

    public function update(Request $request, productAdded $productAdded)
    {
        $request->validate([
            'name' => 'required|string'
        ]);

        $productAdded->update([
            'name' => $request->name
        ]);

        return redirect()->route('show productAddeds')->with(['success' => 'تم تعديل الوحدة بنجاح']);
    }

    public function destroy(productAdded $productAdded)
    {

        $productAdded->delete();

        return redirect()->route('show productAddeds')->with(['success' => 'تم ازالة الوحدة بنجاح']);
    }
}
