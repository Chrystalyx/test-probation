@extends('layouts.app')

@section('title', 'Master Data Inventory')

@section('content')
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Barang (Inventory)</h6>
            <button class="btn btn-primary btn-sm" id="createNewItem">
                <i class="bi bi-plus"></i> Tambah Barang
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="tableInventory" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama Barang</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th width="150px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($inventories as $item)
                            <tr>
                                <td>{{ $item->code }}</td>
                                <td>{{ $item->name }}</td>
                                <td>Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                <td>{{ $item->stock }}</td>
                                <td>
                                    <a href="javascript:void(0)" data-id="{{ $item->id }}"
                                        class="btn btn-warning btn-sm editItem">Edit</a>
                                    <a href="javascript:void(0)" data-id="{{ $item->id }}"
                                        class="btn btn-danger btn-sm deleteItem">Hapus</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="ajaxModal" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modalHeading"></h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="inventoryForm" name="inventoryForm" class="form-horizontal">
                        <input type="hidden" name="id" id="inventory_id">

                        <div class="form-group mb-3">
                            <label for="code" class="col-sm-2 control-label">Kode</label>
                            <div class="col-sm-12">
                                <input type="text" class="form-control" id="code" name="code"
                                    placeholder="Cth: BRG001" required>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="name" class="col-sm-2 control-label">Nama</label>
                            <div class="col-sm-12">
                                <input type="text" class="form-control" id="name" name="name"
                                    placeholder="Nama Barang" required>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="price" class="col-sm-2 control-label">Harga</label>
                            <div class="col-sm-12">
                                <input type="number" class="form-control" id="price" name="price"
                                    placeholder="Harga Satuan" required>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="stock" class="col-sm-2 control-label">Stok</label>
                            <div class="col-sm-12">
                                <input type="number" class="form-control" id="stock" name="stock"
                                    placeholder="Jumlah Stok" required>
                            </div>
                        </div>

                        <div class="col-sm-offset-2 col-sm-10">
                            <button type="submit" class="btn btn-primary" id="saveBtn" value="create">Simpan
                                Data</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script type="text/javascript">
        $(function() {
            var table = $('#tableInventory').DataTable({
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print'
                ],
                responsive: true
            });

            $('#createNewItem').click(function() {
                $('#saveBtn').val("create-item");
                $('#inventory_id').val('');
                $('#inventoryForm').trigger("reset");
                $('#modalHeading').html("Tambah Barang Baru");
                $('#ajaxModal').modal('show');
            });

            $('body').on('click', '.editItem', function() {
                var inventory_id = $(this).data('id');
                $.get("{{ route('inventory.index') }}" + '/' + inventory_id + '/edit', function(data) {
                    $('#modalHeading').html("Edit Barang");
                    $('#saveBtn').val("edit-user");
                    $('#ajaxModal').modal('show');

                    $('#inventory_id').val(data.id);
                    $('#code').val(data.code);
                    $('#name').val(data.name);
                    $('#price').val(data.price);
                    $('#stock').val(data.stock);
                })
            });

            $('#saveBtn').click(function(e) {
                e.preventDefault();
                $(this).html('Menyimpan...');

                $.ajax({
                    data: $('#inventoryForm').serialize(),
                    url: "{{ route('inventory.store') }}",
                    type: "POST",
                    dataType: 'json',
                    success: function(data) {
                        $('#inventoryForm').trigger("reset");
                        $('#ajaxModal').modal('hide');

                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: data.success,
                        }).then((result) => {
                            location.reload();
                        });
                    },
                    error: function(data) {
                        console.log('Error:', data);
                        $('#saveBtn').html('Simpan Data');
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Terjadi kesalahan input atau koneksi.',
                        });
                    }
                });
            });

            $('body').on('click', '.deleteItem', function() {
                var inventory_id = $(this).data("id");

                Swal.fire({
                    title: 'Yakin ingin menghapus?',
                    text: "Data tidak bisa dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Hapus!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            type: "DELETE",
                            url: "{{ route('inventory.index') }}" + '/' + inventory_id,
                            success: function(data) {
                                Swal.fire(
                                    'Terhapus!',
                                    data.success,
                                    'success'
                                ).then((result) => {
                                    location.reload();
                                });
                            },
                            error: function(data) {
                                console.log('Error:', data);
                            }
                        });
                    }
                })
            });

        });
    </script>
@endpush
