<?php

use App\Http\Controllers\Api\Addresses\AddressController;
use App\Http\Controllers\Api\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Api\Auth\MagicLinkController;
use App\Http\Controllers\Api\Auth\PasswordAuthController;
use App\Http\Controllers\Api\Checkout\CheckoutController;
use App\Http\Controllers\Api\Orders\OrderController;
use App\Http\Controllers\Api\Payments\PaymentController;
use App\Http\Controllers\Api\Profile\ProfileController;
use App\Http\Controllers\Api\Shipping\ShippingZoneController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->name('auth.')->group(function () {
    Route::get('csrf-cookie', [AuthenticatedSessionController::class, 'csrfCookie'])->name('csrf-cookie');
    Route::get('session', [AuthenticatedSessionController::class, 'show'])->name('session');
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::post('magic-link/request', [MagicLinkController::class, 'store'])
        ->middleware('throttle:storefront-magic-links')
        ->name('magic-link.request');
    Route::get('magic-link/verify', [MagicLinkController::class, 'verify'])
        ->middleware('signed')
        ->name('magic-link.verify');

    Route::post('password/login', [PasswordAuthController::class, 'login'])
        ->middleware('throttle:storefront-password-logins')
        ->name('password.login');
    Route::post('password/forgot', [PasswordAuthController::class, 'forgot'])
        ->middleware('throttle:storefront-password-resets')
        ->name('password.forgot');
    Route::post('password/reset', [PasswordAuthController::class, 'reset'])
        ->name('password.reset');
});

Route::get('shipping-zones', [ShippingZoneController::class, 'index'])->name('shipping-zones.index');

Route::post('checkout/guest/initialize', [CheckoutController::class, 'initializeGuest'])
    ->name('checkout.guest.initialize');

Route::middleware(['auth:customer', 'ensure.storefront.customer'])->group(function () {
    Route::prefix('checkout')->name('checkout.')->group(function () {
        Route::post('preview', [CheckoutController::class, 'preview'])->name('preview');
        Route::post('orders', [CheckoutController::class, 'createOrder'])->name('orders.create');
        Route::post('initialize', [CheckoutController::class, 'initialize'])->name('initialize');
    });

    Route::apiResource('addresses', AddressController::class)->except(['create', 'edit', 'show'])->names('addresses');
    Route::patch('addresses/{address}/default', [AddressController::class, 'setDefault'])->name('addresses.set-default');

    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('orders/{order}/timeline', [OrderController::class, 'timeline'])->name('orders.timeline');

    Route::post('payments/initialize', [PaymentController::class, 'initialize'])->name('payments.initialize');

    Route::get('profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::match(['put', 'patch'], 'profile', [ProfileController::class, 'update'])->name('profile.update');
});
