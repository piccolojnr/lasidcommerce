<?php

namespace App\Domain\Payment\Actions;

use App\Domain\Payment\Exceptions\PaymentException;
use App\Domain\Payment\Services\PaymentSignatureVerifier;
use App\Domain\Payment\Services\PaymentStatusSynchronizer;
use App\Domain\Payment\Services\PaymentWebhookLogger;
use App\Models\Payment;
use App\Models\PaymentWebhookLog;

class HandlePaystackWebhookAction
{
    public function __construct(
        private PaymentSignatureVerifier  $signatureVerifier,
        private PaymentWebhookLogger      $webhookLogger,
        private PaymentStatusSynchronizer $synchronizer,
    ) {}

    /**
     * @throws PaymentException  on invalid signature
     */
    public function execute(string $rawPayload, string $signature): void
    {
        $secret = (string) config('services.paystack.secret_key', '');

        if (! $this->signatureVerifier->verify($signature, $rawPayload, $secret)) {
            throw new PaymentException('Invalid webhook signature.');
        }

        $data      = json_decode($rawPayload, true) ?? [];
        $event     = $data['event'] ?? 'unknown';
        $reference = $data['data']['reference'] ?? null;

        $log = $this->webhookLogger->log(
            provider:   'paystack',
            eventType:  $event,
            reference:  $reference,
            rawPayload: $rawPayload,
            signature:  $signature,
        );

        try {
            if ($event === 'charge.success') {
                $this->handleChargeSuccess($data['data'] ?? [], $log);
            }
            // All other events are acknowledged but not acted upon

            $this->webhookLogger->markProcessed($log);
        } catch (\Throwable $e) {
            $this->webhookLogger->markFailed($log, $e->getMessage());
            throw $e;
        }
    }

    private function handleChargeSuccess(array $paystackData, PaymentWebhookLog $log): void
    {
        $reference = $paystackData['reference'] ?? null;

        if ($reference === null) {
            $this->webhookLogger->markProcessed($log, 'Missing reference in payload — ignored.');
            return;
        }

        $payment = Payment::where('reference', $reference)->first();

        if ($payment === null) {
            $this->webhookLogger->markProcessed($log, 'Unknown reference — ignored.');
            return;
        }

        if ($payment->isSuccessful()) {
            // Idempotency: already processed, nothing to do
            return;
        }

        $this->synchronizer->syncSuccess($payment, $paystackData);
    }
}
