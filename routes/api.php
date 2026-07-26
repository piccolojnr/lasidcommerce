<?php

use App\Http\Controllers\Api\Cart\CartController;
use App\Http\Controllers\Api\Cart\CartCouponController;
use App\Http\Controllers\Api\Cart\CartItemController;
use App\Http\Controllers\Api\Catalog\BrandController;
use App\Http\Controllers\Api\Catalog\CategoryController;
use App\Http\Controllers\Api\Catalog\CollectionController;
use App\Http\Controllers\Api\Catalog\ProductController;
use App\Http\Controllers\Api\Catalog\TagController;
use App\Http\Controllers\Api\Checkout\CheckoutController;
use App\Http\Controllers\Api\Storefront\AnnouncementController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::prefix('catalog')->name('catalog.')->group(function () {
        Route::get('brands', [BrandController::class, 'index'])->name('brands.index');
        Route::get('brands/{slug}', [BrandController::class, 'show'])->name('brands.show');

        Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::get('categories/{slug}', [CategoryController::class, 'show'])->name('categories.show');

        Route::get('tags', [TagController::class, 'index'])->name('tags.index');
        Route::get('tags/{slug}', [TagController::class, 'show'])->name('tags.show');

        Route::get('collections', [CollectionController::class, 'index'])->name('collections.index');
        Route::get('collections/{slug}', [CollectionController::class, 'show'])->name('collections.show');

        Route::get('products', [ProductController::class, 'index'])->name('products.index');
        Route::get('products/{slug}', [ProductController::class, 'show'])->name('products.show');
    });

    Route::get('storefront/announcement', [AnnouncementController::class, 'show'])
        ->name('storefront.announcement.show');

    Route::get('cart', [CartController::class, 'show'])->name('cart.show');
    Route::post('cart/items', [CartItemController::class, 'store'])->name('cart.items.store');
    Route::patch('cart/items/{cartItem}', [CartItemController::class, 'update'])->name('cart.items.update');
    Route::delete('cart/items/{cartItem}', [CartItemController::class, 'destroy'])->name('cart.items.destroy');
    Route::post('cart/coupon', [CartCouponController::class, 'store'])->name('cart.coupon.store');
    Route::delete('cart/coupon', [CartCouponController::class, 'destroy'])->name('cart.coupon.destroy');

    Route::post('checkout/shipping-methods/resolve', [CheckoutController::class, 'resolveShippingMethods'])->name('checkout.shipping-methods.resolve');
});
