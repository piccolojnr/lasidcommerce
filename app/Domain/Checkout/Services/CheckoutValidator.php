<?php

namespace App\Domain\Checkout\Services;

use App\Domain\Checkout\DTOs\CheckoutData;

class CheckoutValidator
{
    public function validate(CheckoutData $checkoutData): bool
    {
        return ! empty($checkoutData->shippingMethodId);
    }
}
