<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\Auth\AuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\Masters\InventoryController;
use App\Http\Controllers\Web\Purchases\PurchaseController;
use App\Http\Controllers\Web\Sales\SaleController;

Route::get('/', [AuthController::class, 'index'])->name('login');
Route::get('/login', [AuthController::class, 'index'])->name('login.form');

Route::middleware('auth')->group(function () {
    Route::get('/home', [DashboardController::class, 'index'])->name('home');

    Route::controller(InventoryController::class)->middleware(\App\Http\Middleware\RoleMiddleware::class . ':SuperAdmin')->group(function () {
        Route::get('/master/inventories', 'index')->name('inventories.index');
    });

    Route::controller(PurchaseController::class)->middleware(\App\Http\Middleware\RoleMiddleware::class . ':SuperAdmin,Purchase,Manager')->group(function () {
        Route::get('/purchases', 'index')->name('purchases.index');
        Route::get('/purchases/form/{id?}', 'form')->name('purchases.form');
    });

    Route::controller(SaleController::class)->middleware(\App\Http\Middleware\RoleMiddleware::class . ':SuperAdmin,Sales,Manager')->group(function () {
        Route::get('/sales', 'index')->name('sales.index');
        Route::get('/sales/form/{id?}', 'form')->name('sales.form');
    });
});
