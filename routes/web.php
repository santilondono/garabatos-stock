<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\EntryController;
use App\Http\Controllers\OutputController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('auth.login');
});

Route::group(['middleware' => 'role:1'], function () {
    Route::resource('security/users', UserController::class);

    Route::resource('stock/outputs', OutputController::class);

    Route::resource('stock/entries', EntryController::class);

    Route::resource('stock/products', ProductController::class);

    Route::resource('stock/roles', RoleController::class);
});

Route::get('print-sale/{sale_id}', [SaleController::class, 'printSale'])->name('print-sale');

Route::resource('stock-now', StockController::class);

Route::resource('stock/clients', ClientController::class);

Route::resource('stock/sales', SaleController::class);

Auth::routes();

Route::get('/home', [StockController::class, 'index'])->name('home');
