<?php

namespace App\Traits\Shopify;

use App\Jobs\Shopify\GetShopifyProducts;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Traits\ApiHelper;

trait ShopifyHelper
{
    use ApiHelper;
     public function fetchProducts($limit, $reverse, $variantCount, $nextPageCursor, $settings)
    {
        $location = $settings->where('code', 'shopify_location')->first()->value ?? null;

        $queryString = '
            query getProducts($limit: Int, $reverse: Boolean, $nextPageCursor: String,$location: ID!) {
            products(first: $limit, reverse: $reverse, after: $nextPageCursor) {
                edges {
                node {
                    id
                    title
                    description
                    handle
                    totalVariants
                    priceRange {
                        minVariantPrice {
                        amount
                        currencyCode
                        }
                        maxVariantPrice {
                        amount
                        currencyCode
                        }
                    }
                    featuredImage{
                        transformedSrc(maxWidth:300, maxHeight:300)
                    }
                        
                    variants(first: 6) {
                    edges {
                        node {
                        id
                        title
                        sku
                        price
                        barcode
                        selectedOptions {
                            name
                            value
                        }
                        inventoryItem {
                                id
                                sku
                                inventoryLevel(locationId:$location) {
                                    id
                                    quantities(names: ["available", "incoming"]) {
                                    name
                                    quantity
                                    }
                                                        
                                }
                            }
                        }
                    }
                    }
                }
                }
                pageInfo 
                {
                    hasNextPage
                    hasPreviousPage
                    startCursor
                    endCursor
                }
            }
            }';

        $variables = ['limit' => $limit, 'reverse' => $reverse, 'nextPageCursor' => $nextPageCursor, 'location' => $location];
        $shopifyresponse = $this->getHttp($queryString, $variables);
        info("Shopify response" . json_encode($shopifyresponse));
        if (!empty($shopifyresponse)) {
            $shopifyProducts = $shopifyresponse['data']['products']['edges'];
            foreach ($shopifyProducts as $product) {
                if($product['node']['id']=='gid://shopify/Product/7240295776433'){
                 info("node id" . json_encode($product));   
                }
                $products = Product::updateOrCreate(
                    [
                        'shopify_product_id' => str_replace('gid://shopify/Product/', '', $product['node']['id'])
                    ],
                    [
                        'total_variants'=> $product['node']['totalVariants'] ?? null,
                        'title'=> $product['node']['title'] ?? null,
                        'description' => $product['node']['description'] ?? null,
                        'style_number' => $product['node']['handle'] ?? null,
                        'price' => $product['node']['priceRange']['minVariantPrice']['amount']  ?? null,
                        'image' => $product['node']['featuredImage']['transformedSrc'] ?? null,
                    ]
                );

                if ($product['node']['totalVariants']) {
                    $variantsCollection = [];
                    foreach ($product['node']['variants']['edges'] as $productVariant) {
                        $size = '';
                        $color = '';
                        foreach ($productVariant['node']['selectedOptions'] as $option) {
                            if ($option['name'] === 'Size') {
                                $size = $option['value'];
                            }
                            if ($option['name'] === 'Color') {
                                $color = $option['value'];
                            }
                        }
                        $productVariants = ProductVariant::updateOrCreate(
                            [
                                'shopify_product_id' => str_replace('gid://shopify/Product/', '', $product['node']['id']),
                                'shopify_variant_id' => str_replace('gid://shopify/ProductVariant/', '', $productVariant['node']['id']),

                            ],
                        [
                                'style_number'=>$products->style_number??null,
                                'shopify_sku' => $productVariant['node']['sku'] ?? null,
                                'shopify_barcode' => $productVariant['node']['barcode'] ?? null,
                                'color' => $color ?: 'MALTESE',
                                'size' => $size ?? null,
                                'price' => $productVariant['node']['price'] ?? null
                            ]

                        );
                        $variantsCollection[] = $productVariants;
                    }
                }
               
            }
            $pageInfo =  $shopifyresponse['data']['products']['pageInfo'];
            $nextPageCursor = $pageInfo['endCursor'];
            if ($pageInfo['hasNextPage'] == true) {
                // info("has next page");
                GetShopifyProducts::dispatch((int) $limit, $reverse, $variantCount, $nextPageCursor, $settings);
            } else {
                info("shopify product fetch completed");
            }
        }
    }

}
