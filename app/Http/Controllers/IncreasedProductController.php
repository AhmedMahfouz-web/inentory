<?php

namespace App\Http\Controllers;

use App\Models\IncreasedProduct;
use App\Models\Product;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IncreasedProductController extends Controller
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

            $increased_products = IncreasedProduct::whereDate('created_at', '>=', $start_date)
                ->whereDate('created_at', '<=', $end_date)
                ->with(['product' => function ($q) {
                    $q->with(['sub_category', 'unit']);
                }, 'supplier'])->latest()->get();
            return view('pages.increased_product.index', compact('increased_products', 'start_date', 'end_date'));
        }

        return view('pages.increased_product.index');
    }

    public function create()
    {
        $products = Product::orderBy('name', 'asc')->get();
        $suppliers = Supplier::all();

        return view('pages.increased_product.create', compact('products', 'suppliers'));
    }

    public function store(Request $request)
    {

        foreach ($request->product as $increased_product) {
            if (!empty($increased_product) && $increased_product['product_id'] != null && $increased_product['qty'] != null && $increased_product['qty'] != 0) {
                $product = Product::where('id', $increased_product['product_id'])->first();
                DB::beginTransaction();

                IncreasedProduct::create([
                    'product_id' => $increased_product['product_id'],
                    'price' => $increased_product['price'],
                    'supplier_id' => $increased_product['supplier_id'],
                    'qty' => $increased_product['qty']
                ]);
                $product->increment('stock', $increased_product['qty']);
                $product->update(['price' => $increased_product['price']]);
                DB::commit();
            }
        }

        return redirect()->route('increased product')->with(['success' => 'تم اضافة الصنف بنجاح']);
    }
}
