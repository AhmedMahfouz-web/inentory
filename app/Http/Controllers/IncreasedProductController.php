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
        $increased_products = IncreasedProduct::whereDate('created_at', '>=', date("Y-m-d"))
            ->whereDate('created_at', '<=', date("Y-m-d"))
            ->with(['product' => function ($q) {
                $q->with(['sub_category', 'unit']);
            }, 'supplier'])->latest()->get();


        return view('pages.increased_product.index', compact('increased_products'));
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
            if (!empty($increased_product)) {
                DB::beginTransaction();

                IncreasedProduct::create([
                    'product_id' => $increased_product['product_id'],
                    'price' => $increased_product['price'],
                    'supplier_id' => $increased_product['supplier_id'],
                    'qty' => $increased_product['qty'],
                    'created_at' => $request->created_at,
                    'created_by' => auth()->user()->name
                ]);


                $product = Product::findOrFail($increased_product['product_id']);
                $product->increment('stock', $increased_product['qty']);

                $date = explode('-', $request->created_at, 3)[0] . '-' . explode('-', $request->created_at, 3)[1] . '-01';
                $price = calculateProductPrice($product, $increased_product, $date);


                $product->update(['price' => $price]);

                DB::commit();
            }
        }

        return redirect()->route('increased product')->with(['success' => 'تم اضافة الصنف بنجاح']);
    }


    public function edit(IncreasedProduct $product_increased)
    {
        $products = Product::orderBy('name', 'asc')->get();
        $suppliers = Supplier::all();
        $product_increased->load('product');

        return view('pages.increased_product.edit', compact('products', 'suppliers', 'product_increased'));
    }


    public function update(Request $request, IncreasedProduct $product_increased)
    {
        return auth()->user()->name;
        $product_increased->update([
            'product_id' => $request->product[0]['product_id'],
            'price' => $request->product[0]['price'],
            'supplier_id' => $request->product[0]['supplier_id'],
            'qty' => $request->product[0]['qty'],
            'updated_by' => auth()->user()->name
        ]);


        return redirect()->route('increased product')->with(['success' => 'تم تعديل الصنف بنجاح']);
    }
}
