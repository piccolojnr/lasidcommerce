<?php

namespace App\Domain\Payment\Services;

class PaymentSignatureVerifier
{
    public function verify(string $signature, string $payload, string $secret): bool
    {
        return hash_hmac('sha512', $payload, $secret) === $signature;
    }
}
