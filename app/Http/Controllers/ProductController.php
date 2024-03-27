<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('category')->orderBy('code', 'asc')->get();
        $categories = Category::all();
        $units = Unit::all();

        return view('pages.product.index', compact(['products', 'categories', 'units']));
    }

    public function create()
    {
        $categories = Category::all();
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

        $category_code = Category::where('id', $request->category)->select('code')->first();

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
        $categories = Category::all();
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
}
