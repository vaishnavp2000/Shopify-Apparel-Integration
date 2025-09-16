<?php

namespace App\Console\Commands\ApparelMagic;

use App\Models\Order;
use App\Traits\ApparelMagic\ApparelMagicHelper;
use Illuminate\Console\Command;

class CancelAmOrder extends Command
{
    use ApparelMagicHelper;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cancel-am-order{--orderId=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cancel an ApparelMagic order by Shopify Order ID';

    /**
     * Execute the console command.
     */
   public function handle()
{
    $orderId = $this->option('orderId');

    if ($orderId) {
        $order = Order::where('shopify_order_id', $orderId)->first();

        if ($order) {
            $amOrderId = $order->am_order_id;

            if (!empty($amOrderId) && $order->is_cancelled == 0) {
                $apparelOrder = $this->getAmOrderById($amOrderId);

                if (!empty($apparelOrder['response']) && is_array($apparelOrder['response'])) {
                    $orderCancelResponse = $this->cancelApparelOrder($amOrderId);

                    if (!empty($orderCancelResponse)) {
                        $order->is_cancelled = 1;
                        $order->save();
                        $this->info("Order cancelled successfully.");
                    } else {
                        $this->warn("Order cancellation failed.");
                    }
                } else {
                    $this->warn("No ApparelMagic order found.");
                }
            } else {
                $this->warn("Order either already cancelled or missing ApparelMagic order ID.");
            }
        } else {
            $this->warn("Order not found with Shopify Order ID: $orderId");
        }
    } else {
        $this->warn("No order ID provided.");
    }
}

}
