<?php

namespace App\Console\Commands\Shopify;

use App\Jobs\Shopify\GetShopifyOrders;
use App\Models\Setting;
use Illuminate\Console\Command;

class FetchOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-orders';

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
        $settings = Setting::where('type', 'shopify')->where('status', 1)->get();
        if(empty($settings)){
            return $this->info('No Settings Found.');
        }
        $limit = 20;
        $reverse = false;
        $nextPageCursor = null;
        GetShopifyOrders::dispatch((int)$limit,$reverse,$nextPageCursor,$settings);
    }
}
