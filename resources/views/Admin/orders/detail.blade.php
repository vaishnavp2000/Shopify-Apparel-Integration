@extends('admin.layouts.app')

@section('page-title', 'Order Detail')

@section('content')
<div class="content">
    <!-- Breadcrumb -->
    <div class="mb-4">
        <nav style="--bs-breadcrumb-divider: '>'" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.dashboard') }}">
                        <i class="bi bi-globe2 small me-2"></i> Dashboard
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Order Detail</li>
            </ol>
        </nav>
    </div>

        <div class="row">
            <div class="col-lg-8 col-md-12">
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="mb-5 d-flex align-items-center justify-content-between">
                            <span>Order No : <a href="#"># {{ $order->id }}</a></span>
                        </div>

                        <div class="row mb-5 g-4">
                            <div class="col-md-3 col-sm-6">
                                <p class="fw-bold">Order Created at</p>
                                {{ $order->created_at->format('d/m/Y \a\t h:i') ??''}}
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <p class="fw-bold">Name</p>
                                {{ $order->customer_name??''}}
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <p class="fw-bold">Email</p>
                                {{ $order->email ??''}}
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <p class="fw-bold">Contact No</p>
                                {{ $order->phone??''}}
                            </div>
                        </div>
                        

                        <div class="row g-4">
                            <div class="col-md-12 col-sm-12">
                                <div class="card">
                                    <div class="card-body d-flex flex-column">
                                        <div class="d-flex justify-content-between">
                                            <h5 class="mb-0">Delivery Address</h5>
                                        </div>
                                        <div></div>
                                        <div>{{ $order->address_1 }},{{$order->address_2}}</div>
                                        <div>{{ $order->city }}, {{  $order->city ?? 'N/A' }},
                                            {{ $order->postal_code }}
                                        </div>
                                        <div>{{  $order->country ?? 'N/A' }}</div>
                                        <div>{{ $order->phone }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-12 mt-4 mt-lg-0">
                <div class="card mb-4">
                    <div class="card-body">
                        <h6 class="card-title mb-4">Price</h6>
                       
                        <div class="row justify-content-center mb-2 fw-bold">
                            <div class="col-6 text-end">Total :{{ $order->shopify_shipping_total }}</div>
                            <div class="col-6"></div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title mb-4">Order</h6>
                        <div class="row justify-content-center mb-3">
                            <div class="col-6 text-end">Order No :</div>
                            <div class="col-6">
                                <a href="#">#{{ $order->id}}</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Items Section -->
            <div class="col-12">
                <div class="card widget mt-4">
                    <h5 class="card-header">Order Items</h5>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-custom mb-0">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Color</th>
                                        <th>Size</th>
                                        <th>Quantity</th>
                                        <th>Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($order->orderProducts as $orderProduct)
                                        <tr>
                                            <td>{!! wordwrap($orderProduct?->sku_alt ?? '-', 15, "<br>\n") !!}</td>
                                            <td>{!! wordwrap($orderProduct?->attr_2?? '-', 15, "<br>\n") !!}</td>
                                            <td>{!! wordwrap($orderProduct?->size?? '-', 15, "<br>\n") !!}</td>
                                          
                                            <td>{{ $orderProduct->qty }}</td>
                                            <td>${{ $orderProduct->unit_price * $orderProduct->qty }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection