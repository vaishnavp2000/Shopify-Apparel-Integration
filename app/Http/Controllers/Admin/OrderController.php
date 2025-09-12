<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ApparelMagic\CreateApparelOrders;
use App\Jobs\Shopify\GetShopifyOrders;
use App\Models\Order;
use App\Models\Setting;
use App\Traits\ApparelMagic\ApparelMagicHelper;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;



class OrderController extends Controller
{
    use ApparelMagicHelper;
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
    $sync_all = $request->sync_all;

    if ($orderId) {
        $order = Order::with('orderProducts')->where('shopify_order_id', $orderId)->first();

        if ($order) {
            $orderId = $order->shopify_order_id;
            $response = $this->getApparelOrder($orderId);
            // dd($response);
            if (empty($response['response'])) {
                // $this->info("order created");
                $this->createApparelmagicOrder($order);
            } else {
                // $this->info("order updated");
            $item = $response['response'][0];

            $date = isset($item['date']) ? Carbon::parse($item['date'])->format('Y-m-d') : null;
            $dateStart = isset($item['date_start']) ? Carbon::parse($item['date_start'])->format('Y-m-d') : null;

          $orderDetail = Order::updateOrCreate(
            ['shopify_order_id' =>  $item['customer_po']],
            [
                'order_id'      => $item['order_id'] ?? null,
                'customer_id'   => $item['customer_id'] ?? null,
                'division_id'   => $item['division_id'] ?? null,
                'warehouse_id'  => $item['warehouse_id'] ?? null,
                'currency_id'   => $item['currency_id'] ?? null,
                'arr_accnt'      => $item['ar_acct'] ?? null,
                'date'          => $date,
                'date_start'    => $dateStart,
                'source'        => $item['source'] ?? null,
                'notes'         => $item['notes'] ?? null,
                'customer_name' => $item['name'] ?? null,
                'address_1'     => $item['address_1'] ?? null,
                'address_2'     => $item['address_2'] ?? null,
                'city'          => $item['city'] ?? null,
                'postal_code'   => $item['postal_code'] ?? null,
                'country'       => $item['country'] ?? null,
                'state'         => $item['state'] ?? null,
                'phone'         => $item['phone'] ?? null,
                'email'         => $item['email'] ?? null,
                'credit_status'=>$item['credit_status']??null,
                'fulfillment_status'=>$item['fulfillment_status'] ?? null

            ]
        );
      if (!empty($item['order_items']) && is_array($item['order_items'])) {
        foreach ($item['order_items'] as $orderItem) {
            $orderDetail->orderProducts()->updateOrCreate(
                [
                'shopify_order_id'=>$orderDetail->shopify_order_id,
                'shopify_sku'=>$orderItem['sku_alt'],
            ],
                [
                    'sku_id' => $orderItem['sku_id']??null,
                    'product_id'   => $orderItem['product_id'] ?? null,
                    'sku_alt'      => $orderItem['sku_alt'] ?? null,
                    'upc'          => $orderItem['upc'] ?? null,
                    'style_number' => $orderItem['style_number'] ?? null,
                    'description'  => $orderItem['description'] ?? null,
                    'size'         => $orderItem['size'] ?? null,
                    'qty'          => $orderItem['qty'] ?? 0,
                    'qty_picked'=>$orderItem['qty_picked']??0,
                    'qty_cancelled'=>$orderItem['qty_cxl']??0, 
                    'qty_shipped'=>$orderItem['qty_shipped']??0,
                    'unit_price'   => $orderItem['unit_price'] ?? 0,
                    'amount'       => $orderItem['amount'] ?? 0,
                    'is_taxable'   => $orderItem['is_taxable'] ?? '0',
                    'warehouse_id' => $orderItem['warehouse_id'] ?? $item['warehouse_id'] ?? null,
                ]
            );
        }
    }
    }

        } else {
            $this->error("Order not found with ID: {$orderId}");
        }
    } 
   else {
     $orders = Order::with('orderProducts')->whereNotNull('shopify_order_id')->get();

    foreach ($orders as $order) {
        $response = $this->getApparelOrder($order->shopify_order_id);

       if (empty($response['response'])) {
            $this->createApparelmagicOrder($order);
        } else {
            $item = $response['response'][0];
            $date = isset($item['date']) ? Carbon::parse($item['date'])->format('Y-m-d') : null;
            $dateStart = isset($item['date_start']) ? Carbon::parse($item['date_start'])->format('Y-m-d') : null;

            $orderDetail = Order::updateOrCreate(
                ['shopify_order_id' =>$item['customer_po']],
                [
                    'order_id'      => $item['order_id'] ?? null,
                    'customer_id'   => $item['customer_id'] ?? null,
                    'division_id'   => $item['division_id'] ?? null,
                    'warehouse_id'  => $item['warehouse_id'] ?? null,
                    'currency_id'   => $item['currency_id'] ?? null,
                    'arr_accnt'      => $item['ar_acct'] ?? null,
                    'date'          => $date,
                    'date_start'    => $dateStart,
                    'source'        => $item['source'] ?? null,
                    'notes'         => $item['notes'] ?? null,
                    'customer_name' => $item['name'] ?? null,
                    'address_1'     => $item['address_1'] ?? null,
                    'address_2'     => $item['address_2'] ?? null,
                    'city'          => $item['city'] ?? null,
                    'postal_code'   => $item['postal_code'] ?? null,
                    'country'       => $item['country'] ?? null,
                    'state'         => $item['state'] ?? null,
                    'phone'         => $item['phone'] ?? null,
                    'email'         => $item['email'] ?? null,
                    'fulfillment_status'=>$item['fulfillment_status'] ?? null
                ]
                );
            if (!empty($item['order_items']) && is_array($item['order_items'])) {
                foreach ($item['order_items'] as $orderItem) {
                    $orderDetail->orderProducts()->updateOrCreate(
                        ['sku_id' => $orderItem['sku_id'],
                        'shopify_sku'=>$orderItem['sku_alt']
                      ],
                        [
                            'order_id'=>$orderItem['order_id']??null,
                            'product_id'   => $orderItem['product_id'] ?? null,
                            'sku_alt'      => $orderItem['sku_alt'] ?? null,
                            'upc'          => $orderItem['upc'] ?? null,
                            'style_number' => $orderItem['style_number'] ?? null,
                            'description'  => $orderItem['description'] ?? null,
                            'size'         => $orderItem['size'] ?? null,
                            'qty'          => $orderItem['qty'] ?? 0,
                            'qty_picked'=>$orderItem['qty_picked']??0,
                            'qty_cancelled'=>$orderItem['qty_cxl']??0, 
                            'qty_shipped'=>$orderItem['qty_shipped']??0,
                            'unit_price'   => $orderItem['unit_price'] ?? 0,
                            'amount'       => $orderItem['amount'] ?? 0,
                            'is_taxable'   => $orderItem['is_taxable'] ?? '0',
                            'warehouse_id' => $orderItem['warehouse_id'] ?? $item['warehouse_id'] ?? null,
                        ]
                    );
                }
            }
        }
    }


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
