<?php

namespace App\Http\Controllers\Api\Checkout;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\InitializeCheckoutRequest;
use App\Http\Requests\Api\PreviewCheckoutRequest;
use App\Http\Requests\Api\ResolveShippingMethodsRequest;
use App\Support\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

class CheckoutController extends Controller
{
    public function resolveShippingMethods(ResolveShippingMethodsRequest $request): JsonResponse
    {
        return ApiResponse::success([], 'Shipping methods placeholder');
    }

    public function preview(PreviewCheckoutRequest $request): JsonResponse
    {
        return ApiResponse::success([], 'Checkout preview placeholder');
    }

    public function initialize(InitializeCheckoutRequest $request): JsonResponse
    {
        return ApiResponse::created([], 'Checkout initialize placeholder');
    }
}
