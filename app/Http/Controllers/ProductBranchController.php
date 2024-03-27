<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Product_branch;
use Illuminate\Http\Request;

class ProductBranchController extends Controller
{
    public function index(Branch $branch_id)
    {
        $products = Product_branch::where('branc_id', $branch_id)->with('product')->get();

        return view('pages.product_branch.index', compact('products'));
    }
}
