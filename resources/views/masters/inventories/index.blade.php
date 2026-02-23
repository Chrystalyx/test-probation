@extends('layouts.main')

@section('title', $title)

@section('style')
<link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css" rel="stylesheet">
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-8">
        <div class="page-title-box d-flex justify-content-between align-items-center">
            <h4 class="mb-sm-0">{{ $title }}</h4>
            <nav>
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item">
                        <a href="{{ url('/') }}"><i class="fa fa-home"></i></a>
                    </li>
                    <li class="breadcrumb-item">Master Data</li>
                    <li class="breadcrumb-item active">{{ $title }}</li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title">Data {{ $title }}</h4>
                @if((Auth::user()->role === \App\Enums\Role::SUPER_ADMIN))
                <button class="btn btn-primary btn-sm" id="add-button">
                    <i class="fa fa-plus"></i> Add Data
                </button>
                @endif
            </div>
            <div class="card-body">
                <table id="main-table" class="table table-bordered table-striped nowrap w-100">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Price</th>
                            <th>Stock</th>
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
<div class="modal fade" id="form-modal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="main-form">
                <div class="modal-header">
                    <h5 class="modal-title" id="form-modal_title"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="id" id="input-id">

                    <div class="row">
                        <div class="col-6">
                            <label class="form-label" for="input-code">
                                Code <span class="text-danger">*</span>
                            </label>
                            <input class="form-control" id="input-code" type="text" name="code">
                        </div>

                        <div class="col-6">
                            <label class="form-label" for="input-name">
                                Name <span class="text-danger">*</span>
                            </label>
                            <input class="form-control" id="input-name" type="text" name="name">
                        </div>

                        <div class="col-6 mt-3">
                            <label class="form-label" for="input-price">
                                Price <span class="text-danger">*</span>
                            </label>
                            <input class="form-control" id="input-price" type="number" name="price">
                        </div>

                        <div class="col-6 mt-3">
                            <label class="form-label" for="input-stock">
                                Stock <span class="text-danger">*</span>
                            </label>
                            <input class="form-control" id="input-stock" type="number" name="stock">
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">Save</button>
                </div>
            </form>
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
    let endpoint = 'inventories';

    drawDatatable();

    $(document).on("click", "button#add-button", function() {
        $('#input-id').val('');
        $('h5#form-modal_title').text('Add {{ $title }}');
        $('#form-modal').modal('show');
    });

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

    $('form#main-form').submit(function(e) {
        e.preventDefault();
        var formData = new FormData(this);

        $.ajax({
            type: 'post',
            url: BASE_URL + "/api/" + endpoint,
            headers: {
                
            },
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            dataType: 'json',
            beforeSend: function() {
                showLoading('Please Wait!', 'Saving data...');
            },
            success: function(res) {
                hideLoading();
                showAlertOnSubmit(res, '#form-modal', '#main-table');
            }
        });
    });

    $(document).on("click", "a#edit-data", function(e) {
        e.preventDefault();
        let id = $(this).data('id');
        $('h5#form-modal_title').text('Edit {{ $title }}');

        $.ajax({
            url: BASE_URL + "/api/" + endpoint + "/" + id,
            type: 'GET',
            headers: {
                
            },
            dataType: 'JSON',
            beforeSend: function() {
                showLoading('Please Wait!', 'Loading data...');
            },
            success: function(data, textStatus, jqXHR) {
                $('#input-id').val(data.id);
                $('#input-code').val(data.code);
                $('#input-name').val(data.name);
                $('#input-price').val(data.price);
                $('#input-stock').val(data.stock);

                $('#form-modal').modal('show');
                hideLoading();
            },
            error: function(jqXHR, textStatus, errorThrown) {

            },
        });

        $('#form-modal').on('hidden.bs.modal', function() {
            $('#input-id').val('');
            $('#main-form')[0].reset();
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
                headers: {
                    Authorization: TOKEN
                },
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
                    data: 'code',
                    name: 'code'
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'price',
                    name: 'price',
                    render: function(data) {
                        return formatNumber(data);
                    }
                },
                {
                    data: 'stock',
                    name: 'stock'
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
