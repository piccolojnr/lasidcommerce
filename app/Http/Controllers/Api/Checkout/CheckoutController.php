<?php

namespace App\Http\Controllers\Api\Checkout;

use App\Domain\Cart\Actions\GetOrCreateCartAction;
use App\Domain\Checkout\Actions\PreviewCheckoutAction;
use App\Domain\Checkout\Exceptions\CheckoutException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\InitializeCheckoutRequest;
use App\Http\Requests\Api\PreviewCheckoutRequest;
use App\Http\Requests\Api\ResolveShippingMethodsRequest;
use App\Http\Resources\Api\Checkout\CheckoutPreviewResource;
use App\Models\Address;
use App\Models\ShippingMethod;
use App\Support\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class CheckoutController extends Controller
{
    public function __construct(
        private GetOrCreateCartAction $getOrCreateCart,
        private PreviewCheckoutAction $previewAction,
    ) {}

    public function resolveShippingMethods(ResolveShippingMethodsRequest $request): JsonResponse
    {
        return ApiResponse::success([], 'Shipping methods placeholder');
    }

    public function preview(PreviewCheckoutRequest $request): JsonResponse
    {
        $cart    = $this->getOrCreateCart->execute(
            user:      $request->user(),
            cartToken: $request->header('X-Cart-Token'),
        );

        $address = Address::find($request->address_id);

        if ($address === null || $address->user_id !== $request->user()?->id) {
            return ApiResponse::error('Address not found.', [], Response::HTTP_NOT_FOUND);
        }

        $method = ShippingMethod::find($request->shipping_method_id);

        try {
            $preview = $this->previewAction->execute($cart, $address, $method);
        } catch (CheckoutException $e) {
            return ApiResponse::error($e->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return ApiResponse::success(new CheckoutPreviewResource($preview));
    }

    public function initialize(InitializeCheckoutRequest $request): JsonResponse
    {
        return ApiResponse::created([], 'Checkout initialize placeholder');
    }
}
