<?php

namespace App\Traits\ApparelMagic;

use App\Jobs\ApparelMagic\GetApparelMagicCustomers;
use App\Models\Am_Customer;
use App\Models\Am_Division;
use App\Models\Am_Warehouse;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Models\SizeRange;
use App\Traits\ApiHelper;
use DateTime;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;

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

        if (!empty($sizeRangeName)) {
            $params = [
                'time' => (string) $time,
                'token' => (string) $token,
            ];

            $header = [];
            $header['style_number'] = $product->shopify_handle;
            $header['description'] = $product->description;
            $header['is_product'] = 1;
            $header['is_component'] = 0;
            $header['price'] = $product->price;
            $header['size_range_name'] = $sizeRangeName[1];
            $params['header'] = $header;
            $skus = [];

            foreach ($productVariants as $variant) {
                $color = !empty($variant['color']) ? $variant['color'] : 'MALTESE';
                $size = $variant['size'];

                $skus[] = [
                    'attr_2' => $color,
                    'size' => $size,
                    'cost_offset' => '10',
                    'active' => '1',
                ];
            }
            info("skusss", $skus);
            $params['sku'] = $skus;
            $response = $this->apparelMagicApiPostRequest($url, $params);
            info("amProducts--1" . json_encode($response));


            if (!empty($response['response']) && !isset($response['status'])) {
                $amProducts = $response['response'][0];
                info("amProducts" . json_encode($amProducts));

                if (!empty($amProducts)) {
                    foreach ($amProducts as $item) {
                        $product = Product::where('style_number', $item['style_number'] ?? '')->first();
                        // info("product table Creats starts".json_encode($product));
                        if (!empty($product)) {
                            info("product table Creats starts2" . json_encode($product));

                            $updated = Product::where('style_number', $item['style_number'] ?? '')
                                ->update([
                                    'product_id' => $item['product_id'] ?? null,
                                    'size_range_id' => $item['size_range_id'] ?? null,
                                    'is_product' => $item['is_product'] ?? null,
                                    'is_component' => $item['is_component'] ?? null,
                                    'price' => $item['price'] ?? null,
                                    'description' => $item['description'] ?? null,
                                ]);
                            info("product table ends starts2" . json_encode($updated));


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
        info("response" . json_encode($inventory));
        if (!empty($inventory['response']) && !isset($inventory['status'])) {
            $inventoryItems = $inventory['response'];
            info("inventory response" . json_encode($inventoryItems));
            foreach ($inventoryItems as $variantData) {
              ProductVariant::updateOrCreate(
                    [
                        'style_number'=>$variantData['style_number'] ?? null,
                        'color' => !empty($variantData['attr_2']) ? $variantData['attr_2'] : 'MALTESE',
                        'size' => $variantData['size'] ?? null,
                    ],
                    [
                        'product_id' => $variantData['product_id'] ?? null,
                        'sku_id' => $variantData['sku_id'] ?? null,
                        'sku_concat' => $variantData['sku_concat'] ?? null,
                        'sku_alt' => $variantData['sku_alt'] ?? null,
                        'upc_display' => $variantData['upc_display'] ?? null,
                    ]
                );
                    $sku_id = $variantData['sku_id'] ?? null;
                    $sku_alt = $variantData['sku_alt'] ?? null;
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
        $variant = ProductVariant::where('sku_id', $sku_id)->first();
        $shopify_sku = $variant->shopify_sku;
        $shopifybarcode = $variant->shopify_barcode;

        $params = [
            'time' => (string) $time,
            'token' => (string) $token,
            'sku_id' => (string) $sku_id,
        ];

        $inventories = $this->apparelMagicApiRequest($baseUrl, $params);

        if (!empty($inventories['response']) && !isset($inventory['status'])) {
            foreach ($inventories['response'] as $inventory) {
                $inventoryId = $inventory['sku_id'];
                $params = [
                    'time' => (string) $time,
                    'token' => (string) $token,
                    'sku_id' => $sku_id,
                    'sku_alt' => $shopify_sku,
                    'upc_display' => $shopifybarcode,
                ];
                $response = $this->apparelMagicApiPutRequest($baseUrl . '/' . $inventoryId, $params);
                if (!empty($response['response']) && !isset($response['status'])) {
                    info("inventory put response:" . json_encode($response));
                    $inventoryItems = $response['response'][0];
                    if (!empty($inventoryItems)) {
                        ProductVariant::updateOrCreate(
                            [
                                'sku_id' => $sku_id
                            ],
                            [
                                'sku_alt' => $inventoryItems['sku_alt'] ?? null,
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
                            'name' => $range['name'],
                            'sizes' => json_encode($sizes),
                            'is_product' => (bool) $range['is_product'],
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
            info("shopify_sizes" . json_encode($shopify_sizes));
        }
        $sizeRanges = SizeRange::all()->toArray();

        if (empty($sizeRanges)) {
            $this->apparelSizeRanges();
            $sizeRanges = SizeRange::all()->toArray();
            // dd($sizeRanges);
        }
        info("sizeRanges" . json_encode($sizeRanges));

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
    public function getProductByStyleNumber($styleNumber)
    {
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
                    'value' => $styleNumber,
                    'operator' => '=',
                    'include_type' => 'AND'
                ],
            ]
        ];
        $response = $this->apparelMagicApiRequest($url, $params);
        return $response;

    }
    public function getApparelCustomer($page_size = 100, $startAfter = null, $settings)
    {
        try {
            $settings = Setting::where('type', 'apparelmagic')->where('status', 1)->get();
            $this->apparelUrl = $settings->firstWhere('code', 'apparelmagic_api_endpoint')->value;
            $baseUrl = $this->apparelUrl . '/customers';
            $token = $settings->firstWhere('code', 'apparelmagic_token')->value;
            $time = time();
            $customerCount = 0;
            $params = [
                'time' => (string) $time,
                'token' => (string) $token,
                'pagination' => [
                    'page_size' => $page_size
                ],
            ];
            if ($startAfter) {
                $params['pagination']['last_id'] = $startAfter;
            }
            $customers = $this->apparelMagicApiRequest($baseUrl, $params);
            if (!empty($customers['response']) && !isset($customers['status'])) {
                $fetchedCustomers = $customers['response'];
                info("customers" . json_encode($fetchedCustomers));
                foreach ($fetchedCustomers as $cust) {
                    Am_Customer::updateOrCreate(
                        ['am_customer_id' => $cust['customer_id']],
                        ['name' => $cust['customer_name']]
                    );
                }

            }
            $customerCount += $responseCount = count($fetchedCustomers);
            if ($responseCount == $page_size) {
                $meta = $products['meta']['pagination'] ?? [];
                $startAfter = $meta['last_id'] ?? null;
                GetApparelMagicCustomers::dispatch($page_size, $startAfter, $settings);
            }
            info("customers" . json_encode($customers));
        } catch (Exception $e) {
            Log::error('Exception while fetching customers', ['error' => $e->getMessage()]);
        }
    }
    public function getApparelWarehouses($settings)
    {
        try {
            $this->apparelUrl = $settings->firstWhere('code', 'apparelmagic_api_endpoint')->value;
            $baseUrl = $this->apparelUrl . '/warehouses';
            $token = $settings->firstWhere('code', 'apparelmagic_token')->value;
            $time = time();
            $params = [
                'time' => (string) $time,
                'token' => (string) $token,
            ];
            $warehouses = $this->apparelMagicApiRequest($baseUrl, $params);
            if (!empty($warehouses['response']) && !isset($warehouses['status'])) {
                $fetchedWarehouses = $warehouses['response'];
                Log::info(message: "warehouses" . json_encode($fetchedWarehouses));
                foreach ($fetchedWarehouses as $warehouse) {
                    Am_Warehouse::updateOrCreate(
                        ['warehouse_id' => $warehouse['id']],
                        ['name' => $warehouse['name']]
                    );
                }

            }
            // dd($response);
            Log::info("warehouses" . json_encode($warehouses));
        } catch (Exception $e) {
            Log::error('Exception while fetching warehouses', ['error' => $e->getMessage()]);
        }
    }
    public function getApparelDivision($settings)
    {
        try {
            $this->apparelUrl = $settings->firstWhere('code', 'apparelmagic_api_endpoint')->value;
            $baseUrl = $this->apparelUrl . '/divisions';
            $token = $settings->firstWhere('code', 'apparelmagic_token')->value;
            $time = time();
            $params = [
                'time' => (string) $time,
                'token' => (string) $token,
            ];
            $divisions = $this->apparelMagicApiRequest($baseUrl, $params);
             Log::info("divisions" . json_encode($divisions));
            if (!empty($divisions['response']) && !isset($divisions['status'])) {
                $fetchedDivisions = $divisions['response'];
                Log::info(message: "divisions" . json_encode($fetchedDivisions));
                foreach ($fetchedDivisions as $division) {
                    Am_Division::updateOrCreate(
                        ['division_id' => $division['id']],
                        ['name' => $division['name']]
                    );
                }

            }
            // dd($response);
           
        } catch (Exception $e) {
            Log::error('Exception while fetching divisions', ['error' => $e->getMessage()]);
        }
    }

     public function createApparelmagicOrder($order)
    {
        // info("order".json_encode(explode('T', $order['shopify_created_at'])));
        try {
            $settings = Setting::where('type', 'apparelmagic')->where('status', 1)->get();
            // info("settings".json_encode($settings));
            $this->apparelUrl = $settings->firstWhere('code', 'apparelmagic_api_endpoint')->value;
            $baseUrl = $this->apparelUrl . '/orders';
            $token = $settings->firstWhere('code', 'apparelmagic_token')->value;
            //  info("token".json_encode($token));
            $time = time();

            $division_id =1016;
            $warehouse_id = 1006;
            $customer_id = 1000;

            $header = [];
            $header['customer_id'] = $customer_id;
            $header['division_id'] = $division_id;
            $header['ar_acct'] = '1000';
            $header['warehouse_id'] = $warehouse_id;
            $header['currency_id'] = '1000';

            $datecreated = explode('T', $order->shopify_created_at);
            $formateddate = DateTime::createFromFormat('Y-m-d', $datecreated[0]);
            $datecreated = $formateddate->format('m/d/Y');
            $header['date'] = $datecreated;
            $header['date_start'] = $datecreated;

            $header['source'] = 'Shopify Wholesale';
            $header['notes'] = $order->shopify_shipping_notes;
            $header['amount'] = (float) $order->shopify_shipping_total;
            $header['customer_po'] = (float) $order->shopify_order_id;
            $header['name'] = $order->shopify_customer_firstname . ' ' . $order->shopify_customer_lastname;

            $header['address_1'] = $order->shopify_shipping_address1;
            $header['address_2'] = $order->shopify_shipping_address2 ?? '';
            $header['city'] = $order->shopify_shipping_city;
            $header['postal_code'] = $order->shopify_shipping_zip;
            $header['country'] = $order->shopify_shipping_country;
            $header['state'] = $order->shopify_shipping_provincecode;
            $header['phone'] = $order->shopify_shipping_phone;
            $header['email'] = $order->shopify_email;
            
          $items = [];
        $orderItems = $order->orderProducts; 

        foreach ($orderItems as $orderProduct) {
            info("orderProductquanity".json_encode($orderProduct->shopify_quantity));

            $variant = ProductVariant::where('shopify_sku', $orderProduct->shopify_sku)
                        ->whereNotNull('product_id')
                        ->first();

            if (!$variant) {
                continue; 
            }

            $quantity = $orderProduct->shopify_quantity; 
            $items[] = [
                'sku_id'    => $variant->sku_id,
                'qty' => (string) (($quantity ?? 0) > 0 ? $quantity : 1),
                'unit_price'=> (string) ($orderProduct->shopify_amount ?? 0),
                'amount'    => (string) ($quantity * ($orderProduct->shopify_amount ?? 0)),
            ];
        }


            $params = [
                'time'   => (string) $time,
                'token'  => (string) $token,
                'header' => $header,
                'items'  => $items,
            ];

            $response = $this->apparelMagicApiPostRequest($baseUrl, $params);
            // info("Order-CREATED".json_encode($response));

            if (!empty($response) && !isset($response['status'])) {
                $amOrders = $response['response'];
                // info("am order response".json_encode($amOrders));

                foreach ($amOrders as $order) {
                    $orderDetail = Order::updateOrCreate(
                        ['shopify_order_id' => $order['customer_po']],
                    
                        [
                            'am_order_id'=>$order['order_id']??null,
                            'customer_id'   => $order['customer_id'] ?? null,
                            'division_id'   => $order['division_id'] ?? null,
                            'warehouse_id'  => $order['warehouse_id'] ?? null,
                            'currency_id'   => $order['currency_id'] ?? null,
                            'arr_accnt'      => $order['ar_acct'] ?? null,
                            'date'          => isset($order['date']) ? Carbon::parse($order['date'])->format('Y-m-d') : null,
                            'date_start'    => isset($order['date_start']) ?Carbon::parse($order['date_start'])->format('Y-m-d') : null,
                            'source'        => $order['source'] ?? null,
                            'notes'         => $order['notes'] ?? null,
                            'customer_name'  => $order['name'] ?? null,
                            'customer_po'=> $order['customer_po']??null,
                            'address_1'     => $order['address_1'] ?? null,
                            'address_2'     => $order['address_2'] ?? null,
                            'city'          => $order['city'] ?? null,
                            'postal_code'   => $order['postal_code'] ?? null,
                            'country'       => $order['country'] ?? null,
                            'state'         => $order['state'] ?? null,
                            'phone'         => $order['phone'] ?? null,
                            'email'         => $order['email'] ?? null,
                            'created_at'    => $order['creation_time'] ?? '',
                            'credit_status'=>$order['credit_status']??null,
                            'fulfillment_status'=>$order['fulfillment_status'] ?? null

                        ]
                    );

                    if (!empty($order['order_items']) && is_array($order['order_items'])) {
                        Log::info("Order items");
                        foreach ($order['order_items'] as $item) {
                                $orderDetail->orderProducts()->updateOrCreate(
                                    ['sku_id' => $item['sku_id'],
                                    'shopify_sku'=>$item['sku_alt'],
                                    'am_order_id'=>$item['order_id']
                                    ],
                                    [
                                        'order_id'=>$orderDetail->id,
                                        'am_order_id'=> $item['order_id'] ?? null,
                                        'am_order_item_id'=>$item['id']??null,
                                        'sku_id' => $item['sku_id']??null,
                                        'product_id'   => $item['product_id'] ?? null,
                                        'sku_alt'      => $item['sku_alt'] ?? null,
                                        'upc'          => $item['upc'] ?? null,
                                        'style_number' => $item['style_number'] ?? null,
                                        'description'  => $item['description'] ?? null,
                                        'size'         => $item['size'] ?? null,
                                        'qty'          => $item['qty'] ?? 0,
                                        'qty_picked'=>$item['qty_picked']??0,
                                        'qty_cancelled'=>$item['qty_cxl']??0, 
                                        'qty_shipped'=>$item['qty_shipped']??0,
                                        'unit_price'   => $item['unit_price'] ?? 0,
                                        'amount'       => $item['amount'] ?? 0,
                                        'is_taxable'   => $item['is_taxable'] ?? '0',
                                        'warehouse_id' => $item['warehouse_id'] ?? $order['warehouse_id'] ?? null,
                                    ]
                                );
                            }
                    }
                }
            }
             if (!empty($amOrder) && $creditStatus == 'Pending') {
             }
          



            



        } catch (Exception $e) {
            Log::error('Exception while creating order', ['error' => $e->getMessage()]);
        }
    }
    public function apparelOrderAllocate(){

    }
    public function getApparelOrder($orderId){
        // info("orderid".json_encode($orderId));
        $settings = Setting::where(['type' => 'apparelmagic', 'status' => 1])->get();
        $apparelUrl = $settings->firstWhere('code', 'apparelmagic_api_endpoint')->value;
        $token = $settings->firstWhere('code', 'apparelmagic_token')->value;
        $time = time();
        $url = $apparelUrl . '/orders';
        $params = [
            'time' => (string) $time,
            'token' => (string) $token,
            'parameters' => [
                [
                    'field' => 'customer_po',
                    'value' => $orderId,
                    'operator' => '=',
                    'include_type' => 'AND'
                ],
            ]
        ];
        $response = $this->apparelMagicApiRequest($url, $params);
        info("response".json_encode($response));
        return $response;


    }

}
