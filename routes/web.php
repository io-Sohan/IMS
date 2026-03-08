<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;



Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');

Route::get('/logout', [AuthController::class, 'logout'])->middleware('token.auth')->name('logout');


Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('token.auth')->name('dashboard');
Route::get('/categories', [DashboardController::class, 'categories'])->middleware('token.auth')->name('categories');
Route::get('/products', [DashboardController::class, 'product'])->middleware('token.auth')->name('products');
Route::get('/stocks', [DashboardController::class, 'stock'])->middleware('token.auth')->name('stocks');
Route::get('/pos', [DashboardController::class, 'pos'])->middleware('token.auth')->name('pos');
Route::get('/invoices', [DashboardController::class, 'invoice'])->middleware('token.auth')->name('invoices');
Route::get('/customers', [DashboardController::class, 'customer'])->middleware('token.auth')->name('customers');



