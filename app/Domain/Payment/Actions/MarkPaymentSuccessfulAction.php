<?php

namespace App\Domain\Payment\Actions;

use App\Models\Payment;

class MarkPaymentSuccessfulAction
{
    public function execute(Payment $payment): Payment
    {
        $payment->status = 'successful';

        return $payment;
    }
}
