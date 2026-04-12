<?php

namespace App\Http\Controllers\Webhooks\Paystack;

use App\Domain\Payment\Actions\HandlePaystackWebhookAction;
use App\Domain\Payment\Exceptions\PaymentException;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PaystackWebhookController extends Controller
{
    public function __construct(
        private HandlePaystackWebhookAction $handleAction,
    ) {}

    public function handle(Request $request): JsonResponse
    {
        $rawPayload = $request->getContent();
        $signature  = $request->header('X-Paystack-Signature', '');

        try {
            $this->handleAction->execute($rawPayload, $signature);
        } catch (PaymentException $e) {
            // Invalid signature — reject so Paystack knows not to retry with same payload
            return response()->json(['message' => 'Unauthorized.'], Response::HTTP_UNAUTHORIZED);
        }

        return response()->json(['message' => 'Webhook received.'], Response::HTTP_OK);
    }
}
