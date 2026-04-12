<?php

namespace App\Domain\Payment\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class PaystackClient
{
    private ?string $secretKey;
    private string $baseUrl;

    public function __construct()
    {
        $this->secretKey = config('services.paystack.secret_key');
        $this->baseUrl   = rtrim(config('services.paystack.base_url', 'https://api.paystack.co'), '/');
    }

    /**
     * Initialize a transaction.
     *
     * @param  array{email: string, amount: int, reference: string, callback_url?: string, metadata?: array} $payload
     */
    public function initializeTransaction(array $payload): Response
    {
        return Http::withToken($this->secretKey)
            ->acceptJson()
            ->post("{$this->baseUrl}/transaction/initialize", $payload);
    }
}
