<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\CartController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CheckoutController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Product Routes
    Route::prefix('products')->group(function () {
        Route::get('/', [ProductController::class, 'index']);
        Route::post('/store', [ProductController::class, 'store']);
        Route::get('/{id}', [ProductController::class, 'show']);
        Route::post('/{id}', [ProductController::class, 'update']);
        Route::delete('/{id}', [ProductController::class, 'destroy']);
    });

    // Cart Routes
    Route::prefix('cart')->group(function () {
        Route::get('/', [CartController::class, 'index']);
        Route::post('/add', [CartController::class, 'addToCart']);
        Route::post('/item/{itemId}', [CartController::class, 'updateCartItem']);
        Route::delete('/item/{itemId}', [CartController::class, 'removeFromCart']);
        Route::delete('/clear', [CartController::class, 'clearCart']);
    });

    /* Route::post('/checkout', [CheckoutController::class, 'initiateCheckout']);
    Route::get('/checkout/success', [CheckoutController::class, 'checkoutSuccess']); */
    Route::post('/checkout', [CheckoutController::class, 'createOrder']);
    Route::get('/checkout/success', [CheckoutController::class, 'checkoutSuccess']);

});
// Webhook route (must be outside auth middleware)
Route::post('/stripe/webhook', [CheckoutController::class, 'handleWebhook']);