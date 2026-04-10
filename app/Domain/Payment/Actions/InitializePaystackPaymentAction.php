<?php

namespace App\Domain\Payment\Actions;

use App\Domain\Payment\DTOs\PaymentData;

class InitializePaystackPaymentAction
{
    public function execute(PaymentData $paymentData): array
    {
        return $paymentData->toArray();
    }
}
