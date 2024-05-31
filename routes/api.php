<?php

use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Middleware\EnsureUserRole;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::post('register', [AuthController::class, 'register'])->name('register');
Route::post('login', [AuthController::class, 'login'])->name('login');
Route::get('allUsers', [AuthController::class, 'allUser'])->name('allUsers');

Route::group([
    'middleware' => 'auth:api'
], function() {
    // Auth
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('refresh-token', [AuthController::class, 'refreshToken'])->name('refresh');
    Route::get('profile', [AuthController::class, 'profile'])->name('profile');
    Route::group(['middleware' => EnsureUserRole::class], function() {
        // Cart
        Route::get('/cart', [CartController::class, 'getCartProducts'])->name('cart.show');
        Route::post('/cart', [CartController::class, 'addProduct'])->name('cart.addProduct');
        Route::delete('/cart/{id_product}', [CartController::class, 'deleteProduct'])->name('cart.deleteProduct');

        // Address
        Route::get('address', [AddressController::class, 'show'])->name('address.show');
        Route::post('address', [AddressController::class, 'store'])->name('address.store');
        Route::put('/address/{id}', [AddressController::class, 'update'])->name('address.update');
    });
});

// Product
Route::post('products', [ProductController::class, 'store'])->name('products.store');
Route::get('products', [ProductController::class, 'getProducts'])->name('products.show');
Route::get('products', [ProductController::class, 'searchProducts'])->name('products.search');
Route::get('products/{id}', [ProductController::class, 'getProductById'])->name('products.show');
Route::get('products/brand/{brand}', [ProductController::class, 'getProductByBrand'])->name('products.show');
Route::post('products/{id}', [ProductController::class, 'update'])->name('products.update');
Route::delete('products/{id}', [ProductController::class, 'destroy'])->name('products.delete');