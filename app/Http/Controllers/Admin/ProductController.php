<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ApparelMagic\CreateApparelMagicProducts;
use App\Jobs\Shopify\GetShopifyProducts;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Traits\ApparelMagic\ApparelMagicHelper;
use App\Traits\Shopify\ShopifyHelper;
use Yajra\DataTables\Facades\DataTables;


class ProductController extends Controller
{
    use ApparelMagicHelper,ShopifyHelper;
  
    /**
     * Display a listing of the resource.
     */
     public function index(Request $request,Datatables $datatables)
    {
         if ($request->ajax()) {
            $query = Product::select('product_id','style_number','price','description','shopify_handle','image','shopify_product_id','title','total_variants');

            return  datatables()->eloquent($query)
                ->addColumn('image', function (Product $product) {
                    $image = $product->image
                    ? $product->image  
                    : asset('assets/images/no-image.png');
                    return '<img src="' . $image . '" class="rounded" width="40" height="50" alt="Product">';
                })

                ->editColumn('status', function (Product $product) {
                    return $product->status
                        ? '<span class="badge bg-success">Active</span>'
                        : '<span class="badge bg-danger">Inactive</span>';
                })

                

                ->rawColumns(['image'])
                ->make(true);
        }

        return view('Admin.products.list');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
     public function fetchProducts()
    {
    try {
        $settings = Setting::where('type', 'shopify')
            ->where('status', 1)
            ->get();

        $limit = 200;
        $reverse = false;
        $nextPageCursor = null;
        $variantCount = 10;

        GetShopifyProducts::dispatch((int) $limit, $reverse, $variantCount, $nextPageCursor, $settings);

        return response()->json([
            'status' => true,
            'message' => 'Product fetch has been started. You will see updates shortly.'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Failed to start product fetch.',
            'error'   => $e->getMessage()
        ], 500);
    }
}
public function createAmProducts(Request $request)
{
    $productId = $request->product_id;
    $sync_all  = $request->sync_all;

    if ($productId) {
        Artisan::call('app:create-am-products', [
            '--productId' => $productId
        ]);

    }

    // Sync all products
    if ($sync_all == 1) {
        $products = Product::whereNotNull('shopify_product_id')->get();

        foreach ($products as $product) {
            $productVariant = ProductVariant::where('shopify_product_id', $product->shopify_product_id)
                ->select('color', 'size')
                ->get();

            $productVariants = $productVariant->toArray();

            $styleNumber = $product->style_number;
            $response = $this->getProductByStyleNumber($styleNumber);

            if (empty($response['response']) || !is_array($response['response'])) {
                info("Creating apparel product " . $product->shopify_product_id);
                CreateApparelMagicProducts::dispatch($product, $productVariants);
            } else {
                info("Updating apparel product " . $product->shopify_product_id);
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
