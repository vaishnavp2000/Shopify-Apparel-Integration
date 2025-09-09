<?php

namespace App\Jobs\Shopify;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Traits\Shopify\ShopifyHelper;


class GetShopifyProducts implements ShouldQueue
{
    use Queueable;
    use ShopifyHelper;
    
    protected $limit,$reverse,$nextPageCursor,$settings,$variantCount;


    /**
     * Create a new job instance.
     */
    public function __construct($limit,$reverse,$variantCount,$nextPageCursor,$settings)
    {
         $this->limit = $limit;
         $this->reverse = $reverse;
         $this->variantCount=$variantCount;
         $this->nextPageCursor=$nextPageCursor;
         $this->settings=$settings;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
    $this->fetchProducts($this->limit, $this->reverse, $this->variantCount, $this->nextPageCursor, $this->settings);

    }
}
