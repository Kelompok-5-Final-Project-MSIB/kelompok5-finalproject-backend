<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::group([
    'middleware' => 'auth:api'
], function() {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('refresh-token', [AuthController::class, 'refreshToken']);
    Route::get('profile', [AuthController::class, 'profile']);
});

Route::post('products', [ProductController::class, 'store']);
Route::get('products', [ProductController::class, 'getProducts']);
Route::get('products/search', [ProductController::class, 'searchProducts']);
Route::get('products/{id}', [ProductController::class, 'getProductById']);
Route::get('products/brand/{brand}', [ProductController::class, 'getProductByBrand']);
Route::post('products/{id}', [ProductController::class, 'update']);
Route::delete('products/{id}', [ProductController::class, 'destroy']);