@extends('layouts.app')
@section('title', 'Input Penjualan Baru')

@section('content')
    <form id="formTransaction">
        <div class="row">
            <div class="col-md-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Informasi Penjualan</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label>No Transaksi</label>
                            <input type="text" name="number" class="form-control" value="{{ $number }}" readonly>
                        </div>
                        <div class="mb-3">
                            <label>Tanggal</label>
                            <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Keranjang Barang</h6>
                        <button type="button" class="btn btn-success btn-sm" id="addItem">
                            <i class="bi bi-plus-circle"></i> Tambah Baris
                        </button>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered" id="tableItems">
                            <thead>
                                <tr>
                                    <th width="50%">Barang</th>
                                    <th width="15%">Stok</th>
                                    <th width="20%">Qty</th>
                                    <th width="15%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer text-end">
                        <button type="submit" class="btn btn-primary btn-lg" id="btnSave">Simpan Transaksi</button>
                        <a href="{{ route('sales.index') }}" class="btn btn-secondary btn-lg">Batal</a>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            var rowIdx = 0;

            var inventories = @json($inventories);

            $('#addItem').click(function() {
                var options = '<option value="">-- Pilih Barang --</option>';
                inventories.forEach(function(item) {
                    options +=
                        `<option value="${item.id}" data-stock="${item.stock}">${item.code} - ${item.name} (Rp ${item.price})</option>`;
                });

                var html = `
            <tr id="row${rowIdx}">
                <td>
                    <select name="items[${rowIdx}][inventory_id]" class="form-control select2 product-select" required>
                        ${options}
                    </select>
                </td>
                <td>
                    <input type="text" class="form-control stock-info" readonly disabled>
                </td>
                <td>
                    <input type="number" name="items[${rowIdx}][qty]" class="form-control qty-input" min="1" required>
                </td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm remove-row" data-row="row${rowIdx}">X</button>
                </td>
            </tr>
        `;

                $('#tableItems tbody').append(html);

                $('#row' + rowIdx + ' .select2').select2({
                    theme: 'bootstrap-5',
                    width: '100%'
                });

                rowIdx++;
            });

            $(document).on('change', '.product-select', function() {
                var stock = $(this).find(':selected').data('stock');
                var row = $(this).closest('tr');
                row.find('.stock-info').val(stock);
                row.find('.qty-input').attr('max', stock);
            });

            $(document).on('click', '.remove-row', function() {
                var rowId = $(this).data('row');
                $('#' + rowId).remove();
            });

            $('#formTransaction').submit(function(e) {
                e.preventDefault();

                if ($('#tableItems tbody tr').length === 0) {
                    Swal.fire('Error', 'Harap masukkan minimal satu barang!', 'error');
                    return;
                }

                var btn = $('#btnSave');
                btn.prop('disabled', true).text('Memproses...');

                $.ajax({
                    url: "{{ route('sales.store') }}",
                    type: "POST",
                    data: $(this).serialize(),
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.success
                        }).then(() => {
                            window.location.href = "{{ route('sales.index') }}";
                        });
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false).text('Simpan Transaksi');
                        var err = xhr.responseJSON.error || 'Terjadi kesalahan validasi.';
                        Swal.fire('Gagal', err, 'error');
                    }
                });
            });

            $('#addItem').click();
        });
    </script>
@endpush
