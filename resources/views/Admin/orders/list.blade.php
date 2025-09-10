@extends('admin.layouts.app')
@section('page-title','Orders')
@section('head')
<link rel="stylesheet" href="{{ url('libs/dataTable/datatables.min.css') }}" type="text/css">
<link rel="stylesheet" href="{{ url('libs/range-slider/css/ion.rangeSlider.min.css') }}" type="text/css">
<link rel="stylesheet" href="{{ url("libs/toastr.css") }}" />
@endsection
@section('content')
<div class="content ">
    <div class="mb-4">
        <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.dashboard') }}">
                        <i class="bi bi-globe2 small me-2"></i> Dashboard
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Orders</li>
            </ol>
        </nav>
    </div>

    <div class="content">
        <div class="">
            <div class="card">
                <div class="card-body">
                    <div class="d-md-flex gap-4 align-items-center">
                        <div class="d-none d-md-flex">All Orders</div>
                        <div class="d-md-flex gap-4 align-items-center">
                            <form class="mb-3 mb-md-0">
                                <div class="row g-3">
                                    <div class="col-md-7">
                                        <select class="form-select" id="sort">
                                            <option>Sort by</option>
                                            <option data-sort="asc" data-column="1" value="">Name A-z</option>
                                            <option data-sort="desc" data-column="1" value=""> Name Z-a
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-md-5">
                                        <select class="form-select" id="pageLength">
                                            <option value="10">10</option>
                                            <option value="20">20</option>
                                            <option value="30">30</option>
                                            <option value="40">40</option>
                                            <option value="50">50</option>
                                        </select>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="dropdown ms-auto">
                            <button class="btn btn-primary btn-icon" id="fetch_orders_btn">
                                <i class="bi bi-arrow-repeat me-1"></i> Fetch Shopify Orders
                            </button>
                            </a>
                        </div>

                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-custom table-lg mb-0" id="ordertb">
                   <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Shopify Order ID</th>
                            <th>Product ID</th>
                            <th>Style Number</th>
                            <th>Description</th>
                            <th>Amount</th>
                            
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection
@section('script')
<script src="{{ url('libs/dataTable/datatables.min.js') }}"></script>
<script src="{{ url('libs/range-slider/js/ion.rangeSlider.min.js') }}"></script>
<script>
$(document).ready(function () {
var $column = $('#sort').find(':selected').data('column');
var $sort = $('#sort').find(':selected').data('sort');
var $ordertable = $('#ordertb').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
        url: '',
        data: function (d) {
            
        }
    },
    columns: [
        { data: 'order_id', name: 'order_id' },
        { data: 'shopify_order_id', name: 'shopify_order_id' },
        { data: 'product_id', name: 'product_id' },
        { data: 'style_number', name: 'style_number' },
        { data: 'description', name: 'description' },
        { data: 'amount', name: 'amount' },
    ],

    columnDefs: [{
        defaultContent: '--',
        targets: "_all"
    }]
});

$("#ordertb_filter, #ordertb_length").hide();

$('#sort').on('change', function () {
    var $column = $(this).find(':selected').data('column');
    var $sort = $(this).find(':selected').data('sort');
    $ordertable.order([$column, $sort]).draw();
});

$('#pageLength').on('change', function () {
        $ordertable.page.len($(this).val()).draw();
    });

    $('#pageLength').val($ordertable.page.len());

    $(document).on("keyup", ".searchInput", function () {
        $ordertable.search($(this).val()).draw();
    });
    $(document).on('click', '#fetch_orders_btn', function () {
    var btn = $(this);

    $.ajax({
        url: "{{ route('admin.fetch-orders') }}", 
        type: "POST",
        data: {
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        beforeSend: function () {
            btn.prop("disabled", true);
            btn.html(`<span class="spinner-border spinner-border-sm me-2"></span> Fetching...`);
        },
        success: function (response) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: response.message || 'Shopify Orders are being fetched.',
            });
            btn.prop("disabled", false).html(`<i class="bi bi-arrow-repeat me-1"></i> Fetch Shopify Orders`);
        },
        error: function (xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: (xhr.responseJSON && xhr.responseJSON.message) 
                    ? xhr.responseJSON.message 
                    : 'Failed to fetch orders.',
            });
            btn.prop("disabled", false).html(`<i class="bi bi-arrow-repeat me-1"></i> Fetch Shopify Orders`);
        }
    });
});

});



</script>

@endsection