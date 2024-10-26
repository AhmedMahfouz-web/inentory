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

    public function getSoldProductsByCategory($id, $date)
    {
        // Validate the date format (optional)
        if (!\DateTime::createFromFormat('Y-m-d', $date)) {
            return redirect()->back()->with('error', 'Invalid date format. Use YYYY-MM-DD.');
        }

        // Find the category by ID
        $category = Category::find($id);

        if (!$category) {
            return redirect()->back()->with('error', 'Category not found.');
        }

        // Get sold products by category
        $soldQuantity = $category->soldProductsByCategory($date);

        // Pass the data to a Blade view
        return view('pages.reports.sold_products', [
            'category' => $category,
            'date' => $date,
            'sold_quantity' => $soldQuantity,
        ]);
    }
}
