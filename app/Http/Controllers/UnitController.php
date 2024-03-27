<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{

    public function index()
    {
        $units = Unit::all();

        return view('pages.unit.index', compact('units'));
    }

    public function create()
    {
        return view('pages.unit.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string'
        ]);

        $unit = Unit::create([
            'name' => $request->name
        ]);

        return redirect()->route('show units')->with(['success' => 'تم اضافة الوحدة بنجاح']);
    }

    public function edit(Unit $unit)
    {
        return view('pages.unit.edit', compact('unit'));
    }

    public function update(Request $request, Unit $unit)
    {
        $request->validate([
            'name' => 'required|string'
        ]);

        $unit->update([
            'name' => $request->name
        ]);

        return redirect()->route('show units')->with(['success' => 'تم تعديل الوحدة بنجاح']);
    }

    public function destroy(Unit $unit)
    {

        $unit->delete();

        return redirect()->route('show units')->with(['success' => 'تم ازالة الوحدة بنجاح']);
    }
}
