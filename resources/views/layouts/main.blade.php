<!DOCTYPE html>
<html lang="en" data-layout="horizontal" data-content-width="boxed" data-bs-theme="light" data-sidebar-color="dark"
    data-topbar-color="light" data-theme-colors="pastel-blue" dir="ltr">

<head>

    <meta charset="utf-8">
    <title>@yield('title') - Test Probation</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta content="Inventory Management System" name="description">
    <meta content="Test Probation" name="author">
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    {{-- CSS FOR DATATABLE --}}

    @yield('style')
</head>

<body class="horizontal-layout">
    <!-- Begin page -->
    <div id="layout-wrapper">
        <!-- Start topbar -->
        {{-- @include('layouts.header') --}}
        <!-- End topbar -->
        <!-- ========== Left Sidebar Start ========== -->
        <!-- Left Sidebar End -->
        <div class="sidebar-backdrop" id="sidebar-backdrop"></div>
        <!-- ========== Left Sidebar Start ========== -->
        {{-- @include('layouts.navbar') --}}
        <!-- Left Sidebar End -->
        <div class="sidebar-backdrop" id="sidebar-backdrop"></div>
        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    @yield('content')

                    @yield('modal')
                </div><!-- container-fluid -->
            </div><!-- End Page-content -->

            <!-- Begin Footer -->
            {{-- @include('layouts.footer') --}}
            <!-- END Footer -->
            <!-- END Footer -->
        </div>
        <!-- end main content-->
    </div>
    <!-- end main content-->
    </div>
    <!-- END layout-wrapper -->

    <script>
        let BASE_URL = '{{ url('/') }}';
        let TOKEN = '{{ csrf_token() }}';
    </script>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        function showLoading(title, text) {
            Swal.fire({
                title: title || 'Please Wait!',
                text: text || 'Processing data...',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        }

        function hideLoading() {
            Swal.close();
        }

        function showAlertOnSubmitWithRedirect(res, redirectUrl) {
            hideLoading();
            if (res.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: res.message
                }).then(() => {
                    if (redirectUrl) {
                        window.location.href = redirectUrl;
                    }
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: res.message
                });
            }
        }

        function showAlert(icon, title, text) {
            Swal.fire({
                icon: icon,
                title: title,
                text: text
            });
        }

        function showAlertOnSubmit(res, modal, table) {
            if (res.status === 'success' || res.status === 200) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: res.message || 'Data saved successfully'
                }).then(() => {
                    if (modal) {
                        $(modal).modal('hide');
                        $(modal).find('form')[0].reset();
                    }
                    if (table) {
                        $(table).DataTable().ajax.reload();
                    }
                });
            } else {
                let errorMsg = res.message || 'An error occurred';
                if (typeof errorMsg === 'object') {
                    let msgs = [];
                    $.each(errorMsg, function(key, val) {
                        msgs.push(val[0]);
                    });
                    errorMsg = msgs.join('\n');
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMsg
                });
            }
        }

        function showPopupWithAction(title, text, icon, method, data, url, successMsg, tables) {
            Swal.fire({
                title: title,
                text: text,
                icon: icon,
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: url,
                        type: method,
                        headers: {
                            'X-CSRF-TOKEN': TOKEN
                        },
                        data: data,
                        dataType: 'json',
                        beforeSend: function() {
                            showLoading('Please Wait!', 'Processing data...');
                        },
                        success: function(res) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: res.message || successMsg || 'Done'
                            }).then(() => {
                                if (tables) {
                                    tables.forEach(function(table) {
                                        $(table).DataTable().ajax.reload();
                                    });
                                }
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: xhr.responseJSON?.message || 'An error occurred'
                            });
                        }
                    });
                }
            });
        }

        function formatNumber(number) {
            if (number === null || number === undefined || number === '') return '0';
            return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        function convertSeparator(number) {
            if (!number) return 0;
            return parseInt(number.toString().replace(/\./g, ''));
        }

        function getInventories(params) {
            let filter = '';
            if (params.filter) {
                filter = params.filter;
            }

            $.ajax({
                url: BASE_URL + "/api/inventories?all=true&order[id]=desc" + filter,
                type: 'GET',
                headers: {

                },
                dataType: 'JSON',
                success: function(data) {
                    let element = $(params.element);
                    element.empty();
                    element.append('<option value="">-- Select Inventory --</option>');

                    if (data && data.length > 0) {
                        data.forEach(function(item) {
                            element.append('<option value="' + item.id + '">' + item.code + ' - ' + item.name + '</option>');
                        });
                    }

                    if (params.selected) {
                        element.val(params.selected).trigger('change');
                    }

                    if (params.is_async && params.callback) {
                        params.callback();
                    }
                }
            });
        }

        $(document).on('click', '#btn-logout', function() {
            $.ajax({
                type: 'POST',
                url: BASE_URL + '/api/auth/logout',
                headers: {
                    'X-CSRF-TOKEN': TOKEN
                },
                dataType: 'json',
                beforeSend: function() {
                    showLoading('Please Wait!', 'Logging out...');
                },
                success: function(res) {
                    Swal.close();
                    if (res.redirect) {
                        window.location.href = res.redirect;
                    }
                },
                error: function() {
                    Swal.close();
                    window.location.href = BASE_URL + '/login';
                }
            });
        });
    </script>

    @if (session('success'))
    <script>
        showAlert('success', 'Success', "{{ session('success') }}");
    </script>
    @endif
    @if (session('error'))
    <script>
        showAlert('error', 'Error', "{{ session('error') }}");
    </script>
    @endif

    @yield('script')

</body>

</html>