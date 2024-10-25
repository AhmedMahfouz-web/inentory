<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    function __construct()
    {
        $this->middleware(['permission:category-show|category-create|category-edit|category-delete'], ['only' => ['index', 'show']]);
        $this->middleware(['permission:category-create'], ['only' => ['create', 'store']]);
        $this->middleware(['permission:category-edit'], ['only' => ['edit', 'update']]);
        $this->middleware(['permission:category-delete'], ['only' => ['destroy']]);
    }

    public function index()
    {
        $categories = Category::all();

        return view('pages.category.index', compact('categories'));
    }

    public function create()
    {
        return view('pages.category.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'code' => 'required'
        ]);

        $category = Category::create([
            'name' => $request->name,
            'code' => $request->code
        ]);

        return redirect()->route('show categories')->with(['success' => 'تم اضافة القسم بنجاح']);
    }

    public function edit(Category $category)
    {
        return view('pages.category.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string',
            'code' => 'required'
        ]);

        $category->update([
            'name' => $request->name,
            'code' => $request->code
        ]);

        return redirect()->route('show categories')->with(['success' => 'تم تعديل القسم بنجاح']);
    }

    public function destroy(Category $category)
    {

        $category->delete();

        return redirect()->route('show categories')->with(['success' => 'تم ازالة القسم بنجاح']);
    }
}
