@extends('admin.layouts.app')
@section('page-title','Product')
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
                <li class="breadcrumb-item active" aria-current="page">StockReports</li>
            </ol>
        </nav>
    </div>

    <div class="content">
        <div class="">
            <div class="card">
                <div class="card-body">
                    <div class="d-md-flex gap-4 align-items-center">
                        <div class="d-none d-md-flex">All StockReports</div>
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
                            <button class="btn btn-primary btn-icon" id="fetch_stockreports_btn">
                                <i class="bi bi-download me-1"></i> Stock Report
                            </button>
                        </div>
                      

                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-custom table-lg mb-0" id="stockreports-tb">
                   <thead>
                        <tr>

                            <th>Am Sku ID</th>
                            <th>Shopify Sku ID</th>
                            <th>ProductName</th>
                            <th>Shopify_available_qty</th>
                            <th>Am_available_qty</th>
                            <th>Shopify_barcode</th>
                            <th>Upc_display</th>
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
var $stockreports= $('#stockreports-tb').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
        url: '{{ route('admin.inventory-report.index') }}',
        data: function (d) {
        }
    },
    columns: [
        { data: 'am_sku_id', name: 'am_sku_id' },
        { data: 'shopify_sku_id', name: 'shopify_sku_id' },
        { data: 'produt_name', name: 'produt_name' },
         { data: 'shopify_available_qty', name: 'shopify_available_qty' },
        { data: 'am_available_qty', name: 'am_available_qty' },
        { data: 'shopify_barcode', name: 'shopify_barcode' },
        { data: 'upc_display', name: 'upc_display' },
    ],

    columnDefs: [{
        defaultContent: '--',
        targets: "_all"
    }]
});

$("#stockreports-tb_filter, #stockreports-tb_length").hide();

$('#sort').on('change', function () {
    var $column = $(this).find(':selected').data('column');
    var $sort = $(this).find(':selected').data('sort');
    $stockreports.order([$column, $sort]).draw();
});

$('#pageLength').on('change', function () {
        $stockreports.page.len($(this).val()).draw();
    });

    $('#pageLength').val($stockreports.page.len());

    $(document).on("keyup", ".searchInput", function () {
        $stockreports.search($(this).val()).draw();
    });
});
$('#fetch_stockreports_btn').on('click', function () {
    let button = $(this);
    button.prop('disabled', true).text('Fetching...');

    $.ajax({
        url: "{{ route('admin.inventory-report-export') }}",
        method: 'GET',
        xhrFields: {
            responseType: 'blob' 
        },
        success: function (data, status, xhr) {
            let filename = "inventory_report.xlsx";

            // Try to get filename from Content-Disposition header
            let disposition = xhr.getResponseHeader('Content-Disposition');
            if (disposition && disposition.indexOf('attachment') !== -1) {
                let matches = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/.exec(disposition);
                if (matches != null && matches[1]) {
                    filename = matches[1].replace(/['"]/g, '');
                }
            }

            // Create blob and trigger download
            let blob = new Blob([data], { type: xhr.getResponseHeader('Content-Type') });
            let url = window.URL.createObjectURL(blob);
            let a = document.createElement('a');
            a.href = url;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            a.remove();
            window.URL.revokeObjectURL(url);
        },
        error: function () {
            alert('Error fetching stock report');
        },
        complete: function () {
            button.prop('disabled', false).html('<i class="bi bi-download me-1"></i> Stock Report');
        }
    });
});

</script>

@endsection