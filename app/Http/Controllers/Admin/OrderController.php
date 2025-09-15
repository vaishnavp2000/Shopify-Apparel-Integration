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
     */
   public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Order::select('orders.*')->orderBy('id', 'asc');

            return DataTables::of($query)
                ->addColumn('action', function ($order) {
                    return '
                        <div class="d-flex">
                         <button class="btn btn-sm btn-primary fulfil-order-btn" 
                                data-id="' . $order->id . '">
                               Shipment
                            </button>
                            <a href="' . route('admin.order.show', $order->id) . '" 
                                class="btn btn-sm btn-clean btn-icon text-end" 
                                title="Show">
                                <i class="fa fa-eye"></i>
                            </a>
                        </div>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('admin.orders.list');
    }
    public function fetchOrders()
    {
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
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function createAmOrders(Request $request)
    {
        $orderId = $request->order_id;
        $sync_all = $request->sync_all;
        // dd($sync_all);

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
                    $this->updateApparelOrder($item);
                    $orderData = Order::where('am_order_id', $item['order_id'])->first();
                    if (!empty($orderData) && ($orderData->credit_status ?? '') != 'Pending') {
                        if ($orderData->allocated == 0) {
                            if ($this->apparelOrderAllocate($orderData)) {
                                $orderData->allocated = 1;
                                $orderData->save();
                            }
                        }
                        if ($orderData->allocated == 1) {
                            $pickticket = $this->createApparelPickTicket($orderData);
                            if (!empty($pickticket['pick_ticket_id'])) {
                                $orderData->pick_ticket_id = $pickticket['pick_ticket_id'];
                                $orderData->save();
                            }
                        }
                    }


                }

            } else {
                $this->error("Order not found with ID: {$orderId}");
            }
        }  if ($sync_all == 1)  {
            $orders = Order::with('orderProducts')->whereNotNull('shopify_order_id')->get();

            foreach ($orders as $order) {
                $response = $this->getApparelOrder($order->shopify_order_id);

                if (empty($response['response'])) {
                    info("Creating the order");
                    // CreateApparelOrders::dispatch($order);
                } else {
                   info("updating the order...");
                    $item = $response['response'][0];
                    $this->updateApparelOrder($item);
                    $orderData = Order::where('am_order_id', $item['order_id'])->first();
                    if (!empty($orderData) && ($orderData->credit_status ?? '') != 'Pending') {
                        if ($orderData->allocated == 0) {
                            if ($this->apparelOrderAllocate($orderData)) {
                                $orderData->allocated = 1;
                                $orderData->save();
                            }
                        }
                        if ($orderData->allocated == 1) {
                            $pickticket = $this->createApparelPickTicket($orderData);
                            if (!empty($pickticket['pick_ticket_id'])) {
                                $orderData->pick_ticket_id = $pickticket['pick_ticket_id'];
                                $orderData->save();
                            }
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
        $order = Order::with('orderProducts')->find($id);

        return view('admin.orders.detail', compact('order'));
    }
    public function createShipment(Request $request){
        // dd($request->pickticket_id);
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'pickticket_id' => 'required|string|max:255',
        ]);  
        $pickticket_id=$request->pickticket_id;
        if($pickticket_id){
            $picktickets=$this->getApparelPickTickets($pickticket_id);
             $result=$this->createApparelShipment($picktickets);
            if (!empty($result['error'])) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Shipment creation failed'
            ], 500);
            }
             return response()->json([
            'success' => true,
            'message' => $result['message'] ?? 'Shipment processed successfully',
            'ship_id' => $result['ship_id'] ?? null
            ], 200);
        }
         return response()->json([
        'success' => false,
        'message' => 'Pick ticket ID is required'
         ], 400);



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
