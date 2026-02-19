@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h3>Dashboard</h3>
                    <p>Selamat datang di sistem Inventory.</p>
                    <div class="alert alert-info">
                        Role Anda saat ini: <strong>{{ Auth::user()->role->label() }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
