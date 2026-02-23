@extends('layouts.main')

@section('title', $title)

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <h3>Dashboard</h3>
                <p>Welcome to the Inventory System.</p>
                <div class="alert alert-info">
                    Your current role: <strong>{{ Auth::user()->role->label() }}</strong>
                </div>

                <div class="mt-4">
                    <h5>Application Modules:</h5>
                    <div class="d-flex flex-wrap gap-2">
                        @if(in_array(Auth::user()->role, [\App\Enums\Role::SUPER_ADMIN]))
                        <a href="{{ route('inventories.index') }}" class="btn btn-primary"><i class="fa fa-boxes"></i> Inventories</a>
                        @endif

                        @if(in_array(Auth::user()->role, [\App\Enums\Role::SUPER_ADMIN, \App\Enums\Role::SALES, \App\Enums\Role::MANAGER]))
                        <a href="{{ route('sales.index') }}" class="btn btn-success"><i class="fa fa-shopping-cart"></i> Sales</a>
                        @endif

                        @if(in_array(Auth::user()->role, [\App\Enums\Role::SUPER_ADMIN, \App\Enums\Role::PURCHASE, \App\Enums\Role::MANAGER]))
                        <a href="{{ route('purchases.index') }}" class="btn btn-warning"><i class="fa fa-truck"></i> Purchases</a>
                        @endif

                        <button type="button" class="btn btn-danger" id="btn-logout"><i class="fa fa-sign-out-alt"></i> Logout</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection