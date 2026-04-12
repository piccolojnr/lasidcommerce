<?php

namespace App\Http\Controllers\Api\Payments;

use App\Domain\Payment\Actions\InitializePaystackPaymentAction;
use App\Domain\Payment\Exceptions\PaymentException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\InitializePaymentRequest;
use App\Models\Order;
use App\Support\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class PaymentController extends Controller
{
    public function __construct(
        private InitializePaystackPaymentAction $initializeAction,
    ) {}

    public function initialize(InitializePaymentRequest $request): JsonResponse
    {
        $order = Order::find($request->order_id);

        if ($order->user_id !== $request->user()->id) {
            return ApiResponse::error('Order not found.', [], Response::HTTP_NOT_FOUND);
        }

        if ($order->payment_status === 'paid') {
            return ApiResponse::error('Order is already paid.', [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($order->status === 'cancelled') {
            return ApiResponse::error('Order is cancelled.', [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($order->total_amount <= 0) {
            return ApiResponse::error('Order total must be greater than zero.', [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $result = $this->initializeAction->execute($order, $request->user());
        } catch (PaymentException $e) {
            return ApiResponse::error($e->getMessage(), [], Response::HTTP_BAD_GATEWAY);
        }

        return ApiResponse::success([
            'authorization_url' => $result['authorization_url'],
            'access_code'       => $result['access_code'],
            'reference'         => $result['reference'],
        ]);
    }
}
