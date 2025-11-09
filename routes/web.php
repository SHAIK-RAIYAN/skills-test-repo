<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductTestController;

Route::get('/', [ProductTestController::class, 'index']);
Route::post('/save-product', [ProductTestController::class, 'store']);
Route::get('/get-products', [ProductTestController::class, 'list']);