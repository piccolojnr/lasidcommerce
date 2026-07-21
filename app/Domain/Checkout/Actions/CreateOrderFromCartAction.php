<?php

namespace App\Domain\Checkout\Actions;

use App\Domain\Checkout\Exceptions\CheckoutException;
use App\Domain\Checkout\Services\OrderNumberGenerator;
use App\Domain\Inventory\Exceptions\InsufficientStockException;
use App\Domain\Inventory\Services\StockReservationService;
use App\Domain\Notification\Services\CustomerNotificationService;
use App\Domain\Notification\Services\InternalNotificationService;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\ShippingMethod;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateOrderFromCartAction
{
    public function __construct(
        private PreviewCheckoutAction $previewAction,
        private OrderNumberGenerator $numberGenerator,
        private CustomerNotificationService $notificationService,
        private InternalNotificationService $internalNotificationService,
        private StockReservationService $stockReservationService,
    ) {}

    /**
     * @throws CheckoutException
     */
    public function execute(
        Cart $cart,
        Address $address,
        ShippingMethod $shippingMethod,
        User $user,
        ?string $notes = null,
        ?string $deliveryNotes = null,
    ): Order {
        // Reuse preview for validation + totals
        $preview = $this->previewAction->execute($cart, $address, $shippingMethod);

        try {
            $order = DB::transaction(function () use ($preview, $cart, $address, $shippingMethod, $user, $notes, $deliveryNotes) {
                $this->stockReservationService->reserveCart($cart);

                $zone = $preview['shipping_zone'];

                $order = Order::create([
                    'order_number' => $this->numberGenerator->generate(),
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'phone' => $user->phone ?? null,
                    'status' => 'pending',
                    'payment_status' => 'unpaid',
                    'fulfillment_status' => 'unfulfilled',
                    'currency_code' => $cart->currency_code,
                    'subtotal_amount' => $preview['subtotal_amount'],
                    'discount_amount' => $preview['discount_amount'],
                    'tax_amount' => $preview['tax_amount'],
                    'shipping_amount' => $preview['shipping_amount'],
                    'total_amount' => $preview['total_amount'],
                    'shipping_zone_id' => $zone->id,
                    'shipping_method_id' => $shippingMethod->id,
                    'shipping_zone_name' => $zone->name,
                    'shipping_method_name' => $shippingMethod->name,
                    'notes' => $notes,
                    'delivery_notes' => $deliveryNotes,
                    'placed_at' => now(),
                ]);

                // Ensure variant option values are loaded so the snapshot is complete
                $cart->loadMissing('cartItems.productVariant.optionValues.optionType');

                foreach ($cart->cartItems as $item) {
                    // Build a richer snapshot so we have full context if product/variant is deleted later
                    $variantSnapshot = null;
                    if ($item->product_variant_id !== null) {
                        $variant = $item->productVariant;
                        $optionValues = $variant?->relationLoaded('optionValues')
                            ? $variant->optionValues->map(fn ($ov) => [
                                'id' => $ov->id,
                                'value' => $ov->value,
                                'option_type_id' => $ov->option_type_id,
                                'option_type_name' => $ov->optionType?->name,
                            ])->values()->all()
                            : [];

                        $variantSnapshot = [
                            'id' => $item->product_variant_id,
                            'name' => $item->variant_name_snapshot,
                            'sku' => $item->sku_snapshot,
                            'option_values' => $optionValues,
                        ];
                    }

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $item->product_id,
                        'product_variant_id' => $item->product_variant_id,
                        'product_name' => $item->product_name_snapshot,
                        'variant_name' => $item->variant_name_snapshot,
                        'sku' => $item->sku_snapshot,
                        'unit_price' => $item->unit_price,
                        'quantity' => $item->quantity,
                        'discount_amount' => 0,
                        'tax_amount' => 0,
                        'line_total' => $item->line_total,
                        'product_snapshot_json' => [
                            'id' => $item->product_id,
                            'name' => $item->product_name_snapshot,
                            'sku' => $item->sku_snapshot,
                            'variant' => $variantSnapshot,
                        ],
                    ]);
                }

                OrderAddress::create([
                    'order_id' => $order->id,
                    'type' => $address->type,
                    'name' => $address->name,
                    'phone' => $address->phone,
                    'country' => $address->country,
                    'region' => $address->region,
                    'city' => $address->city,
                    'district' => $address->district,
                    'address_line_1' => $address->address_line_1,
                    'address_line_2' => $address->address_line_2,
                    'landmark' => $address->landmark,
                    'postal_code' => $address->postal_code,
                ]);

                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'from_status' => null,
                    'to_status' => 'pending',
                    'note' => 'Order placed.',
                    'changed_by' => $user->id,
                ]);

                $cart->update(['status' => 'converted']);

                return $order->load('orderItems.product.media', 'orderAddresses', 'orderStatusHistories');
            });
        } catch (InsufficientStockException $e) {
            throw new CheckoutException($e->getMessage());
        }

        $this->notificationService->sendOrderPlaced($order);
        $this->internalNotificationService->sendOrderPlaced($order);

        return $order;
    }
}
