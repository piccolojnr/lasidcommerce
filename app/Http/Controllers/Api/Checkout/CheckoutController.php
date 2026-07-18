<?php

namespace App\Http\Controllers\Api\Checkout;

use App\Domain\Cart\Actions\GetOrCreateCartAction;
use App\Domain\Checkout\Actions\CreateOrderFromCartAction;
use App\Domain\Checkout\Actions\InitializeCheckoutAction;
use App\Domain\Checkout\Actions\InitializeGuestCheckoutAction;
use App\Domain\Checkout\Actions\PreviewCheckoutAction;
use App\Domain\Checkout\Exceptions\CheckoutException;
use App\Domain\Shipping\Actions\ResolveShippingMethodsAction;
use App\Domain\Shipping\DTOs\ShippingAddressData;
use App\Domain\Shipping\Services\ShippingFeeCalculator;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CreateOrderRequest;
use App\Http\Requests\Api\InitializeCheckoutRequest;
use App\Http\Requests\Api\InitializeGuestCheckoutRequest;
use App\Http\Requests\Api\PreviewCheckoutRequest;
use App\Http\Requests\Api\ResolveShippingMethodsRequest;
use App\Http\Resources\Api\Checkout\CheckoutPreviewResource;
use App\Http\Resources\Api\Checkout\OrderResource;
use App\Models\Address;
use App\Models\Cart;
use App\Models\ShippingMethod;
use App\Models\ShippingZone;
use App\Support\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckoutController extends Controller
{
    public function __construct(
        private GetOrCreateCartAction $getOrCreateCart,
        private PreviewCheckoutAction $previewAction,
        private CreateOrderFromCartAction $createOrderAction,
        private ResolveShippingMethodsAction $resolveShippingMethodsAction,
        private InitializeCheckoutAction $initializeCheckoutAction,
        private InitializeGuestCheckoutAction $initializeGuestCheckoutAction,
        private ShippingFeeCalculator $feeCalculator,
    ) {}

    public function resolveShippingMethods(ResolveShippingMethodsRequest $request): JsonResponse
    {
        if ($request->filled('shipping_zone_id')) {
            $zone = ShippingZone::active()
                ->with(['shippingMethods' => fn ($q) => $q->active()->orderBy('name')])
                ->find($request->integer('shipping_zone_id'));

            $resolved = $zone ? [
                'shipping_zone' => [
                    'id' => $zone->id,
                    'name' => $zone->name,
                    'code' => $zone->code,
                ],
                'shipping_methods' => $zone->shippingMethods
                    ->map(fn ($method) => [
                        'id' => $method->id,
                        'name' => $method->name,
                        'code' => $method->code,
                        'method_type' => $method->method_type,
                        'price_type' => $method->price_type,
                        'flat_rate_amount' => $method->flat_rate_amount,
                        'shipping_amount' => $this->feeCalculator->calculate($method),
                        'min_delivery_days' => $method->min_delivery_days,
                        'max_delivery_days' => $method->max_delivery_days,
                        'description' => $method->description,
                    ])
                    ->values()
                    ->all(),
            ] : ['shipping_zone' => null, 'shipping_methods' => []];
        } else {
            $resolved = $this->resolveShippingMethodsAction->execute(new ShippingAddressData(
                country: $request->country,
                region: $request->region,
                city: $request->city,
            ));
        }

        $cart = null;
        if ($request->filled('cart_id')) {
            $cart = Cart::query()->active()->find($request->integer('cart_id'));
        }

        return ApiResponse::success([
            'shipping_zone' => $resolved['shipping_zone'],
            'shipping_methods' => $resolved['shipping_methods'],
            'cart_id' => $cart?->id,
        ]);
    }

    public function preview(PreviewCheckoutRequest $request): JsonResponse
    {
        $cart = $this->getOrCreateCart->execute(
            user: $request->user(),
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

    public function createOrder(CreateOrderRequest $request): JsonResponse
    {
        $cart = $this->getOrCreateCart->execute(
            user: $request->user(),
            cartToken: $request->header('X-Cart-Token'),
        );

        $address = Address::find($request->address_id);

        if ($address === null || $address->user_id !== $request->user()->id) {
            return ApiResponse::error('Address not found.', [], Response::HTTP_NOT_FOUND);
        }

        $method = ShippingMethod::find($request->shipping_method_id);

        try {
            $order = $this->createOrderAction->execute(
                cart: $cart,
                address: $address,
                shippingMethod: $method,
                user: $request->user(),
                notes: $request->notes,
                deliveryNotes: $request->delivery_notes,
            );
        } catch (CheckoutException $e) {
            return ApiResponse::error($e->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return ApiResponse::created(new OrderResource($order));
    }

    public function initialize(InitializeCheckoutRequest $request): JsonResponse
    {
        $cart = $this->getOrCreateCart->execute(
            user: $request->user(),
            cartToken: $request->header('X-Cart-Token'),
        );

        $address = Address::find($request->address_id);

        if ($address === null || $address->user_id !== $request->user()->id) {
            return ApiResponse::error('Address not found.', [], Response::HTTP_NOT_FOUND);
        }

        $method = ShippingMethod::find($request->shipping_method_id);

        try {
            $result = $this->initializeCheckoutAction->execute(
                cart: $cart,
                address: $address,
                shippingMethod: $method,
                user: $request->user(),
                paymentProvider: $request->payment_provider,
                notes: $request->notes,
                deliveryNotes: $request->delivery_notes,
                couponCode: $request->coupon_code,
            );
        } catch (CheckoutException $e) {
            return ApiResponse::error($e->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($result['payment_error'] !== null) {
            return ApiResponse::error(
                'Checkout was created, but payment initialization failed.',
                [
                    'order_id' => $result['order']->id,
                    'payment' => $result['payment_error'],
                ],
                Response::HTTP_BAD_GATEWAY,
            );
        }

        return ApiResponse::created([
            'order' => new OrderResource($result['order']),
            'payment' => [
                'provider' => $request->payment_provider,
                'authorization_url' => $result['payment']['authorization_url'],
                'access_code' => $result['payment']['access_code'],
                'reference' => $result['payment']['reference'],
            ],
        ]);
    }

    public function initializeGuest(InitializeGuestCheckoutRequest $request): JsonResponse
    {
        try {
            $guestCheckout = $this->initializeGuestCheckoutAction->execute(
                payload: $request->validated(),
                cartToken: $request->header('X-Cart-Token'),
            );
        } catch (CheckoutException $e) {
            return ApiResponse::error($e->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        Auth::guard('customer')->login($guestCheckout['user']);
        $request->session()->regenerate();

        $result = $guestCheckout['result'];

        if ($result['payment_error'] !== null) {
            return ApiResponse::error(
                'Checkout was created, but payment initialization failed.',
                [
                    'order_id' => $result['order']->id,
                    'payment' => $result['payment_error'],
                ],
                Response::HTTP_BAD_GATEWAY,
            );
        }

        return ApiResponse::created([
            'order' => new OrderResource($result['order']),
            'payment' => [
                'provider' => $request->payment_provider,
                'authorization_url' => $result['payment']['authorization_url'],
                'access_code' => $result['payment']['access_code'],
                'reference' => $result['payment']['reference'],
            ],
        ]);
    }
}
