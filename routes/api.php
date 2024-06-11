<?php

use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Middleware\EnsureAdminRole;
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
], function () {
    // Auth
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('refresh-token', [AuthController::class, 'refreshToken'])->name('refresh');
    Route::get('profile', [AuthController::class, 'profile'])->name('profile');
    Route::get('products', [ProductController::class, 'getProducts'])->name('products.show');
    Route::get('products/brand', [ProductController::class, 'getBrandProductCounts'])->name('products.brand');
    
    Route::group(['middleware' => EnsureUserRole::class], function () {
        // Cart
        Route::get('/cart', [CartController::class, 'getCartProducts'])->name('cart.show');
        Route::post('/cart/{id_product}', [CartController::class, 'addProduct'])->name('cart.addProduct');
        Route::delete('/cart/{id_product}', [CartController::class, 'deleteProduct'])->name('cart.deleteProduct');

        Route::post('payment', [TransactionController::class, 'payment'])->name('payment');

        // Address
        Route::get('address', [AddressController::class, 'show'])->name('address.show');
        Route::post('address', [AddressController::class, 'store'])->name('address.store');
        Route::put('/address/{id}', [AddressController::class, 'update'])->name('address.update');
    });

    Route::group(['middleware' => EnsureAdminRole::class], function () {
        // Auth
        Route::post('addAdmin', [AuthController::class, 'addAdmin'])->name('addAdmin');

        // Product
        Route::post('products', [ProductController::class, 'store'])->name('products.store');        
        Route::post('products/{id}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('products/{id}', [ProductController::class, 'destroy'])->name('products.delete');
    });
});



Route::get('location/{type}', [AddressController::class, 'getLocation'])->name('city.show');
Route::get('province', [AddressController::class, 'getProvinces'])->name('province.show');
