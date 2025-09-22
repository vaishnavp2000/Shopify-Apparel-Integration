<?php

namespace App\Jobs\Shopify;

use App\Traits\Shopify\ShopifyHelper;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GetShopifyInventory implements ShouldQueue
{
    use Queueable,ShopifyHelper;

    /**
     * Create a new job instance.
     */
    protected $settings,$nextPageCursor,$limit;
    public function __construct($settings,$nextPageCursor,$limit)
    {
        $this->settings=$settings;
        $this->nextPageCursor=$nextPageCursor;
        $this->limit=$limit;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->fetchShopifyInventoryItems($this->settings,$this->nextPageCursor,$this->limit);
    }
}
