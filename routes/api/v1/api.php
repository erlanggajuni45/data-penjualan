<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TransaksiBarangController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/** PRODUCTS */
Route::prefix('products')->group(function() {
Route::get('/', [ProductController::class, 'listProduct']);
Route::post('/', [ProductController::class, 'addProduct']);
Route::get('/edit/{id}', [ProductController::class, 'editProduct']);
Route::put('/edit/{id}', [ProductController::class, 'updateProduct']);
Route::delete('/{id}', [ProductController::class, 'deleteProduct']);
});

/** TRANSACTIONS */
Route::prefix('transactions')->group(function() {
Route::get('/', [TransaksiBarangController::class, 'listTransaction']);
Route::post('/', [TransaksiBarangController::class, 'addTransaction']);
Route::delete('/{id}', [TransaksiBarangController::class, 'deleteTransaction']);
Route::get('/edit/{id}', [TransaksiBarangController::class, 'editTransaction']);
Route::put('/edit/{id}', [TransaksiBarangController::class, 'updateTransaction']);
});
