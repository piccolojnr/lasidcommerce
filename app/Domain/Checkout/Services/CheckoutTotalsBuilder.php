<?php

namespace App\Domain\Checkout\Services;

use App\Domain\Checkout\DTOs\CheckoutData;

class CheckoutTotalsBuilder
{
    public function build(CheckoutData $checkoutData): array
    {
        return [
            'subtotal_amount' => $checkoutData->subtotalAmount,
            'discount_amount' => $checkoutData->discountAmount,
            'tax_amount' => $checkoutData->taxAmount,
            'shipping_amount' => $checkoutData->shippingAmount,
            'total_amount' => $checkoutData->totalAmount,
        ];
    }
}
