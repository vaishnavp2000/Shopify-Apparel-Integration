<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\Shopify\GetShopifyOrders;
use App\Models\Order;
use App\Models\Setting;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;


class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */ public function index(Request $request, Datatables $datatables)
    {
        if ($request->ajax()) {
            $query = Order::select('order_id','product_id','style_number','amount','shopify_order_id');

            return DataTables::of($query) 
            ->make(true);
        }

        return view('orders.list');
    }
    public function fetchOrders(){
    try {
        $settings = Setting::where('type', 'shopify')
            ->where('status', 1)
            ->get();

        $limit = 200;
        $reverse = false;
        $nextPageCursor = null;
        // $variantCount = 5;

        GetShopifyOrders::dispatch((int) $limit, $reverse, $nextPageCursor, $settings);

        return response()->json([
            'status' => true,
            'message' => 'Product fetch has been started. You will see updates shortly.'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Failed to start product fetch.',
            'error'   => $e->getMessage()
        ], 500);
    }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
