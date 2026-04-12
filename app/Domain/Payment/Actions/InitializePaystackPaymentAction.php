<?php

namespace App\Domain\Payment\Actions;

use App\Domain\Payment\Exceptions\PaymentException;
use App\Domain\Payment\Services\PaymentReferenceGenerator;
use App\Domain\Payment\Services\PaystackClient;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;

class InitializePaystackPaymentAction
{
    public function __construct(
        private PaystackClient $client,
        private PaymentReferenceGenerator $referenceGenerator,
    ) {}

    /**
     * @return array{authorization_url: string, access_code: string, reference: string, payment: Payment}
     * @throws PaymentException
     */
    public function execute(Order $order, User $user): array
    {
        $reference = $this->referenceGenerator->generate();

        $payload = [
            'email'        => $order->email,
            'amount'       => $order->total_amount,
            'reference'    => $reference,
            'callback_url' => config('services.paystack.callback_url'),
            'metadata'     => [
                'order_id'     => $order->id,
                'order_number' => $order->order_number,
                'user_id'      => $user->id,
            ],
        ];

        $response = $this->client->initializeTransaction($payload);

        if (! $response->successful() || ! $response->json('status')) {
            throw new PaymentException(
                $response->json('message') ?? 'Paystack initialization failed.'
            );
        }

        $data = $response->json('data');

        $payment = Payment::create([
            'order_id'      => $order->id,
            'user_id'       => $user->id,
            'provider'      => 'paystack',
            'reference'     => $reference,
            'status'        => 'pending',
            'amount'        => $order->total_amount,
            'currency_code' => $order->currency_code,
            'raw_payload_json' => $data,
        ]);

        return [
            'authorization_url' => $data['authorization_url'],
            'access_code'       => $data['access_code'],
            'reference'         => $data['reference'],
            'payment'           => $payment,
        ];
    }
}
