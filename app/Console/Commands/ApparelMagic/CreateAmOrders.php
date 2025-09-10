<?php

namespace App\Console\Commands\ApparelMagic;

use App\Jobs\ApparelMagic\CreateApparelOrders;
use App\Models\Order;
use Illuminate\Console\Command;

class CreateAmOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-am-orders';

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
    $order= Order::with('orderProducts')->whereNotNull('shopify_order_id')->get();
   
    foreach ($order as $order) {
      // dd($order->orderProducts);
        CreateApparelOrders::dispatch($order);
    }
  }
}
