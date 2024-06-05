<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class StartInventoryController extends Controller
{
    public function index()
    {
        $start = date('Y-m') . '-01';
        $products = Product::with([
            'start' => function ($q) use ($start) {
                $q->where('month', $start);
            },
        ])->get();
        return view('pages.start_inventory.create', compact('products'));
    }
}
