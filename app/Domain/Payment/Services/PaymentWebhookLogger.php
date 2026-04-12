<?php

namespace App\Domain\Payment\Services;

use App\Models\PaymentWebhookLog;

class PaymentWebhookLogger
{
    public function log(
        string  $provider,
        string  $eventType,
        ?string $reference,
        string  $rawPayload,
        ?string $signature,
    ): PaymentWebhookLog {
        return PaymentWebhookLog::create([
            'provider'     => $provider,
            'event_type'   => $eventType,
            'reference'    => $reference,
            'payload_json' => $rawPayload,
            'signature'    => $signature,
            'processed'    => false,
        ]);
    }

    public function markProcessed(PaymentWebhookLog $log, ?string $note = null): void
    {
        $log->update([
            'processed'     => true,
            'processed_at'  => now(),
            'error_message' => $note,
        ]);
    }

    public function markFailed(PaymentWebhookLog $log, string $errorMessage): void
    {
        $log->update([
            'processed'     => false,
            'error_message' => $errorMessage,
        ]);
    }
}
