<?php

namespace App\Domain\Checkout\Actions;

use App\Domain\Cart\Services\CartItemValidator;
use App\Domain\Checkout\Exceptions\CheckoutException;
use App\Domain\Shipping\DTOs\ShippingAddressData;
use App\Domain\Shipping\Services\ShippingFeeCalculator;
use App\Domain\Shipping\Services\ShippingZoneResolver;
use App\Models\Address;
use App\Models\Cart;
use App\Models\ShippingMethod;
use App\Models\ShippingZone;

class PreviewCheckoutAction
{
    public function __construct(
        private ShippingZoneResolver $zoneResolver,
        private ShippingFeeCalculator $feeCalculator,
        private CartItemValidator $itemValidator,
    ) {}

    /**
     * @throws CheckoutException
     */
    public function execute(Cart $cart, Address $address, ShippingMethod $shippingMethod): array
    {
        $cart->loadMissing('cartItems.product', 'cartItems.productVariant');

        if ($cart->cartItems->isEmpty()) {
            throw new CheckoutException('Cart is empty.');
        }

        $zone = $address->shipping_zone_id
            ? ShippingZone::find($address->shipping_zone_id)
            : $this->zoneResolver->resolve(new ShippingAddressData(
                country: $address->country,
                region: $address->region,
                city: $address->city,
                district: $address->district,
            ));

        if ($zone === null) {
            throw new CheckoutException('No shipping zone available for your address.');
        }

        if (! $shippingMethod->is_active || ! $zone->shippingMethods()->whereKey($shippingMethod->getKey())->exists()) {
            throw new CheckoutException('Selected shipping method is not available for your address.');
        }

        foreach ($cart->cartItems as $item) {
            try {
                $this->itemValidator->validate($item->product, $item->productVariant ?? null);
            } catch (\RuntimeException $e) {
                throw new CheckoutException("Cart item \"{$item->product_name_snapshot}\" is no longer available: {$e->getMessage()}");
            }
        }

        $subtotal = $cart->cartItems->sum('line_total');
        $shipping = $this->feeCalculator->calculate($shippingMethod);
        $discount = 0;
        $tax = 0;
        $total = $subtotal + $shipping - $discount + $tax;

        return [
            'cart' => $cart,
            'address' => $address,
            'shipping_zone' => $zone,
            'shipping_method' => $shippingMethod,
            'subtotal_amount' => $subtotal,
            'discount_amount' => $discount,
            'tax_amount' => $tax,
            'shipping_amount' => $shipping,
            'total_amount' => $total,
        ];
    }
}
