<?php

namespace App\Domain\Checkout\Actions;

use App\Domain\Checkout\Exceptions\CheckoutException;
use App\Domain\Payment\Actions\InitializePaystackPaymentAction;
use App\Domain\Payment\Exceptions\PaymentException;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Order;
use App\Models\ShippingMethod;
use App\Models\User;

class InitializeCheckoutAction
{
    public function __construct(
        private CreateOrderFromCartAction $createOrderAction,
        private InitializePaystackPaymentAction $initializePaymentAction,
    ) {}

    /**
     * @return array{order: Order, payment: array|null, payment_error: string|null}
     *
     * @throws CheckoutException
     */
    public function execute(
        Cart $cart,
        Address $address,
        ShippingMethod $shippingMethod,
        User $user,
        string $paymentProvider,
        ?string $notes = null,
        ?string $deliveryNotes = null,
        ?string $couponCode = null,
    ): array {
        if ($couponCode !== null) {
            throw new CheckoutException('Coupon codes are not supported in checkout initialization yet.');
        }

        if ($paymentProvider !== 'paystack') {
            throw new CheckoutException('Selected payment provider is not supported.');
        }

        $order = $this->createOrderAction->execute(
            cart: $cart,
            address: $address,
            shippingMethod: $shippingMethod,
            user: $user,
            notes: $notes,
            deliveryNotes: $deliveryNotes,
        );

        try {
            $payment = $this->initializePaymentAction->execute($order, $user);
        } catch (PaymentException $e) {
            return [
                'order' => $order,
                'payment' => null,
                'payment_error' => $e->getMessage(),
            ];
        }

        return [
            'order' => $order,
            'payment' => $payment,
            'payment_error' => null,
        ];
    }
}
