<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\InventoryController;
use App\Http\Controllers\API\PurchaseController;
use App\Http\Controllers\API\SaleController;

Route::controller(AuthController::class)->group(function () {
    Route::post('auth/login', 'authenticate')->name('api.login');
    Route::post('auth/logout', 'logout')->name('api.logout');
});

Route::middleware([\App\Http\Middleware\RoleMiddleware::class . ':SuperAdmin,Sales,Purchase,Manager'])->controller(InventoryController::class)->group(function () {
    Route::get('inventories/{id?}', 'get')->name('get.inventories');
});

Route::middleware([\App\Http\Middleware\RoleMiddleware::class . ':SuperAdmin'])->controller(InventoryController::class)->group(function () {
    Route::post('inventories', 'post')->name('post.inventories');
    Route::patch('inventories/{id}', 'patch')->name('patch.inventories');
    Route::put('inventories/{id}', 'put')->name('put.inventories');
    Route::delete('inventories/{id}', 'delete')->name('delete.inventories');
    Route::post('inventories_datatables', 'datatables')->name('datatable.inventories');
    Route::patch('inventories/{id}/approve', 'approve')->name('approve.inventories');
});

Route::middleware([\App\Http\Middleware\RoleMiddleware::class . ':SuperAdmin,Purchase,Manager'])->controller(PurchaseController::class)->group(function () {
    Route::get('purchases/{id?}', 'get')->name('get.purchases');
    Route::middleware(\App\Http\Middleware\RoleMiddleware::class . ':SuperAdmin,Purchase')->group(function () {
        Route::post('purchases', 'post')->name('post.purchases');
        Route::patch('purchases/{id}', 'patch')->name('patch.purchases');
        Route::put('purchases/{id}', 'put')->name('put.purchases');
        Route::delete('purchases/{id}', 'delete')->name('delete.purchases');
        Route::patch('purchases/{id}/approve', 'approve')->name('approve.purchases');
    });
    Route::post('purchases_datatables', 'datatables')->name('datatable.purchases');
});

Route::middleware([\App\Http\Middleware\RoleMiddleware::class . ':SuperAdmin,Sales,Manager'])->controller(SaleController::class)->group(function () {
    Route::get('sales/{id?}', 'get')->name('get.sales');
    Route::middleware(\App\Http\Middleware\RoleMiddleware::class . ':SuperAdmin,Sales')->group(function () {
        Route::post('sales', 'post')->name('post.sales');
        Route::patch('sales/{id}', 'patch')->name('patch.sales');
        Route::put('sales/{id}', 'put')->name('put.sales');
        Route::delete('sales/{id}', 'delete')->name('delete.sales');
        Route::patch('sales/{id}/approve', 'approve')->name('approve.sales');
    });
    Route::post('sales_datatables', 'datatables')->name('datatable.sales');
});
