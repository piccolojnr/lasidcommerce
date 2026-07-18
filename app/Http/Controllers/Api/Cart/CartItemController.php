<?php

namespace App\Http\Controllers\Api\Cart;

use App\Domain\Cart\Actions\AddCartItemAction;
use App\Domain\Cart\Actions\GetOrCreateCartAction;
use App\Domain\Cart\Actions\RemoveCartItemAction;
use App\Domain\Cart\Actions\UpdateCartItemAction;
use App\Domain\Cart\Exceptions\CartException;
use App\Domain\Cart\Services\CartTotalsCalculator;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreCartItemRequest;
use App\Http\Requests\Api\UpdateCartItemRequest;
use App\Http\Resources\Api\Cart\CartResource;
use App\Models\CartItem;
use App\Support\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CartItemController extends Controller
{
    public function __construct(
        private GetOrCreateCartAction $getOrCreate,
        private AddCartItemAction $addItem,
        private UpdateCartItemAction $updateItem,
        private RemoveCartItemAction $removeItem,
        private CartTotalsCalculator $totals,
    ) {}

    public function store(StoreCartItemRequest $request): JsonResponse
    {
        $cart = $this->getOrCreate->execute(
            user: $request->user(),
            cartToken: $request->header('X-Cart-Token'),
        );

        try {
            $this->addItem->execute(
                $cart,
                (int) $request->product_id,
                $request->product_variant_id ? (int) $request->product_variant_id : null,
                (int) $request->quantity,
            );
        } catch (CartException $e) {
            return ApiResponse::error($e->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->totals->recalculate($cart);
        $cart->load('cartItems.product.media');

        return ApiResponse::created(new CartResource($cart));
    }

    public function update(UpdateCartItemRequest $request, CartItem $cartItem): JsonResponse
    {
        $cart = $this->getOrCreate->execute(
            user: $request->user(),
            cartToken: $request->header('X-Cart-Token'),
        );

        if ($cartItem->cart_id !== $cart->id) {
            return ApiResponse::error('Cart item not found', [], Response::HTTP_NOT_FOUND);
        }

        try {
            $this->updateItem->execute($cartItem, (int) $request->quantity);
        } catch (CartException $e) {
            return ApiResponse::error($e->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->totals->recalculate($cart);
        $cart->load('cartItems.product.media');

        return ApiResponse::success(new CartResource($cart));
    }

    public function destroy(Request $request, CartItem $cartItem): JsonResponse
    {
        $cart = $this->getOrCreate->execute(
            user: $request->user(),
            cartToken: $request->header('X-Cart-Token'),
        );

        if ($cartItem->cart_id !== $cart->id) {
            return ApiResponse::error('Cart item not found', [], Response::HTTP_NOT_FOUND);
        }

        $this->removeItem->execute($cartItem);
        $this->totals->recalculate($cart);
        $cart->load('cartItems.product.media');

        return ApiResponse::success(new CartResource($cart));
    }
}
