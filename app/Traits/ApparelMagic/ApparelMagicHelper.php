<?php

namespace App\Traits\ApparelMagic;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Models\SizeRange;
use App\Traits\ApiHelper;

trait ApparelMagicHelper
{
     use ApiHelper;
      public function createAmProducts($product, $productVariants)
    {
        // info("productVariant-sizes",$productVariants);
       
        $settings = Setting::where(['type' => 'apparelmagic', 'status' => 1])->get();
        $this->apparelUrl = $settings->firstWhere('code', 'apparelmagic_api_endpoint')->value;
        $url = $this->apparelUrl . '/products';
        $token = $settings->firstWhere('code', 'apparelmagic_token')->value;
        $time = time();
        $sizeRangeName = $this->getSizeRangeByVariant($productVariants);

        if(!empty($sizeRangeName)){
        $params = [
            'time' => (string) $time,
            'token' => (string) $token,
        ];

        $header = [];
        $header['style_number'] =$product->shopify_product_id;
        $header['description'] =$product->description;   
        $header['is_product'] = 1;
        $header['is_component'] = 0;
        $header['price'] =$product->price;  
        $header['size_range_name'] = $sizeRangeName[1];
        $params['header'] = $header;
        $skus = [];
       
        foreach ($productVariants as $variant) {
            $color = !empty($variant['color']) ? $variant['color'] : 'MALTESE';
            $size=$variant['size'];

            $skus[] = [
                'attr_2' => $color,
                'size' => $size,
                'cost_offset' => '10',
                'active' => '1',
            ];
        }
        info("skusss",$skus);
        $params['sku'] = $skus;
        $response = $this->apparelMagicApiPostRequest($url, $params);
        info("amProducts--1".json_encode($response));


        if(!empty($response['response']) && !isset($response['status'])){
        $amProducts = $response['response'][0];
        info("amProducts".json_encode($amProducts));

        if (!empty($amProducts)) {
            foreach ($amProducts as $item) {
                $product = Product::where('style_number', $item['style_number'] ?? '')->first();
                info("product table Creats starts".json_encode($product));
                if (!empty($product)) {
                info("product table Creats starts2".json_encode($product));

                    $updated = Product::where('style_number', $item['style_number'] ?? '')
                        ->update([
                            'product_id' => $item['product_id'] ?? null,
                            'size_range_id' => $item['size_range_id'] ?? null,
                            'is_product' => $item['is_product'] ?? null,
                            'is_component' => $item['is_component'] ?? null,
                            'price' => $item['price'] ?? null,
                            'description' => $item['description'] ?? null,
                        ]);
                    info("product table ends starts2".json_encode($updated));


                    $this->getApparelVariants($item);
                }
            }
        }
        }
      }
    }
     public function getApparelVariants($item)
    {
        // dd($item);
        $settings = Setting::where(['type' => 'apparelmagic', 'status' => 1])->get();
        $this->apparelUrl = $settings->firstWhere('code', 'apparelmagic_api_endpoint')->value;
        $token = $settings->firstWhere('code', 'apparelmagic_token')->value;
        $time = time();
        $inventoryUrl = $this->apparelUrl . '/inventory/';
        $inventoryParams = [
            'time' => $time,
            'token' => $token,
            'parameters' => [
                [
                    'field' => 'product_id',
                    'value' => $item['product_id'],
                    'operator' => '=',
                    'include_type' => 'AND'
                ],
            ]
        ];
        $inventory = $this->apparelMagicApiRequest($inventoryUrl, $inventoryParams);
        info("response".json_encode($inventory));
        if (!empty($inventory['response']) && !isset($inventory['status'])) { 
        $inventoryItems = $inventory['response'];
        info("inventory response".json_encode($inventoryItems));
        foreach ($inventoryItems as $variantData) {
           ProductVariant::updateOrCreate(
                [
                    'style_number'=>$variantData['style_number'] ?? null,
                    'color' => !empty($variantData['attr_2']) ? $variantData['attr_2'] : 'MALTESE',
                    'size'  => $variantData['size'] ?? null,
                ],
                [
                    'product_id' => $variantData['product_id'] ?? null,
                    'sku_id'     => $variantData['sku_id'] ?? null,
                    'sku_concat' => $variantData['sku_concat'] ?? null,
                    'sku_alt' => $variantData['sku_alt'] ?? null,
                    'upc_display' => $variantData['upc_display'] ?? null,
                ]
                );
            // info(json_encode($productVariant));
            $sku_id = $variantData['sku_id'] ?? null;
            $sku_alt     = $variantData['sku_alt'] ?? null;
            $upc_display = $variantData['upc_display'] ?? null;
            if ($sku_id && (empty($sku_alt) || empty($upc_display))) {
               $this->fetchApparelmagicInventory($settings, $sku_id);
            }
        }
        }
      
    }
     public function fetchApparelmagicInventory($settings, $sku_id)
    {
        $settings = Setting::where('type', 'apparelmagic')->where('status', 1)->get();
        // $productId = Product::where('am_product_id')->first();
        // $productId = '2103';
        // $sku_id = ProductVariant::where('sku_id', $inventoryId)->first();
        $this->apparelUrl = $settings->firstWhere('code', 'apparelmagic_api_endpoint')->value;
        $baseUrl = $this->apparelUrl . '/inventory';
        $token = $settings->firstWhere('code', 'apparelmagic_token')->value;
        $time = time();
        $variant=ProductVariant::where('sku_id',$sku_id)->first();
        $shopify_sku= $variant->shopify_sku;
        $shopifybarcode=$variant->shopify_barcode;

        $params = [
            'time' => (string) $time,
            'token' => (string) $token,
            'sku_id' => (string) $sku_id,
        ];

        $inventories = $this->apparelMagicApiRequest($baseUrl, $params);

        if (!empty($inventories['response'])&&!isset($inventory['status'])) {
            foreach ($inventories['response'] as $inventory) {
                $inventoryId = $inventory['sku_id'];
                $params = [
                    'time' => (string) $time,
                    'token' => (string) $token,
                    'sku_id' => $sku_id,
                    'sku_alt' =>  $shopify_sku,
                    'upc_display' => $shopifybarcode,
                ];
                $response = $this->apparelMagicApiPutRequest($baseUrl . '/' . $inventoryId, $params);
                if(!empty($response['response'])&&!isset($response['status'])){
                info("inventory put response:".json_encode($response));
                    $inventoryItems=$response['response'][0];
                    if(!empty($inventoryItems)){
                    ProductVariant::updateOrCreate(
                        [
                            'sku_id' => $sku_id 
                        ],
                        [
                            'sku_alt'     => $inventoryItems['sku_alt'] ?? null,
                            'upc_display' => $inventoryItems['upc_display'] ?? null
                        ]
                    );
                }
            }


            }
        }
    }
     public function apparelSizeRanges()
    {
        $settings = Setting::where(['type' => 'apparelmagic', 'status' => 1])->get();
        $this->apparelUrl = $settings->firstWhere('code', 'apparelmagic_api_endpoint')->value;
        $url = $this->apparelUrl . '/size_ranges';
        $token = $settings->firstWhere('code', 'apparelmagic_token')->value;
        $time = time();
        $params = [
            'time' => (string) $time,
            'token' => (string) $token,
        ];

        $response = $this->apparelMagicApiRequest($url, $params);

        if (!empty($response)) {
            $sizeRanges = $response['response'];

            foreach ($sizeRanges as $range) {
                $sizes = [];

                for ($i = 1; $i <= 30; $i++) {
                    $key = 'size_' . str_pad($i, 2, '0', STR_PAD_LEFT);
                    if (!empty($range[$key])) {
                        $sizes[] = $range[$key];
                    }
                }

                if (!empty($sizes)) {
                    SizeRange::updateOrCreate(
                     ['size_range_id' => $range['id']],  
                        [
                            'name'         => $range['name'],
                            'sizes'        => json_encode($sizes),
                            'is_product'   => (bool) $range['is_product'],
                            'is_component' => (bool) $range['is_component'],
                        ]
                    );
                }

            }

            return $sizeRanges;
        }

        return [];
    }
    public function getSizeRangeByVariant($productVariants)
    {
        info('productVariants ' . json_encode($productVariants));
        $shopify_sizes = [];
        foreach ($productVariants as $variant) {
            $shopify_sizes[] = $variant['size'];
            info("shopify_sizes".json_encode($shopify_sizes));
        }
        $sizeRanges = SizeRange::all()->toArray();

        if (empty($sizeRanges)) {
            $this->apparelSizeRanges(); 
            $sizeRanges = SizeRange::all()->toArray();
            // dd($sizeRanges);
        }
        info("sizeRanges".json_encode($sizeRanges));

        $matchingIds = collect($sizeRanges)
            ->filter(function ($item) use ($shopify_sizes) {
                $sizes = json_decode($item['sizes'], true);
                return collect($shopify_sizes)->every(fn($size) => in_array($size, $sizes));
            })
            ->pluck('name')
            ->toArray();
            info("Matching IDs: " . json_encode($matchingIds));
        return $matchingIds;
    }
       public function getProductByProductId($styleNumber){
         $settings = Setting::where(['type' => 'apparelmagic', 'status' => 1])->get();
         $apparelUrl = $settings->firstWhere('code', 'apparelmagic_api_endpoint')->value;
         $token = $settings->firstWhere('code', 'apparelmagic_token')->value;
         $time = time();
         $url = $apparelUrl . '/products';
           $params = [
            'time' => (string) $time,
            'token' => (string) $token,
              'parameters' => [
                    [
                        'field' => 'style_number',
                        'value' =>$styleNumber,
                        'operator' => '=',
                        'include_type' => 'AND'
                    ],
                ]
                ];
            $response=$this->apparelMagicApiRequest($url,$params);
            // info("response".json_encode($response));
            return $response;

    }

}
