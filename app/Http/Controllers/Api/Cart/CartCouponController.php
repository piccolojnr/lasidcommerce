<?php

namespace App\Http\Controllers\Api\Cart;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ApplyCouponRequest;
use App\Support\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

class CartCouponController extends Controller
{
    public function store(ApplyCouponRequest $request): JsonResponse
    {
        return ApiResponse::success([], 'Cart coupon apply placeholder');
    }

    public function destroy(): JsonResponse
    {
        return ApiResponse::success([], 'Cart coupon remove placeholder');
    }
}
