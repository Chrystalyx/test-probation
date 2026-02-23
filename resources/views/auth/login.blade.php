@extends('layouts.auth')

@section('title', 'Login')

@section('content')
<div class="card shadow-sm" style="width: 400px;">
    <div class="card-body p-4">
        <h3 class="card-title text-center mb-4">Login System</h3>

        <form id="login-form">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" required autofocus>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-primary">Login</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('script')
<script>
    $('form#login-form').submit(function(e) {
        e.preventDefault();
        var formData = new FormData(this);

        $.ajax({
            type: 'POST',
            url: BASE_URL + '/api/auth/login',
            headers: {
                'X-CSRF-TOKEN': TOKEN
            },
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            dataType: 'json',
            beforeSend: function() {
                showLoading('Please Wait!', 'Authenticating...');
            },
            success: function(res) {
                Swal.close();
                if (res.status === 'success') {
                    showAlert('success', 'Success', res.message);
                    setTimeout(function() {
                        window.location.href = res.redirect;
                    }, 1000);
                }
            },
            error: function(xhr) {
                Swal.close();
                let errorMsg = 'An error occurred';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                showAlert('error', 'Oops...', errorMsg);
            }
        });
    });
</script>
@endsection