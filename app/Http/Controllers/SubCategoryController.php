<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Http\Request;

class SubCategoryController extends Controller
{
    public function index()
    {
        $categories = SubCategory::with('category')->get();
        $parent_category = Category::all();

        return view('pages.sub_category.index', compact('categories', 'parent_category'));
    }

    public function create()
    {
        $parent_category = Category::all();
        return view('pages.sub_category.create', compact('parent_category'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'code' => 'required'
        ]);

        $parent_category = Category::where('id', $request->category)->first();

        $category = SubCategory::create([
            'name' => $request->name,
            'code' => $parent_category->code . '-' . $request->code,
            'category_id' => $request->category,
        ]);

        return redirect()->route('show sub_categories')->with(['success' => 'تم اضافة القسم بنجاح']);
    }

    public function edit(SubCategory $category)
    {
        $parent_categories = Category::all();
        return view('pages.sub_category.edit', compact('category', 'parent_categories'));
    }

    public function update(Request $request, SubCategory $category)
    {
        $request->validate([
            'name' => 'required|string',
            'code' => 'required'
        ]);

        $parent_category = Category::where('id', $request->category)->first();

        $category->update([
            'name' => $request->name,
            'category_id' => $request->category,
        ]);

        return redirect()->route('show sub_categories')->with(['success' => 'تم تعديل القسم بنجاح']);
    }

    public function destroy(SubCategory $category)
    {

        $category->delete();

        return redirect()->route('show sub_categories')->with(['success' => 'تم ازالة القسم بنجاح']);
    }
}
