<?php

namespace App\Traits\Shopify;

use App\Jobs\Shopify\GetShopifyProducts;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Traits\ApiHelper;
use Exception;
use Illuminate\Support\Facades\Log;

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
                        'shopify_handle' => $product['node']['handle'] ?? null,
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
    public function fetchOrders($limit, $reverse, $nextPageCursor, $settings){
         try {
            $queryString = 'query orders($limit: Int, $reverse:Boolean, $nextPageCursor: String) {
            orders(first: $limit, reverse:$reverse, after:$nextPageCursor) {
                edges {
                    node {
                        id
                        email
                        name
                        displayFulfillmentStatus
                        createdAt
                        updatedAt
                        closedAt
                        note
                        totalPriceSet {
                            shopMoney {
                                amount
                            }
                        }
                        fulfillmentOrders(first:5) {
                            edges {
                                cursor
                                node {
                                    id
                                    status
                                    lineItems (first:10) {
                                        edges {
                                            node {
                                                id
                                                lineItem {
                                                    id
                                                    sku
                                                    title
                                                    variant {
                                                        id
                                                        title
                                                    }
                                                    originalTotalSet {
                                                        shopMoney {
                                                            amount
                                                            currencyCode
                                                        }
                                                    }
                                                }
                                                totalQuantity
                                                remainingQuantity
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        totalShippingPriceSet {
                            shopMoney {
                                amount
                                currencyCode
                            }
                        }
                        totalTaxSet {
                            shopMoney {
                                amount
                                currencyCode
                            }
                        }
                        totalDiscountsSet {
                            shopMoney {
                                amount
                                currencyCode
                            }
                        }
                        subtotalPriceSet {
                            shopMoney {
                                amount
                                currencyCode
                            }
                        }
                        totalPriceSet {
                            shopMoney {
                                amount
                                currencyCode
                            }
                        }
                        customer {
                            id
                            firstName
                            lastName
                        }
                        billingAddress {
                            id
                            name
                            phone
                            address1
                            address2
                            company
                            zip
                            city
                            country
                        }
                        shippingAddress {
                            id
                            name
                            phone
                            address1
                            address2
                            company
                            zip
                            city
                            country
                            provinceCode
                        }
                        shippingLine {
                            id
                            carrierIdentifier
                            code
                            source
                            title
                        }
                    }
                }
                pageInfo {
                    hasNextPage
                    hasPreviousPage
                    startCursor
                    endCursor
                }
            }
        }';

            $variables = ['limit' => $limit, 'reverse' => $reverse, 'nextPageCursor' => $nextPageCursor];
            $response = $this->getHttp($queryString, $variables);
            info("response".json_encode($response));
             
                foreach ($response['data']['orders']['edges'] as $edge) {
                    // Log::info("edge".json_encode($edge));
                    $shopifyOrder = $edge['node'];
                    $this->storeShopifyOrder($shopifyOrder);

                    }

               }catch (Exception $e) {
            Log::error("Failed to store Shopify order", ['error' => $e->getMessage()]);
        }
    }
    public function storeShopifyOrder($shopifyOrder)
    {
    //    info("customer_id".$shopifyOrder['customer']['id']); 
        try {
            $order = Order::updateOrCreate(
                [
                  'shopify_order_id' => str_replace('gid://shopify/Order/', '', $shopifyOrder['id'])],
                [
                    'shopify_email' => $shopifyOrder['email'] ?? null,
                    'shopify_order_name' => $shopifyOrder['name'] ?? null,
                    'shopify_shipping_notes' => $shopifyOrder['note'] ?? null,
                    'shopify_fulfillment_status' => $shopifyOrder['displayFulfillmentStatus'] ?? null,

                    'shopify_customer_id' => str_replace('gid://shopify/Customer/', '', $shopifyOrder['customer']['id']) ?? null,
                    'shopify_customer_firstname' => $shopifyOrder['customer']['firstName'] ?? null,
                    'shopify_customer_lastname' => $shopifyOrder['customer']['lastName'] ?? null,

                    'shopify_shipping_name' => $shopifyOrder['shippingAddress']['name'] ?? null,
                    'shopify_shipping_phone' => $shopifyOrder['shippingAddress']['phone'] ?? null,
                    'shopify_shipping_address1' => $shopifyOrder['shippingAddress']['address1'] ?? null,
                    'shopify_shipping_address2' => $shopifyOrder['shippingAddress']['address2'] ?? null,
                    'shopify_shipping_zip' => $shopifyOrder['shippingAddress']['zip'] ?? null,
                    'shopify_shipping_city' => $shopifyOrder['shippingAddress']['city'] ?? null,
                    'shopify_shipping_provincecode'=>$shopifyOrder['shippingAddress']['provinceCode']??null,
                    'shopify_shipping_country' => $shopifyOrder['shippingAddress']['country'] ?? null,
                    'shopify_shipping_total' => $shopifyOrder['totalPriceSet']['shopMoney']['amount'] ?? '',
                    'shopify_created_at' => $shopifyOrder['createdAt'] ?? null,
                    
                ]
            );

            foreach ($shopifyOrder['fulfillmentOrders']['edges'] as $fulfillmentEdge) {
                $fulfillmentNode = $fulfillmentEdge['node'];

                foreach ($fulfillmentNode['lineItems']['edges'] as $lineItemEdge) {
                    $lineItemNode = $lineItemEdge['node'];
                    $lineItem = $lineItemNode['lineItem'];

                    OrderProduct::updateOrCreate(
                        [
                             'shopify_order_id' => str_replace('gid://shopify/Order/', '', $shopifyOrder['id']),
                             'shopify_line_item_id' =>str_replace('gid://shopify/LineItem/', '',$lineItem['id']),
                        ],
                        [
                            'shopify_order_id' => str_replace('gid://shopify/Order/', '', $shopifyOrder['id']),
                            'shopify_title' => $lineItem['title'] ?? null,
                            'shopify_order_name'=>$shopifyOrder['name'],
                            'shopify_sku' => $lineItem['sku'] ?? null,
                            'shopify_quantity' => $lineItemNode['totalQuantity'] ?? 0,
                            'shopify_current_quantity' => $lineItemNode['remainingQuantity'] ?? 0,
                            'shopify_variant_id' => str_replace('gid://shopify/ProductVariant/', '',$lineItem['variant']['id'])??null,
                            'shopify_variant_title' => $lineItem['variant']['title'] ?? null,
                            'shopify_fulfillment_order_id' =>str_replace('gid://shopify/FulfillmentOrder/', '',$fulfillmentNode['id']) ?? null,
                        ]
                    );


                }
            }
        } catch (Exception $e) {
            Log::error("Failed to store Shopify order", ['error' => $e->getMessage()]);
        }
    }

}
