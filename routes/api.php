<?php

use App\Http\Controllers\Api\Addresses\AddressController;
use App\Http\Controllers\Api\Cart\CartController;
use App\Http\Controllers\Api\Cart\CartCouponController;
use App\Http\Controllers\Api\Cart\CartItemController;
use App\Http\Controllers\Api\Catalog\CategoryController;
use App\Http\Controllers\Api\Catalog\ProductController;
use App\Http\Controllers\Api\Checkout\CheckoutController;
use App\Http\Controllers\Api\Orders\OrderController;
use App\Http\Controllers\Api\Profile\ProfileController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::prefix('catalog')->name('catalog.')->group(function () {
        Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::get('categories/{slug}', [CategoryController::class, 'show'])->name('categories.show');

        Route::get('products', [ProductController::class, 'index'])->name('products.index');
        Route::get('products/{slug}', [ProductController::class, 'show'])->name('products.show');
    });

    Route::get('cart', [CartController::class, 'show'])->name('cart.show');
    Route::post('cart/items', [CartItemController::class, 'store'])->name('cart.items.store');
    Route::patch('cart/items/{cartItem}', [CartItemController::class, 'update'])->name('cart.items.update');
    Route::delete('cart/items/{cartItem}', [CartItemController::class, 'destroy'])->name('cart.items.destroy');
    Route::post('cart/coupon', [CartCouponController::class, 'store'])->name('cart.coupon.store');
    Route::delete('cart/coupon', [CartCouponController::class, 'destroy'])->name('cart.coupon.destroy');

    Route::post('checkout/shipping-methods/resolve', [CheckoutController::class, 'resolveShippingMethods'])->name('checkout.shipping-methods.resolve');

    Route::middleware('auth')->group(function () {
        Route::prefix('checkout')->name('checkout.')->group(function () {
            Route::post('preview', [CheckoutController::class, 'preview'])->name('preview');
            Route::post('initialize', [CheckoutController::class, 'initialize'])->name('initialize');
        });
        Route::apiResource('addresses', AddressController::class)->except(['create', 'edit', 'show'])->names('addresses');
        Route::patch('addresses/{address}/default', [AddressController::class, 'setDefault'])->name('addresses.set-default');
        Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');

        Route::get('profile', [ProfileController::class, 'show'])->name('profile.show');
        Route::match(['put', 'patch'], 'profile', [ProfileController::class, 'update'])->name('profile.update');
    });
});
