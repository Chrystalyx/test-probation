<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>@yield('title') - Test Probation</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    @yield('style')
</head>

<body class="bg-light d-flex align-items-center justify-content-center" style="height: 100vh;">

    @yield('content')

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        let BASE_URL = '{{ url('/') }}';
        let TOKEN = '{{ csrf_token() }}';

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

        function showAlert(icon, title, text) {
            Swal.fire({
                icon: icon,
                title: title,
                text: text
            });
        }
    </script>

    @yield('script')

</body>

</html>
