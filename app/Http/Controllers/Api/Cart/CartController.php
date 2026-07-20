<?php

namespace App\Http\Controllers\Api\Cart;

use App\Domain\Cart\Actions\GetOrCreateCartAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Cart\CartResource;
use App\Support\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(
        private GetOrCreateCartAction $getOrCreateCartAction,
    ) {}

    public function show(Request $request): JsonResponse
    {
        $cart = $this->getOrCreateCartAction->execute(
            user: $request->user(),
            cartToken: $request->header('X-Cart-Token'),
        );

        $cart->load('cartItems.product.media', 'cartItems.productVariant.optionValues.optionType');

        return ApiResponse::success(new CartResource($cart));
    }
}
