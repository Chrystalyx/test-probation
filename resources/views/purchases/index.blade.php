@extends('layouts.main')

@section('title', $title)

@section('style')
<link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css" rel="stylesheet">
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-10">
        <div class="page-title-box d-flex justify-content-between align-items-center">
            <h4 class="mb-sm-0">{{ $title }}</h4>
            <nav>
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item">
                        <a href="{{ url('/') }}"><i class="fa fa-home"></i></a>
                    </li>
                    <li class="breadcrumb-item active">{{ $title }}</li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-10">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title">Data {{ $title }}</h4>
                @if(in_array(Auth::user()->role, [\App\Enums\Role::SUPER_ADMIN, \App\Enums\Role::PURCHASE]))
                <a href="{{ route('purchases.form') }}" class="btn btn-primary btn-sm" id="add-button">
                    <i class="fa fa-plus"></i> Add Data
                </a>
                @endif
            </div>
            <div class="card-body">
                <table id="main-table" class="table table-bordered table-striped nowrap w-100">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Number</th>
                            <th>Date</th>
                            <th>User</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('modal')
<div class="modal fade" id="detail-modal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detail-modal_title">Purchase Detail</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-4"><strong>Number:</strong> <span id="detail-number"></span></div>
                    <div class="col-4"><strong>Date:</strong> <span id="detail-date"></span></div>
                    <div class="col-4"><strong>User:</strong> <span id="detail-user_name"></span></div>
                </div>
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>Inventory</th>
                            <th>Qty</th>
                            <th>Price</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody id="detail-items"></tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3" class="text-end">Total</th>
                            <th id="detail-total"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<script>
    let dt;
    let endpoint = 'purchases';

    drawDatatable();

    $(document).on('click', 'a#delete-data', function(e) {
        e.preventDefault();
        let id = $(this).data('id');

        showPopupWithAction(
            'Are you sure?',
            'Delete this {{ $title }} data?',
            'warning',
            'DELETE',
            null,
            BASE_URL + '/api/' + endpoint + '/' + id,
            '',
            ['#main-table']
        );
    });

    $(document).on("click", "a#detail-data", function(e) {
        e.preventDefault();
        let id = $(this).data('id');

        $.ajax({
            url: BASE_URL + "/api/" + endpoint + "/" + id,
            type: 'GET',
            headers: {
                
            },
            dataType: 'JSON',
            beforeSend: function() {
                showLoading('Please Wait!', 'Loading data...');
            },
            success: function(data) {
                $('#detail-number').text(data.number);
                $('#detail-date').text(data.date);
                $('#detail-user_name').text(data.user_name);

                let html = '';
                let total = 0;
                if (data.details && data.details.length > 0) {
                    data.details.forEach(function(item) {
                        let subtotal = item.qty * item.price;
                        total += subtotal;
                        html += '<tr>';
                        html += '<td>' + item.inventory_code + ' - ' + item.inventory_name + '</td>';
                        html += '<td>' + item.qty + '</td>';
                        html += '<td>' + formatNumber(item.price) + '</td>';
                        html += '<td>' + formatNumber(subtotal) + '</td>';
                        html += '</tr>';
                    });
                }
                $('#detail-items').html(html);
                $('#detail-total').text(formatNumber(total));

                $('#detail-modal').modal('show');
                hideLoading();
            }
        });
    });

    function drawDatatable() {
        dt = $('#main-table').addClass('nowrap').DataTable({
            dom: "<'row mb-3'<'col-md-6'B><'col-md-6'f>>" +
                "<'row'<'col-12'tr>>" +
                "<'row mt-3'<'col-md-5'i><'col-md-4'p><'col-md-3 text-end'l>>",
            buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],

            destroy: true,
            pageLength: 10,
            processing: true,
            serverSide: true,
            responsive: true,
            scrollX: false,

            ajax: {
                url: BASE_URL + '/api/' + endpoint + '_datatables',
                type: 'POST',
                dataType: 'json',
                data: function(d) {
                    d.filter = {};
                }
            },

            columns: [{
                    data: 'id',
                    name: 'id',
                    visible: false
                },
                {
                    data: 'number',
                    name: 'number'
                },
                {
                    data: 'date',
                    name: 'date'
                },
                {
                    data: 'user_name',
                    name: 'user_name'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    className: 'text-end'
                }
            ],

            order: [
                [0, 'desc']
            ]
        });
    }
</script>
@endsection
