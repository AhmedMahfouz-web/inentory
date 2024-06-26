<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Order;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        if (!empty($request->start_date)) {
            $start_date = $request->start_date;
            if (!empty($request->end_date)) {
                $end_date = $request->end_date;
            } else {
                $end_date = date("Y-m-d");
            }

            $orders = Order::whereDate('created_at', '>=', $start_date)
                ->whereDate('created_at', '<=', $end_date)->with(['branch'])->latest()->get();
            return view('pages.order.index', compact('orders', 'start_date', 'end_date'));
        }

        return view('pages.order.index');
    }

    public function edit(Request $request, Order $order)
    {
        $branches = Branch::all();
        $products = Product::all();
        $order->load(['branch', 'product_added' => function ($q) {
            $q->with('product');
        }]);
        return view('pages.order.edit', compact('order', 'branches', 'products'));
    }

    public function update(Request $request, Order $order)
    {
        $order->load('product_added');
        $order->update([
            'branch_id' => $request->branch_id,
            'updated_by' => auth()->user()->name,
        ]);
        foreach ($order->product_added as $index => $product_added) {
            $product_added->update([
                'product' => $request->product[$index]['product_id'],
                'qty' => $request->product[$index]['qty'],
                'branch_id' => $request->branch_id,
                'updated_by' => auth()->user()->name,
            ]);
        }

        return redirect()->route('show order')->with('success', 'تم تعديل التحويل بنجاح');
    }

    public function print(Order $order)
    {
        // Assuming you have data to pass
        $order->load(['branch', 'product_added' => function ($q) {
            $q->with(['product' => function ($q) {
                $q->with('unit');
            }]);
        }]);
        $pdf = Pdf::loadView('print', compact('order'))->setPaper('a4');

        return view('print', compact('order'));
    }
}
