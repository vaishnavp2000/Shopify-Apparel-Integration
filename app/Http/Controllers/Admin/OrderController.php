<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ApparelMagic\CreateApparelOrders;
use App\Jobs\Shopify\GetShopifyOrders;
use App\Models\Order;
use App\Models\Setting;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;


class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */ public function index(Request $request, Datatables $datatables)
    {
        if ($request->ajax()) {
            $query = Order::select('order_id','shopify_order_id','customer_name','phone','email','amount','fulfillment_status','date','country','state','balance');

            return DataTables::of($query) 
            ->make(true);
        }

        return view('admin.orders.list');
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
            'message' => 'Order fetch has been started. You will see updates shortly.'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Failed to start order fetch.',
            'error'   => $e->getMessage()
        ], 500);
    }
    }
    public function createAmOrders(Request $request)
    {
    $orderId = $request->order_id;
    dd()
    $sync_all = $request->sync_all;

    if ($orderId) {
       Artisan::call('app:create-am-products', [
            '--orderId' => $orderId
        ]);
    }
    if ($sync_all == 1) {
        $orders = Order::whereNotNull('shopify_order_id')->get();

        foreach ($orders as $order) {
            $orderProducts = $order->orderProducts()->get();
            $response = $this->getOrderByShopifyOrderId($order->shopify_order_id);

            if (empty($response['response']) || !is_array($response['response'])) {
                info("Creating apparel order " . $order->shopify_order_id);
                CreateApparelOrders::dispatch($order, $orderProducts);
            } else {
                info("Order already exists " . $order->shopify_order_id);
        }
    }

    return response()->json(['message' => 'Orders sync process initiated']);
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
