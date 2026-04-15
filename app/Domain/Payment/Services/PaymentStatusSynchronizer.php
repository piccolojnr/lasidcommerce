<?php

namespace App\Domain\Payment\Services;

use App\Models\OrderStatusHistory;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class PaymentStatusSynchronizer
{
    public function syncSuccess(Payment $payment, array $paystackData): void
    {
        DB::transaction(function () use ($payment, $paystackData) {
            $payment->update([
                'status'                  => 'paid',
                'provider_transaction_id' => isset($paystackData['id'])
                    ? (string) $paystackData['id']
                    : null,
                'paid_at'          => now(),
                'gateway_response' => $paystackData['gateway_response'] ?? null,
                'raw_payload_json' => $paystackData,
            ]);

            $order      = $payment->order;
            $fromStatus = $order->status;
            $updateData = ['payment_status' => 'paid'];

            if ($order->status === 'pending') {
                $updateData['status'] = 'confirmed';
            }

            $order->update($updateData);

            if (($updateData['status'] ?? null) === 'confirmed') {
                OrderStatusHistory::create([
                    'order_id'    => $order->id,
                    'from_status' => $fromStatus,
                    'to_status'   => 'confirmed',
                    'note'        => 'Payment confirmed via Paystack webhook.',
                    'changed_by'  => null,
                ]);
            }
        });
    }
}
