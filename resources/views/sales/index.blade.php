@extends('layouts.app')
@section('title', 'Riwayat Penjualan')

@section('content')
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Riwayat Penjualan</h6>
            @if (Auth::user()->role !== App\Enums\Role::MANAGER)
                <a href="{{ route('sales.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-cart-plus"></i> Transaksi Baru
                </a>
            @endif
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="tableSales" width="100%">
                    <thead>
                        <tr>
                            <th>No Transaksi</th>
                            <th>Tanggal</th>
                            <th>Dibuat Oleh</th>
                            <th>Total Harga</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="detailModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Transaksi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-sm">
                        <tr>
                            <td>Nomor:</td>
                            <td id="detNumber"></td>
                        </tr>
                        <tr>
                            <td>Tanggal:</td>
                            <td id="detDate"></td>
                        </tr>
                        <tr>
                            <td>Sales:</td>
                            <td id="detUser"></td>
                        </tr>
                    </table>
                    <table class="table table-bordered mt-3">
                        <thead>
                            <tr>
                                <th>Barang</th>
                                <th>Qty</th>
                                <th>Harga</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="detItems"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            var table = $('#tableSales').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('sales.index') }}",
                columns: [{
                        data: 'number',
                        name: 'number'
                    },
                    {
                        data: 'date',
                        name: 'date'
                    },
                    {
                        data: 'user.name',
                        name: 'user.name'
                    },
                    {
                        data: 'total_price',
                        name: 'total_price',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ],
                dom: 'Bfrtip',
                buttons: ['csv', 'excel', 'pdf', 'print']
            });

            $('body').on('click', '.showDetail', function() {
                var id = $(this).data('id');
                $.get("{{ url('sales') }}" + '/' + id, function(data) {
                    $('#detNumber').text(data.number);
                    $('#detDate').text(data.date);
                    $('#detUser').text(data.user.name);

                    var rows = '';
                    var grandTotal = 0;
                    $.each(data.details, function(key, val) {
                        var subtotal = val.price * val.qty;
                        grandTotal += subtotal;
                        rows += '<tr>' +
                            '<td>' + val.inventory.name + '</td>' +
                            '<td>' + val.qty + '</td>' +
                            '<td>Rp ' + val.price.toLocaleString() + '</td>' +
                            '<td>Rp ' + subtotal.toLocaleString() + '</td>' +
                            '</tr>';
                    });
                    rows +=
                        '<tr class="fw-bold"><td colspan="3" class="text-end">Total</td><td>Rp ' +
                        grandTotal.toLocaleString() + '</td></tr>';
                    $('#detItems').html(rows);
                    $('#detailModal').modal('show');
                });
            });

            $('body').on('click', '.deleteSale', function() {
                var saleId = $(this).data("id");

                Swal.fire({
                    title: 'Batalkan Transaksi?',
                    text: "Stok barang akan dikembalikan ke Inventory!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Batalkan!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            type: "DELETE",
                            url: "{{ url('sales') }}" + '/' + saleId,
                            success: function(data) {
                                Swal.fire(
                                    'Dibatalkan!',
                                    data.success,
                                    'success'
                                );
                                $('#tableSales').DataTable().ajax.reload();
                            },
                            error: function(data) {
                                Swal.fire(
                                    'Gagal!',
                                    data.responseJSON.error || 'Terjadi kesalahan.',
                                    'error'
                                );
                            }
                        });
                    }
                });
            });
        });
    </script>
@endpush
