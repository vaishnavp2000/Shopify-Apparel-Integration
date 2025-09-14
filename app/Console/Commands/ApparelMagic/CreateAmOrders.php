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
            } else 
            {
            $this->info("updating the order");

            $item = $response['response'][0];
          $orderDetail = Order::updateOrCreate(
            ['shopify_order_id' =>  $item['customer_po']],
            [
                'am_order_id'    => $item['order_id'] ?? null,
                'customer_id'   => $item['customer_id'] ?? null,
                'division_id'   => $item['division_id'] ?? null,
                'warehouse_id'  => $item['warehouse_id'] ?? null,
                'currency_id'   => $item['currency_id'] ?? null,
                'arr_accnt'      => $item['ar_acct'] ?? null,
                'date'          =>  isset($item['date']) ? Carbon::parse($item['date'])->format('Y-m-d') : null,
                'date_start'    =>  isset($item['date_start']) ? Carbon::parse($item['date_start'])->format('Y-m-d') : null;,
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

            ]);
      if (!empty($item['order_items']) && is_array($item['order_items'])) {
        foreach ($item['order_items'] as $orderItem) {

            $orderDetail->orderProducts()->updateOrCreate(
        ['sku_id' => $orderItem['sku_id'],
                                'shopify_sku'=>$orderItem['sku_alt'],
                                'am_order_id'=>$orderItem['order_id']
                        ],
                [
                    'order_id'=>$orderDetail->id,
                    'am_order_id'=> $orderItem['order_id'] ?? null,
                    'am_order_item_id'=>$orderItem['id']??null,
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
    if (!empty($order) && ($order->credit_status ?? '') != 'Pending') {
            if ($orderDetail->allocated == 0) {
                  if ($this->apparelOrderAllocate($order)) {
                    $orderDetail->allocated = 1;
                    $orderDetail->save();       
                  }
                  
            }
            if ($orderDetail->allocated == 1){
                $pickticket = $this->createApparelPickTicket($order);
                $orderDetail->pick_ticket_id=$pickticket['pick_ticket_id'];
                $orderDetail->save();

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
            $date = isset($item['date']) ? Carbon::parse($item['date'])->format('Y-m-d') : null;
            $dateStart = isset($item['date_start']) ? Carbon::parse($item['date_start'])->format('Y-m-d') : null;

            $orderDetail = Order::updateOrCreate(
                ['shopify_order_id' => $order->shopify_order_id],
                [
                    'am_order_id'      => $item['order_id'] ?? null,
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
                                        'shopify_sku'=>$orderItem['sku_alt'],
                                        'am_order_id'=>$orderItem['order_id']
                                    ],
                        [
                            'order_id'=>$orderDetail->id,
                            'am_order_id'=> $orderItem['order_id'] ?? null,
                            'am_order_item_id'=>$orderItem['id']??null,
                            'sku_id' => $orderItem['sku_id'],
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
}
