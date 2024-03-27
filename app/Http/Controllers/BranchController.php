<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{

    public function index()
    {
        $branches = Branch::all();

        return view('pages.branch.index', compact('branches'));
    }

    public function create()
    {
        return view('pages.branch.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string'
        ]);

        $branch = Branch::create([
            'name' => $request->name
        ]);

        return redirect()->route('show branches')->with(['success' => 'تم اضافة المخزن الفرعي بنجاح']);
    }

    public function edit(Branch $branch)
    {
        return view('pages.branch.edit', compact('branch'));
    }

    public function update(Request $request, Branch $branch)
    {
        $request->validate([
            'name' => 'required|string'
        ]);

        $branch->update([
            'name' => $request->name
        ]);

        return redirect()->route('show branches')->with(['success' => 'تم تعديل المخزن الفرعي بنجاح']);
    }

    public function destroy(Branch $branch)
    {

        $branch->delete();

        return redirect()->route('show branches')->with(['success' => 'تم ازالة المخزن الفرعي بنجاح']);
    }
}
