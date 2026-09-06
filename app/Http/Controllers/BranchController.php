<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:branch-show|branch-create|branch-edit|branch-delete'], ['only' => ['index', 'show']]);
        $this->middleware(['permission:branch-create'], ['only' => ['create', 'store']]);
        $this->middleware(['permission:branch-edit'], ['only' => ['edit', 'update']]);
        $this->middleware(['permission:branch-delete'], ['only' => ['destroy']]);
    }

    private function validateRequest(Request $request)
    {
        $request->validate([
            'name' => 'required|string'
        ]);
    }

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
        $this->validateRequest($request);

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
        $this->validateRequest($request);

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
