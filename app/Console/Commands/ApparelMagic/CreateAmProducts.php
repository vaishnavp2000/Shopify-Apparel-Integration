<?php

namespace App\Console\Commands\ApparelMagic;

use App\Jobs\ApparelMagic\CreateApparelMagicProducts;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
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
        // Process single product
        $product = Product::where('shopify_product_id', $productId)->first();

        if (!$product) {
            $this->error("Product not found for Shopify ID: {$productId}");
            return; 
        }

        $productVariant = ProductVariant::where('shopify_product_id', $product->shopify_product_id)
                            ->select('color', 'size')
                            ->get();
        $productVariants = $productVariant->toArray();
         $styleNumber = $product->shopify_handle;
        $response = $this->getProductByStyleNumber($styleNumber);

        if (empty($response['response'])) {
            $this->info("create option product id");
            CreateApparelMagicProducts::dispatch($product, $productVariants);
        } else {
            $this->info("update option product id");
            $item = $response['response'][0];
            info("item".json_encode($item));
            Product::where('style_number', $item['style_number'])
                ->update([
                    'product_id' => $item['product_id'] ?? null,
                    'size_range_id' => $item['size_range_id'] ?? null,
                    'is_product' => $item['is_product'] ?? null,
                    'is_component' => $item['is_component'] ?? null,
                    'price' => $item['price'] ?? null,
                    'style_number'=>$item['style_number']??null,
                    'description' => $item['description'] ?? null,
                ]);
            $this->getApparelVariants($item);
        }

        $createdCount = Product::whereNotNull('product_id')->count();
        $this->info("Total ApparelMagic products created: {$createdCount}");

    } else {
        $products = Product::whereNotNull('shopify_product_id')->get();
        
        foreach ($products as $product) {
            $productVariant = ProductVariant::where('shopify_product_id', $product->shopify_product_id)
                                ->select('color', 'size')
                                ->get();
            $productVariants = $productVariant->toArray();
            $styleNumber = $product->style_number;
            $response = $this->getProductByStyleNumber($styleNumber);

            if (empty($response['response'])) {
                $this->info("create apparel product " . $product->shopify_product_id);                
                CreateApparelMagicProducts::dispatch($product, $productVariants);
            } else {
                $this->info("update apparel product");
                $item = $response['response'][0];
                Product::where('style_number', $item['style_number'])
                    ->update([
                        'product_id' => $item['product_id'] ?? null,
                        'size_range_id' => $item['size_range_id'] ?? null,
                        'is_product' => $item['is_product'] ?? null,
                        'is_component' => $item['is_component'] ?? null,
                        'price' => $item['price'] ?? null,
                        'description' => $item['description'] ?? null,
                    ]);
                $this->getApparelVariants($item);
            }
        }
    }
}


}
