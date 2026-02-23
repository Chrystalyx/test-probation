@extends('layouts.main')

@section('title', $title)

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
                    <li class="breadcrumb-item">Sales</li>
                    <li class="breadcrumb-item active">{{ $title }}</li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-10">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">{{ $title }}</h4>
            </div>
            <div class="card-body">
                <form id="main-form">
                    <input type="hidden" name="id" id="input-id" value="{{ $id }}">

                    <div class="row">
                        <div class="col-md-4">
                            <label class="form-label" for="input-number">
                                Number <span class="text-danger">*</span>
                            </label>
                            <input class="form-control" id="input-number" type="text" name="number" readonly>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" for="input-date">
                                Date <span class="text-danger">*</span>
                            </label>
                            <input class="form-control" id="input-date" type="date" name="date">
                        </div>
                    </div>

                    <hr>
                    <h5>Detail Items</h5>

                    <div id="detail-wrapper"></div>

                    <div class="mt-3">
                        <button type="button" class="btn btn-outline-primary btn-sm" id="btn-add_detail">
                            <i class="fa fa-plus"></i> Add Item
                        </button>
                    </div>

                    <hr>
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('sales.index') }}" class="btn btn-outline-danger">Back</a>
                        <button type="submit" class="btn btn-success">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    let rowCount = 0;
    let endpoint = 'sales';

    $.ajax({
        url: BASE_URL + "/api/inventories?all=true&order[id]=desc",
        type: 'GET',
        headers: {

        },
        dataType: 'JSON',
        success: function(data) {
            window.inventoryData = data.data || data || [];
            $('select[name$="[inventory_id]"]').each(function() {
                let currentVal = $(this).val() || $(this).attr('data-selected-id');
                let options = '<option value="">-- Select Inventory --</option>';
                window.inventoryData.forEach(function(item) {
                    let isSelected = (item.id == currentVal) ? 'selected' : '';
                    options += '<option value="' + item.id + '" ' + isSelected + '>' + item.code + ' - ' + item.name + '</option>';
                });
                $(this).html(options);
            });
        }
    });

    $('#input-date').val(new Date().toISOString().split('T')[0]);

    let editId = '{{ $id }}';
    if (editId) {
        $.ajax({
            url: BASE_URL + "/api/" + endpoint + "/" + editId,
            type: 'GET',
            headers: {

            },
            dataType: 'JSON',
            beforeSend: function() {
                showLoading('Please Wait!', 'Loading data...');
            },
            success: function(data) {
                $('#input-id').val(data.id);
                $('#input-number').val(data.number);
                $('#input-date').val(data.date ? data.date.split('T')[0] : '');

                if (data.details && data.details.length > 0) {
                    data.details.forEach(function(detail) {
                        addRow(detail);
                        rowCount++;
                    });
                }
                hideLoading();
            }
        });
    } else {
        $('#input-number').val('Auto Generated');
        addRow();
        rowCount++;
    }

    function addRow(defaultValue) {
        let detailId = defaultValue?.id ?? '';
        let inventoryId = defaultValue?.inventory_id ?? '';
        let qtyVal = defaultValue?.qty ?? '';
        let priceVal = defaultValue ? formatNumber(defaultValue?.price ?? 0) : '';

        let html = '';

        html += '<div class="row g-3 detail-row ' + (rowCount > 0 ? 'mt-2' : '') + '" id="detail-row-' + rowCount + '">';
        html += '    <input type="hidden" name="details[' + rowCount + '][id]" value="' + detailId + '">';
        html += '    <div class="col-md-4">';
        html += '        <label class="form-label">Inventory</label>';
        html += '        <select class="form-select select2" name="details[' + rowCount + '][inventory_id]" id="input-inventory_id-' + rowCount + '">';
        html += '            <option value="">-- Select Inventory --</option>';

        if (window.inventoryData) {
            window.inventoryData.forEach(function(item) {
                let selected = (item.id == inventoryId) ? 'selected' : '';
                html += '            <option value="' + item.id + '" ' + selected + '>' + item.code + ' - ' + item.name + '</option>';
            });
        }

        html = html.replace('<select ', '<select data-selected-id="' + inventoryId + '" ');

        html += '        </select>';
        html += '    </div>';
        html += '    <div class="col-md-2">';
        html += '        <label class="form-label">Qty</label>';
        html += '        <input type="number" class="form-control" name="details[' + rowCount + '][qty]" id="input-qty-' + rowCount + '" value="' + qtyVal + '">';
        html += '    </div>';
        html += '    <div class="col-md-3">';
        html += '        <label class="form-label">Price</label>';
        html += '        <input type="number" class="form-control" name="details[' + rowCount + '][price]" id="input-price-' + rowCount + '" value="' + (defaultValue?.price ?? '') + '">';
        html += '    </div>';
        html += '    <div class="col-md-1 d-flex align-items-end">';
        html += '        <button type="button" class="btn btn-outline-danger btn-sm w-100 remove-detail" data-index="' + rowCount + '">';
        html += '            <i class="fa fa-trash"></i>';
        html += '        </button>';
        html += '    </div>';
        html += '</div>';

        $('#detail-wrapper').append(html);
        $('#input-inventory_id-' + rowCount).select2({
            theme: 'bootstrap-5',
            width: '100%'
        }).on('change', function() {
            let selectedId = $(this).val();
            let rowIdx = $(this).attr('id').split('-').pop();

            if (selectedId && window.inventoryData) {
                let selectedItem = window.inventoryData.find(i => i.id == selectedId);
                if (selectedItem) {
                    $('#input-price-' + rowIdx).val(selectedItem.price);
                }
            }
        });
    }

    $(document).on('click', '#btn-add_detail', function() {
        addRow();
        rowCount++;
    });

    $(document).on('click', '.remove-detail', function() {
        let index = $(this).data('index');
        $('#detail-row-' + index).remove();
    });

    $('form#main-form').submit(function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        formData.append('user_id', '{{ auth()->id() }}');

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
                showAlertOnSubmitWithRedirect(res, "{{ route('sales.index') }}");
            }
        });
    });
</script>
@endsection