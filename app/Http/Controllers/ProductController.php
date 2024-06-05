<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\SubCategory;
use App\Models\Unit;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['sub_category' => function ($q) {
            $q->with('category');
        }])->orderBy('code', 'asc')->get();
        $categories = SubCategory::all();
        $units = Unit::all();

        return view('pages.product.index', compact(['products', 'categories', 'units']));
    }

    public function create()
    {
        $categories = SubCategory::all();
        $units = Unit::all();

        return view('pages.product.create', compact(['categories', 'units']));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'code' => 'required',
            'category' => 'required',
            'unit' => 'required',
        ]);

        $category_code = SubCategory::where('id', $request->category)->select('code')->first();

        $product = Product::create([
            'name' => $request->name,
            'category_id' => $request->category,
            'unit_id' => $request->unit,
            'min_stock' => $request->min_stock,
            'max_stock' => $request->max_stock,
            'code' => $category_code->code . '-' . $request->code,
        ]);


        return redirect()->route('show products')->with(['success' => 'تم اضافة الصنف بنجاح']);
    }

    public function edit(Product $product)
    {
        $categories = SubCategory::all();
        $units = Unit::all();

        return view('pages.product.edit', compact(['product', 'categories', 'units']));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string',
            'category' => 'required',
            'unit' => 'required',
        ]);

        $product->update([
            'name' => $request->name,
            'category_id' => $request->category,
            'unit_id' => $request->unit,
            'min_stock' => $request->min_stock,
            'max_stock' => $request->max_stock,
            'price' => $request->price,
        ]);


        return redirect()->route('show products')->with(['success' => 'تم تعديل الصنف بنجاح']);
    }

    public function destroy(Product $product)
    {

        $product->delete();

        return redirect()->route('show products')->with(['success' => 'تم ازالة الصنف بنجاح']);
    }

    public function inventory(Request $request)
    {
        if (!empty($request->start_date)) {
            $start = explode('-', $request->start_date, 3)[0] . '-' . explode('-', $request->start_date, 3)[1] . '-01';
            $end = explode('-', $request->start_date, 3)[0] . '-' . explode('-', $request->start_date, 3)[1] . '-31';
            $products = Product::with([
                'start' => function ($q) use ($start) {
                    $q->where('month', $start);
                }
            ])->withSum(
                [
                    'product_added' => function ($q) use ($start, $end) {
                        $q->whereBetween('created_at', [$start, $end]);
                    }
                ],
                'qty'
            )->withSum(
                [
                    'sell' => function ($q) use ($start, $end) {
                        $q->whereBetween('created_at', [$start, $end]);
                    }
                ],
                'qty'
            )->get();
        } else {

            $start = date('Y-m') . '-01';
            $products = Product::with([
                'start' => function ($q) use ($start) {
                    $q->where('month', $start);
                },
            ])
                ->withSum(
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
        }

        return view('pages.product.inventory', compact('products', 'start'));
    }
}
