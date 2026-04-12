<?php

namespace App\Domain\Payment\Services;

use App\Models\Payment;
use Illuminate\Support\Str;

class PaymentReferenceGenerator
{
    public function generate(): string
    {
        do {
            $reference = 'PAY-' . strtoupper(Str::random(16));
        } while (Payment::where('reference', $reference)->exists());

        return $reference;
    }
}
