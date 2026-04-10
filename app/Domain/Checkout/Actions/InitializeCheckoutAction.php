<?php

namespace App\Domain\Checkout\Actions;

use App\Domain\Checkout\DTOs\CheckoutData;

class InitializeCheckoutAction
{
    public function execute(CheckoutData $checkoutData): array
    {
        return $checkoutData->toArray();
    }
}
