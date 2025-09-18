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
                            <span>Order No : <a href="#">{{ $order->shopify_order_name }}</a></span>
                          @php
                            $statusText = '';
                            $statusClass = '';

                            if($order->is_cancelled) {
                                $statusText = 'Cancelled';
                                $statusClass = 'bg-danger';
                            } elseif($order->is_refund) {
                                $statusText = 'Refunded';
                                $statusClass = 'bg-warning';
                            } elseif($order->allocated == 0) {
                                $statusText = 'Pending Allocation';
                                $statusClass = 'bg-secondary';
                            } elseif($order->allocated == 1 && empty($order->pick_ticket_id)) {
                                $statusText = 'Allocated';
                                $statusClass = 'bg-info';
                            } elseif(!empty($order->pick_ticket_id) && empty($order->shipment_id)) {
                                $statusText = 'Pick Ticket Generated';
                                $statusClass = 'bg-primary';
                            } elseif(!empty($order->shipment_id) && $order->fulfillment_status != 'fulfilled') {
                                $statusText = 'Shipped';
                                $statusClass = 'bg-warning';
                            } elseif($order->fulfillment_status == 'fulfilled') {
                                $statusText = 'Fulfilled';
                                $statusClass = 'bg-success';
                            } else {
                                $statusText = 'Unknown';
                                $statusClass = 'bg-dark';
                            }
                        @endphp

                        <span class="badge {{ $statusClass }}">{{ $statusText }}</span>

                        </div>

                        <div class="row mb-5 g-4">
                            <div class="col-md-3 col-sm-6">
                                <p class="fw-bold">Order Created at</p>
                                {{ $order->created_at->format('d/m/Y \a\t h:i') ?? ''}}
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <p class="fw-bold">Name</p>
                                {{ $order->customer_name ?? ''}}
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <p class="fw-bold">Email</p>
                                {{ $order->email ?? ''}}
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <p class="fw-bold">Contact No</p>
                                {{ $order->phone ?? ''}}
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

                            <!-- Outlined box for the total -->
                            <div class="card border p-3">
                                <div class="row justify-content-between fw-bold">
                                    <div class="col text-start">Grand Total:</div>
                                    <div class="col text-end">${{ $order->shopify_shipping_total }}</div>
                                </div>
                            </div>
                        </div>
                    </div>


                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title mb-4">Payment History</h6>

                        @php
                            $payments = \App\Models\Payment::where('am_order_id', $order->am_order_id)->get();
                        @endphp

                        @forelse($payments as $payment)
                            <div class="card mb-3">
                                <div class="card-body">
                                    <div class="row mb-2">
                                        <div class="col-6 text-end fw-bold">Invoice No :</div>
                                        <div class="col-6 text-start">
                                            <a href="#">#{{ $order->am_invoice_id ?? 'N/A' }}</a>
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-6 text-end fw-bold">Payment Id :</div>
                                        <div class="col-6 text-start">{{ $payment->payment_id ?? 'N/A' }}</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-6 text-end fw-bold">Payment Type :</div>
                                        <div class="col-6 text-start">{{ $payment->payment_type ?? 'N/A' }}</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-6 text-end fw-bold">Date :</div>
                                        <div class="col-6 text-start">{{ $payment->date ?? 'N/A' }}</div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="row">
                                <div class="col-12 text-center text-muted">No payments found.</div>
                            </div>
                        @endforelse


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
                                            <td>{!! wordwrap($orderProduct?->attr_2 ?? '-', 15, "<br>\n") !!}</td>
                                            <td>{!! wordwrap($orderProduct?->size ?? '-', 15, "<br>\n") !!}</td>

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
            <div class="col-12 mt-3 text-end">
                <button class="btn btn-danger returnOrderBtn" data-bs-toggle="modal" data-bs-target="#returnOrderModal">
                    Return Order
                </button>
                <button type="button" class="btn btn-warning create-credit-memo" data-order-id="{{ $order->id }}">Create
                    Credit Memo
                </button>
                @if($order->is_refund == 0 && !empty($order->payment_id))
                    <button type="button" class="btn btn-success refund-order-btn" data-order-id="{{ $order->id }}">
                        Refund Order
                    </button>
                @endif
            </div>
        </div>

    </div>


    <!-- Return modal -->
    <div class="modal fade" id="returnOrderModal" tabindex="-1" aria-labelledby="returnOrderModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="returnOrderForm" method="POST">
                @csrf
                <input type="hidden" name="order_id" id="orderId" value="{{ $order->id }}">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="returnOrderModalLabel">Return Order</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="returnReason" class="form-label">Reason for Return</label>
                            <textarea name="return_reason" id="returnReason" class="form-control" rows="4"
                                required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-danger submitReturnBtn">Submit Return</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
@section('script')
    <script>
        $(document).ready(function () {

            $('.submitReturnBtn').click(function () {
                let orderId = $('#orderId').val();
                let reason = $('#returnReason').val().trim();

                if (reason === '') {
                    alert('Please enter a reason.');
                    return;
                }

                $.ajax({
                    url: "{{ route('admin.return-order') }}",
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        order_id: orderId,
                        reason: reason
                    },
                    success: function (response) {
                        $('#returnOrderModal').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message || 'Return request submitted successfully.',
                        });

                    },
                    error: function (xhr, status, error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'error!',
                            text: response.message || 'Failed to submit return request.',
                        });

                    }
                });
            });
        });
        $(document).on('click', '.create-credit-memo', function () {
            let orderId = $(this).data('order-id');
            let btn = $(this);

            $.ajax({
                url: "{{ route('admin.create-credit-memo')}}",
                method: "POST",
                data: {
                    order_id: orderId,
                    _token: "{{ csrf_token() }}"
                },
                beforeSend: function () {
                    btn.prop('disabled', true).text('Creating...');
                },
                success: function (response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message || 'Credit Memo Created',
                    });

                },
                error: function (xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'error!',
                        text: response.message || 'Failed to create Credit Memo.',
                    });
                },
                complete: function () {
                    btn.prop('disabled', false).text('Create Credit Memo');
                }
            });
        });
        $(document).on('click', '.refund-order-btn', function () {
            let orderId = $(this).data('order-id');
            let btn = $(this);

            $.ajax({
                url: "{{ route('admin.create-refund') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    order_id: orderId
                },
                beforeSend: function () {
                    btn.prop('disabled', true).text('Processing...');
                },
                success: function (response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message || 'Credit Memo Created',
                        });
                        btn.remove();

                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'error!',
                            text: response.message || 'Failed to create Credit Memo.',
                        });
                    }
                },
                error: function () {
                    alert('Something went wrong!');
                },
                complete: function () {
                    btn.prop('disabled', false).text('Refund Order');
                }
            });
        });


    </script>

@endsection