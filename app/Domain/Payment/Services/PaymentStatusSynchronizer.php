<?php

namespace App\Domain\Payment\Services;

use App\Models\Payment;

class PaymentStatusSynchronizer
{
    public function sync(Payment $payment): Payment
    {
        return $payment;
    }
}
