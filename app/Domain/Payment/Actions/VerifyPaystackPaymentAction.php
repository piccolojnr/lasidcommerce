<?php

namespace App\Domain\Payment\Actions;

use App\Domain\Payment\Services\PaymentStatusSynchronizer;
use App\Domain\Payment\Services\PaystackClient;
use App\Models\Payment;

class VerifyPaystackPaymentAction
{
    public function __construct(
        private PaystackClient $client,
        private PaymentStatusSynchronizer $synchronizer,
    ) {}

    /**
     * Look up a payment by reference, verify with Paystack if still pending,
     * sync the order if confirmed. Returns the payment (with order loaded) or null.
     */
    public function execute(string $reference): ?Payment
    {
        $payment = Payment::with('order.orderAddresses')
            ->where('reference', $reference)
            ->where('provider', 'paystack')
            ->first();

        if ($payment === null) {
            return null;
        }

        // Already confirmed — nothing to do
        if ($payment->isSuccessful()) {
            return $payment;
        }

        // Still pending — ask Paystack directly (handles race condition with webhook)
        $response = $this->client->verifyTransaction($reference);

        if (! $response->successful() || ! $response->json('status')) {
            return $payment;
        }

        $data = $response->json('data') ?? [];
        $status = $data['status'] ?? null;

        if ($status === 'success') {
            $this->synchronizer->syncSuccess($payment, $data);
            $payment->refresh()->load('order.orderAddresses');
        }

        return $payment;
    }
}
