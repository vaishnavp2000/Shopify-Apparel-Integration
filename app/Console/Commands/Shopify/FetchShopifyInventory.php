<?php

namespace App\Console\Commands\Shopify;

use App\Jobs\Shopify\GetShopifyInventory;
use App\Models\Setting;
use Illuminate\Console\Command;

class FetchShopifyInventory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-shopify-stock';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch the shopify inventory';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $nextPageCursor=null;
        $limit=10;
        $settings=Setting::where('type', 'shopify')->where('status', 1)->get();
        GetShopifyInventory::dispatch($settings,$nextPageCursor,$limit);
    }
}
