<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{

    public function index()
    {
        $suppliers = Supplier::all();

        return view('pages.supplier.index', compact('suppliers'));
    }

    public function create()
    {
        return view('pages.supplier.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_name' => 'required|string',
            'betaqa_darebya_image' => 'image|mimes:jpeg,png,jpg',
            'segel_togary_image' => 'image|mimes:jpeg,png,jpg',
        ]);


        if (!empty($request->has_delivery)) {
            $has_delivery = true;
        } else {
            $has_delivery = false;
        }

        $supplier = Supplier::create([
            'name' => $request->supplier_name,
            'phone' => $request->phone,
            'address' => $request->address,
            'desc' => $request->desc,
            'has_delivery' => $has_delivery,
        ]);

        if (!empty($request->betaqa_drebya_image)) {
            $betaqa_drebya_image = time() . '.' . $request->betaqa_drebya_image->extension();
            $request->betaqa_drebya_image->move(public_path('images/betaqa_drebya'), $betaqa_drebya_image);
            $supplier->update([
                'betaqa_drebya' => $request->betaqa_drebya,
                'betaqa_drebya_image' => 'images/betaqa_drebya/' . $betaqa_drebya_image,
            ]);
        }

        if (!empty($request->segel_togary_image)) {
            $segel_togary_image = time() . '.' . $request->segel_togary_image->extension();
            $request->segel_togary_image->move(public_path('images/segel_togary'), $segel_togary_image);
            $supplier->update([
                'segel_togary' => $request->segel_togary,
                'segel_togary_image' => 'images/segel_togary/' . $segel_togary_image,
            ]);
        }

        return redirect()->route('show suppliers')->with(['success' => 'تم اضافة القسم بنجاح']);
    }

    public function edit(Supplier $supplier)
    {
        return view('pages.supplier.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $request->validate([
            'supplier_name' => 'required|string',
            'betaqa_darebya_image' => 'image|mimes:jpeg,png,jpg',
            'segel_togary_image' => 'image|mimes:jpeg,png,jpg',
        ]);


        if (!empty($request->has_delivery)) {
            $has_delivery = true;
        } else {
            $has_delivery = false;
        }

        $supplier->update([
            'name' => $request->supplier_name,
            'phone' => $request->phone,
            'address' => $request->address,
            'desc' => $request->desc,
            'has_delivery' => $has_delivery,
        ]);

        if (!empty($request->betaqa_drebya_image)) {
            $betaqa_drebya_image = time() . '.' . $request->betaqa_drebya_image->extension();
            $request->betaqa_drebya_image->move(public_path('images/betaqa_drebya'), $betaqa_drebya_image);
            $supplier->update([
                'betaqa_drebya' => $request->betaqa_drebya,
                'betaqa_drebya_image' => 'images/betaqa_drebya/' . $betaqa_drebya_image,
            ]);
        }

        if (!empty($request->segel_togary_image)) {
            $segel_togary_image = time() . '.' . $request->segel_togary_image->extension();
            $request->segel_togary_image->move(public_path('images/segel_togary'), $segel_togary_image);
            $supplier->update([
                'segel_togary' => $request->segel_togary,
                'segel_togary_image' => 'images/segel_togary/' . $segel_togary_image,
            ]);
        }

        return redirect()->route('show suppliers')->with(['success' => 'تم اضافة القسم بنجاح']);
    }

    public function destroy(Supplier $supplier)
    {

        $supplier->delete();

        return redirect()->route('show suppliers')->with(['success' => 'تم ازالة القسم بنجاح']);
    }
}
