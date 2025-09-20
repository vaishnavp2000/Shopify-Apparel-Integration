<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ApparelMagic\CreateApparelOrders;
use App\Jobs\Shopify\GetShopifyOrders;
use App\Models\Order;
use App\Models\Setting;
use App\Traits\ApparelMagic\ApparelMagicHelper;
use App\Traits\Shopify\ShopifyHelper;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    use ApparelMagicHelper, ShopifyHelper;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Order::with('returnOrder')->select('orders.*')->orderBy('id', 'asc')->get();


            return DataTables::of($query)
                ->addColumn('status', function ($order) {
                    if ($order->is_cancelled == 1) {
                        return '<span class="badge bg-danger">Cancelled</span>';
                    }

                    if ($order->allocated == 1 && empty($order->pick_ticket_id)) {
                        return '<span class="badge bg-info">Allocated (No Ticket)</span>';
                    }

                    if ($order->allocated == 1 && !empty($order->pick_ticket_id) && empty($order->shipment_id)) {
                        return '<span class="badge bg-primary">Pick Ticket Created</span>';
                    }

                    if ($order->allocated == 1 && !empty($order->pick_ticket_id) && !empty($order->shipment_id)) {
                        if (!empty(optional($order->returnOrder)->return_authorization_id)) {
                            if ($order->is_refund == 1) {
                                return '<span class="badge bg-warning">Refunded</span>';
                            }
                            return '<span class="badge bg-secondary">Return Order</span>';
                        }
                        return '<span class="badge bg-success">Fulfilled</span>';
                    }

                    return '<span class="badge bg-warning">Pending</span>';
                })
                ->addColumn('action', function ($order) {
                    $actions = '<div class="d-flex">';

                    $actions .= '
                <a href="' . route('admin.order.show', $order->id) . '" 
                    class="btn btn-sm btn-clean btn-icon text-end" 
                    title="Show">
                    <i class="fa fa-eye"></i>
                </a>';
                    if (!empty($order->shipment_id) && strtolower($order->shopify_fulfillment_status) != 'fulfilled') {
                        $actions .= '
                        <button class="btn btn-sm btn-primary fulfil-order-btn me-1" 
                            data-id="' . $order->id . '">
                        Fulfill
                        </button>';
                    }

                    if ($order->is_cancelled != 1 && empty($order->shipment_id)) {
                        $actions .= '
                    <button class="btn btn-sm btn-danger cancel_order_btn" 
                        data-id="' . $order->id . '">
                    Cancel Order
                    </button>';
                    }

                    $actions .= '</div>';

                    return $actions;
                })
                ->rawColumns(['action', 'status'])
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
                            } else {
                                $pickticket = $this->getApparelPickTicketsByOrderId($orderData->am_order_id);
                                if ($pickticket && isset($pickticket['pick_ticket_id'])) {
                                    $orderData->allocated = 1;
                                    $orderData->pick_ticket_id = $pickticket['pick_ticket_id'];
                                    $orderData->save();

                                }
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
                return response()->json(['message' => 'Order not found.'], 404);
            }
        }
        if ($sync_all == 1) {
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
    public function processReturn(Request $request)
    {
        // dd($request->all());
        $orderId = $request->order_id;
        $reason = $request->reason;

        $orderData = Order::where('id', $orderId)->first();
        // dd($orderData);

        if ($orderData) {
            $this->returnApparelOrder($orderData, $reason);
            return response()->json([
                'success' => true,
                'message' => 'Return processed successfully.'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Order not found.'
            ], 404);
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
        $order = Order::with('orderProducts', 'returnOrder')->find($id);

        return view('admin.orders.detail', compact('order'));
    }
    public function createShipment(Request $request)
    {

        $request->validate([
            'order_ids' => 'required',
        ]);

        $orderIds = is_array($request->order_ids) ? $request->order_ids : explode(',', $request->order_ids);
        // dd($orderIds);
        $results = [];

        foreach ($orderIds as $orderId) {
            $order = Order::where('am_order_id', $orderId)->first();
            // dd($order);
            if (!$order) {
                $results[$orderId] = [
                    'success' => false,
                    'message' => 'Order not found'
                ];
                continue;
            }

            if (empty($order->pick_ticket_id)) {
                $results[$orderId] = [
                    'success' => false,
                    'message' => 'Pick ticket ID is missing'
                ];
                continue;
            }

            $picktickets = $this->getApparelPickTickets($order->pick_ticket_id);
            // dd($picktickets);

            if (empty($picktickets)) {
                $results[$orderId] = [
                    'success' => false,
                    'message' => 'Pick ticket details not found'
                ];
                continue;
            }
            $result = $this->createApparelShipment($picktickets);


            if (!empty($result['error'])) {
                $results[$orderId] = [
                    'success' => false,
                    'message' => $result['message'] ?? 'Shipment creation failed'
                ];
            } else {
                $results[$orderId] = [
                    'success' => true,
                    'message' => $result['message'] ?? 'Shipment processed successfully',
                    'ship_id' => $result['ship_id'] ?? null
                ];
            }
        }

        return response()->json([
            'success' => true,
            'results' => $results
        ], 200);
    }

    public function cancelOrder(Request $request)
    {
        $orderId = $request->order_id;

        if (!$orderId) {
            return response()->json(['success' => false, 'message' => 'No order ID provided.']);
        }

        $order = Order::find($orderId);

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found.']);
        }

        if ($order->is_cancelled) {
            return response()->json(['success' => false, 'message' => 'Order already cancelled.']);
        }

        $amOrderId = $order->am_order_id;

        if (empty($amOrderId)) {
            return response()->json(['success' => false, 'message' => 'Missing ApparelMagic order ID.']);
        }

        $apparelOrder = $this->getAmOrderById($amOrderId);

        if (empty($apparelOrder['response']) || !is_array($apparelOrder['response'])) {
            return response()->json(['success' => false, 'message' => 'No ApparelMagic order found.']);
        }

        $orderCancelResponse = $this->cancelApparelOrder($amOrderId);

        if (!empty($orderCancelResponse)) {
            $order->is_cancelled = 1;
            $order->save();
            return response()->json(['success' => true, 'message' => 'Order cancelled successfully.']);
        }

        return response()->json(['success' => false, 'message' => 'Order cancellation failed.']);
    }
    public function fulfilOrder(Request $request)
    {
        $orderId = $request->order_id;
        $order = Order::find($orderId);

        if (empty($order)) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        try {
            $result = $this->shopifyFulfilOrder($order);

            return response()->json([
                'success' => $result['error'] === 0,
                'message' => $result['message'] ?? 'Fulfillment process completed'
            ], 200);

        } catch (\Exception $e) {


            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fulfilling the order'
            ], 500);
        }
    }
    public function createCreditMemo(Request $request)
    {
        // dd( $request->all());
        $orderId = $request->order_id;
        $orderData = Order::where('id', $orderId)->first();
        if ($orderData) {
            $this->createApparelCreditMemo($orderData);
            return response()->json([
                'success' => true,
                'message' => 'Created Credit Memo processed successfully.'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Order not found.'
            ], 404);
        }
    }
    public function createAmRefund(Request $request)
    {
        $orderId = $request->order_id;
        $orderData = Order::where('id', $orderId)->first();
        if ($orderData) {
            $this->createApparelRefund($orderData);
            return response()->json([
                'success' => true,
                'message' => 'Created Credit Memo processed successfully.'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Order not found.'
            ], 404);
        }

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
