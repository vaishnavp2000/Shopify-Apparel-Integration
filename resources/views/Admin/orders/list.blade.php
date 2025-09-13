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
                              <button class="btn btn-primary btn-icon" id="sync_am_order" data-bs-toggle="modal" data-bs-target="#syncAmOrderModal">
                                <i class="bi bi-plus-circle me-1"></i>Create Am Order
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
                        <th>Am Order ID</th>
                        <th>Shopify Order ID</th>
                        <th>Customer Name</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Amount</th>
                        <th>Fulfillment Status</th>
                        <th>Date</th>
                        <th>Country</th>
                        <th>State</th>
                        <th>Balance</th>
                            
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
 <div class="modal fade" id="syncAmOrderModal" tabindex="-1" aria-labelledby="syncAmOrderModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      
      <div class="modal-header">
        <h5 class="modal-title" id="syncAmOrderModalLabel">Sync AM Order</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <form id="syncAmOrderForm">
          <div class="mb-3">
            <label for="shopify_order_id" class="form-label">Shopify Order ID</label>
            <input type="text" class="form-control" id="shopify_order_id" name="shopify_order_id" placeholder="Enter Shopify Order ID">
            <div class="form-text">Leave blank if you want to sync all orders.</div>
          </div>

          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" value="1" id="sync_all" name="sync_all">
            <label class="form-check-label" for="sync_all">
              Sync all order
            </label>
          </div>
        </form>
      </div>

        <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        
        <button type="button" class="btn btn-primary" id="syncAmProductSubmit">
            <span class="btn-text">Sync</span>
            <span class="spinner-border spinner-border-sm d-none ms-2" role="status" aria-hidden="true"></span>
        </button>
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
    { data: 'am_order_id', name: 'am_order_id' },
    { data: 'shopify_order_id', name: 'shopify_order_id' },
    { data: 'customer_name', name: 'customer_name' },
    { data: 'phone', name: 'phone' },
    { data: 'email', name: 'email' },
    { data: 'amount', name: 'amount' },
    { data: 'fulfillment_status', name: 'fulfillment_status' },
    { data: 'date', name: 'date' },
    { data: 'country', name: 'country' },
    { data: 'state', name: 'state' },
    { data: 'balance', name: 'balance' }
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
$(document).on('click', '#syncAmProductSubmit', function () {
    // console.log("sync logoooooo");
    var btn = $(this);
    var orderId = $('#shopify_order_id').val(); 
    console.log("orderId",orderId);
    var syncAll = $('#sync_all').is(':checked') ? 1 : 0;

    $.ajax({
        url: "{{ route('admin.create-am-orders') }}", 
        type: 'POST',
        data: {
            order_id: orderId,
            sync_all: syncAll,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        beforeSend: function () {
            btn.prop("disabled", true);
            btn.find(".btn-text").text("Syncing...");
            btn.find(".spinner-border").removeClass("d-none");
        },
        success: function (response) {
            $('#syncAmOrderModal').modal('hide');
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: response.message || 'Orders synced successfully.',
            });
        },
        error: function (xhr) {
            $('#syncAmOrderModal').modal('hide');
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: (xhr.responseJSON && xhr.responseJSON.message) 
                    ? xhr.responseJSON.message 
                    : 'Failed to sync orders.',
            });
        },
        complete: function () {
            btn.prop("disabled", false);
            btn.find(".btn-text").text("Sync");
            btn.find(".spinner-border").addClass("d-none");
        }
    });
});


});



</script>

@endsection