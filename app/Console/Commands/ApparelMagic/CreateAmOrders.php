<?php

namespace App\Console\Commands\ApparelMagic;

use App\Jobs\ApparelMagic\CreateApparelOrders;
use App\Models\Order;
use App\Traits\ApparelMagic\ApparelMagicHelper;
use Illuminate\Console\Command;
use Carbon\Carbon;


class CreateAmOrders extends Command
{
    use ApparelMagicHelper;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-am-orders{--orderId=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $orderId = $this->option('orderId');

        if ($orderId) {
            $order = Order::with('orderProducts')->where('shopify_order_id', $orderId)->first();

            if ($order) {
                $orderId = $order->shopify_order_id;
                $response = $this->getApparelOrder($orderId);
                if (empty($response['response'])) {
                    $this->info("Creating the order");
                    $this->createApparelmagicOrder($order);
                } else {
                    $this->info("updating the order");
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
        } else {
            $orders = Order::with('orderProducts')->whereNotNull('shopify_order_id')->get();

            foreach ($orders as $order) {
                $response = $this->getApparelOrder($order->shopify_order_id);

                if (empty($response['response'])) {
                    $this->info("Creating the order");
                    CreateApparelOrders::dispatch($order);
                } else {
                    $this->info("updating the order");
                    $item = $response['response'][0];
                    $this->updateApparelOrder($item);
                    $orderData = Order::where('am_order_id', $item['order_id'])->first();
                    // $this->info("am-order-id",$orderData);
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
}



