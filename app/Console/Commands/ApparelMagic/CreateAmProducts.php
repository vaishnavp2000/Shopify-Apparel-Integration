<?php

namespace App\Console\Commands\ApparelMagic;

use App\Jobs\ApparelMagic\CreateApparelMagicProducts;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Traits\ApparelMagic\ApparelMagicHelper;
use App\Traits\Shopify\ShopifyHelper;
use Illuminate\Console\Command;


class CreateAmProducts extends Command
{
     use ShopifyHelper,ApparelMagicHelper;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-am-products {--productId=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create Am products from shopify';

    /**
     * Execute the console command.
     */
  public function handle()
{
    $productId = $this->option('productId');

    if ($productId) {
      
        $product = Product::where('shopify_product_id', $productId)->first();

        if ($product) {
            $productVariant = ProductVariant::where('shopify_product_id', $product->shopify_product_id)
                                ->select('color', 'size')
                                ->get();

            $productVariants = $productVariant->toArray();

            CreateApparelMagicProducts::dispatch($product, $productVariants);
        }

        return; 
    }

    $products = Product::whereNotNull('shopify_product_id')->get();

    foreach ($products as $product) {
        $productVariant = ProductVariant::where('shopify_product_id', $product->shopify_product_id)
                            ->select('color', 'size')
                            ->get();

        $productVariants = $productVariant->toArray();

        CreateApparelMagicProducts::dispatch($product, $productVariants);
    }
}

}
