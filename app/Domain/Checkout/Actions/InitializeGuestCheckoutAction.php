<?php

namespace App\Domain\Checkout\Actions;

use App\Domain\Addresses\Actions\CreateAddressAction;
use App\Domain\Auth\Actions\RequestCustomerMagicLinkAction;
use App\Domain\Auth\Actions\ResolveStorefrontCustomerFromEmailAction;
use App\Domain\Auth\DTOs\RequestCustomerMagicLinkData;
use App\Domain\Cart\Actions\MergeGuestCartAction;
use App\Domain\Checkout\Exceptions\CheckoutException;
use App\Domain\Notification\Services\CustomerNotificationService;
use App\Domain\Notification\Services\InternalNotificationService;
use App\Models\Cart;
use App\Models\Order;
use App\Models\ShippingMethod;
use App\Models\User;

class InitializeGuestCheckoutAction
{
    public function __construct(
        private MergeGuestCartAction $mergeGuestCartAction,
        private ResolveStorefrontCustomerFromEmailAction $resolveCustomerAction,
        private CreateAddressAction $createAddressAction,
        private InitializeCheckoutAction $initializeCheckoutAction,
        private RequestCustomerMagicLinkAction $requestMagicLinkAction,
        private CustomerNotificationService $customerNotificationService,
        private InternalNotificationService $internalNotificationService,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     * @return array{user: User, was_created: bool, result: array{order: Order, payment: array|null, payment_error: string|null}}
     *
     * @throws CheckoutException
     */
    public function execute(array $payload, ?string $cartToken): array
    {
        $guestCart = $this->resolveGuestCart($cartToken);
        $resolved = $this->resolveCustomerAction->execute(
            email: $payload['email'],
            name: $payload['name'],
            phone: $payload['phone'] ?? null,
        );

        $user = $resolved['user'];
        $checkoutCart = $this->mergeGuestCartAction->execute($user, $guestCart->session_id) ?? $guestCart;

        if ($checkoutCart->user_id === null) {
            $checkoutCart->update(['user_id' => $user->id]);
            $checkoutCart = $checkoutCart->fresh('cartItems');
        }

        $address = $this->createAddressAction->execute($user, [
            'type' => 'shipping',
            'name' => $payload['name'],
            'phone' => $payload['phone'] ?? null,
            'country' => $payload['country'] ?? 'GH',
            'region' => $payload['region'] ?? null,
            'city' => $payload['city'] ?? null,
            'district' => $payload['district'] ?? null,
            'address_line_1' => $payload['address_line_1'],
            'address_line_2' => $payload['address_line_2'] ?? null,
            'landmark' => $payload['landmark'] ?? null,
            'postal_code' => $payload['postal_code'] ?? null,
            'is_default' => true,
            'shipping_zone_id' => $payload['shipping_zone_id'] ?? null,
            'shipping_zone_area_id' => $payload['shipping_zone_area_id'] ?? null,
        ]);

        $method = ShippingMethod::find($payload['shipping_method_id']);

        $result = $this->initializeCheckoutAction->execute(
            cart: $checkoutCart,
            address: $address,
            shippingMethod: $method,
            user: $user,
            paymentProvider: $payload['payment_provider'],
            notes: $payload['notes'] ?? null,
            deliveryNotes: $payload['delivery_notes'] ?? null,
            couponCode: $payload['coupon_code'] ?? null,
        );

        $this->requestMagicLinkAction->execute(new RequestCustomerMagicLinkData(
            email: $user->email,
            cartToken: null,
            redirectTo: config('storefront.orders_path'),
        ));

        if ($resolved['was_created']) {
            $this->customerNotificationService->sendWelcome($user);
            $this->internalNotificationService->sendNewCustomer($user);
        }

        return [
            'user' => $user,
            'was_created' => $resolved['was_created'],
            'result' => $result,
        ];
    }

    /**
     * @throws CheckoutException
     */
    private function resolveGuestCart(?string $cartToken): Cart
    {
        if ($cartToken === null || $cartToken === '') {
            throw new CheckoutException('Cart is empty.');
        }

        $cart = Cart::query()
            ->active()
            ->whereNull('user_id')
            ->where('session_id', $cartToken)
            ->with('cartItems')
            ->latest()
            ->first();

        if ($cart === null || $cart->cartItems->isEmpty()) {
            throw new CheckoutException('Cart is empty.');
        }

        return $cart;
    }
}
