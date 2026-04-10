<?php

use App\Http\Controllers\Webhooks\Paystack\PaystackWebhookController;
use Illuminate\Support\Facades\Route;

Route::prefix('webhooks')->name('webhooks.')->group(function () {
    Route::post('paystack', [PaystackWebhookController::class, 'handle'])->name('paystack.handle');
});
