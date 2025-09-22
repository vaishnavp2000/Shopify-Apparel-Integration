<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Configuration;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Traits\ApparelMagic\ApparelMagicHelper;
use App\Traits\Shopify\ShopifyHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Yajra\DataTables\DataTables;


class HomeController extends Controller
{
    use ShopifyHelper,ApparelMagicHelper;
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index(Request $request)
    {
        $this->getApparelWareHouseStock();
       $productCount=Product::count();
       $orderCount=Order::count();
       return view('admin.home', [
            'productCount' => $productCount,
            'orderCount' => $orderCount
        ]);
    }
}
