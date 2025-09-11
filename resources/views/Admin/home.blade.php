@extends('admin.layouts.app')

@section('page-title', 'Shopify_Apparel')

@section('head')
<link rel="stylesheet" href="{{ url('libs/dataTable/datatables.min.css') }}" type="text/css">
<link rel="stylesheet" href="{{ url('libs/range-slider/css/ion.rangeSlider.min.css') }}" type="text/css">

<style>
    .my-3{
        color:#172f3d !important;
    }
    
</style>
@endsection

@section('content')
    <div class="row row-cols-1 row-cols-md-3 g-4">
        <div class="col-lg-12 col-md-12">
            <div class="card widget">
                <div class="card-header">
                    <h5 class="card-title"></h5>
                </div>
            </div>
            <div class="row g-4">
               
                <div class="col-md-6">
                   
                        <div class="card border-0">
                            <div class="card-body text-center">
                                <div class="display-5">
                                    <i class="bi bi-bag"></i>
                                </div>
                                <h5 class="my-3">Products</h5>
                                <div class="text-muted">{{ $productCount }}</div>
                            </div>
                        </div>
                </div>

                <div class="col-md-6">
                        <div class="card border-0">
                            <div class="card-body text-center">
                                <div class="display-5">
                                    <i class="bi bi-cart"></i>
                                </div>
                                <h5 class="my-3">Orders</h5>
                                <div class="text-muted">{{$orderCount }}</div>
                            </div>
                        </div>
                       
                </div>
            </div>
        </div>
    </div>

@endsection

@section('script')
<script src="{{ url('libs/dataTable/datatables.min.js') }}"></script>
<script src="{{ url('libs/range-slider/js/ion.rangeSlider.min.js') }}"></script>
<script src="{{ url("dist/js/examples/dashboard.js") }}"></script>
<script>

</script>
@endsection