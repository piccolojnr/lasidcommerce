<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Shipment;
use App\Models\ShipmentItem;
use App\Models\ShippingMethod;
use App\Models\ShippingZone;
use App\Models\User;
use App\Models\WarehouseLocation;
use Illuminate\Database\Seeder;

class DemoOrdersSeeder extends Seeder
{
    public function run(): void
    {
        $ordersManager = User::query()->where('email', 'orders.manager@example.com')->first()
            ?? User::query()->where('email', 'admin@example.com')->first();

        $warehouse = WarehouseLocation::query()->where('code', 'ACCRA-HQ')->first();
        $kumasiWarehouse = WarehouseLocation::query()->where('code', 'KUMASI-HUB')->first();
        $tamaleWarehouse = WarehouseLocation::query()->where('code', 'TAMALE-HUB')->first();
        $accraZone = ShippingZone::query()->where('code', 'GH-ACCRA')->first();
        $ashantiZone = ShippingZone::query()->where('code', 'GH-ASHANTI')->first();
        $northernZone = ShippingZone::query()->where('code', 'GH-NORTHERN')->first();
        $nationwideZone = ShippingZone::query()->where('code', 'GH-NATIONWIDE')->first();
        $sameDay = ShippingMethod::query()->where('code', 'same-day-accra')->first();
        $nextDayAccra = ShippingMethod::query()->where('code', 'next-day-accra')->first();
        $expressAshanti = ShippingMethod::query()->where('code', 'express-ashanti')->first();
        $standard = ShippingMethod::query()->where('code', 'standard-ghana')->first();
        $northernEconomy = ShippingMethod::query()->where('code', 'northern-economy')->first();
        $pickup = ShippingMethod::query()->where('code', 'pickup-station')->first();

        $productMap = Product::query()
            ->whereIn('sku', [
                'LAS-SNK-001',
                'LAS-SND-002',
                'LAS-BAG-003',
                'LAS-CAP-004',
                'LAS-WLT-005',
                'ADM-DRS-006',
                'ADM-TOP-007',
                'ADB-SHT-008',
                'ADB-TRS-009',
                'KID-UNI-010',
                'KID-PLY-011',
                'LAS-XBD-012',
                'GCC-FRG-013',
                'GCC-SKN-014',
                'NHL-DEC-015',
                'NHL-BTH-016',
            ])
            ->get()
            ->keyBy('sku');

        $customerAma = User::query()->where('email', 'customer.ama@example.com')->first();
        $customerKojo = User::query()->where('email', 'customer.kojo@example.com')->first();
        $customerEfua = User::query()->where('email', 'customer.efua@example.com')->first();
        $customerAbena = User::query()->where('email', 'customer.abena@example.com')->first();
        $customerKweku = User::query()->where('email', 'customer.kweku@example.com')->first();

        // order 1: Processing order with same-day delivery in Accra
        $this->seedOrder(
            orderNumber: 'ORD-DEMO-1001',
            customer: $customerAma,
            actor: $ordersManager,
            zone: $accraZone,
            method: $sameDay,
            warehouse: $warehouse,
            orderAttributes: [
                'status' => 'processing',
                'payment_status' => 'paid',
                'fulfillment_status' => 'unfulfilled',
                'shipping_amount' => 2500,
                'placed_at' => now()->subDays(2),
                'notes' => 'High-priority demo order awaiting final delivery handoff.',
            ],
            address: [
                'name' => 'Ama Mensah',
                'phone' => '+233240000111',
                'country' => 'Ghana',
                'region' => 'Greater Accra',
                'city' => 'Accra',
                'district' => 'Osu',
                'address_line_1' => '18 Osu Ringway',
                'address_line_2' => null,
                'landmark' => 'Near Oxford Street',
                'postal_code' => null,
            ],
            items: [
                ['sku' => 'LAS-SNK-001', 'quantity' => 1],
                ['sku' => 'LAS-BAG-003', 'quantity' => 1],
            ],
            payment: [
                'reference' => 'PAY-DEMO-1001',
                'status' => 'successful',
                'gateway_response' => 'Approved',
                'paid_at' => now()->subDays(2)->addHour(),
            ],
            shipment: [
                'status' => 'packed',
                'carrier_name' => 'Lasid Dispatch',
                'rider_name' => 'Kwesi Rider',
                'rider_phone' => '+233201111001',
                'tracking_number' => 'TRK-DEMO-1001',
                'tracking_url' => 'https://example.com/track/TRK-DEMO-1001',
                'notes' => 'Packed and waiting for rider pickup.',
                'packed_at' => now()->subDay(),
            ],
            statusHistory: [
                ['from_status' => null, 'to_status' => 'pending', 'note' => 'Order created.', 'created_at' => now()->subDays(2)],
                ['from_status' => 'pending', 'to_status' => 'processing', 'note' => 'Payment confirmed and order released to warehouse.', 'created_at' => now()->subDays(2)->addHour()],
            ],
        );

        // order 2: Completed order with nationwide delivery
        $this->seedOrder(
            orderNumber: 'ORD-DEMO-1002',
            customer: $customerKojo,
            actor: $ordersManager,
            zone: $nationwideZone,
            method: $standard,
            warehouse: $warehouse,
            orderAttributes: [
                'status' => 'completed',
                'payment_status' => 'paid',
                'fulfillment_status' => 'fulfilled',
                'shipping_amount' => 5000,
                'placed_at' => now()->subDays(9),
                'completed_at' => now()->subDays(4),
                'notes' => 'Completed nationwide delivery demo.',
            ],
            address: [
                'name' => 'Kojo Asare',
                'phone' => '+233540000112',
                'country' => 'Ghana',
                'region' => 'Ashanti',
                'city' => 'Kumasi',
                'district' => 'Adum',
                'address_line_1' => '45 Adum High Street',
                'address_line_2' => null,
                'landmark' => 'Opposite Central Market',
                'postal_code' => null,
            ],
            items: [
                ['sku' => 'LAS-WLT-005', 'quantity' => 1],
                ['sku' => 'LAS-CAP-004', 'quantity' => 2],
            ],
            payment: [
                'reference' => 'PAY-DEMO-1002',
                'status' => 'paid',
                'gateway_response' => 'Paid',
                'paid_at' => now()->subDays(9)->addMinutes(40),
            ],
            shipment: [
                'status' => 'delivered',
                'carrier_name' => 'Lasid Express',
                'rider_name' => 'Yaw Courier',
                'rider_phone' => '+233201111002',
                'tracking_number' => 'TRK-DEMO-1002',
                'tracking_url' => 'https://example.com/track/TRK-DEMO-1002',
                'notes' => 'Delivered successfully to customer.',
                'packed_at' => now()->subDays(8),
                'shipped_at' => now()->subDays(7),
                'delivered_at' => now()->subDays(4),
            ],
            statusHistory: [
                ['from_status' => null, 'to_status' => 'pending', 'note' => 'Order created.', 'created_at' => now()->subDays(9)],
                ['from_status' => 'pending', 'to_status' => 'processing', 'note' => 'Order approved for fulfillment.', 'created_at' => now()->subDays(9)->addHour()],
                ['from_status' => 'processing', 'to_status' => 'completed', 'note' => 'Delivered and closed.', 'created_at' => now()->subDays(4)],
            ],
        );

        // order 3: Pending order awaiting payment authorization in Accra
        $this->seedOrder(
            orderNumber: 'ORD-DEMO-1003',
            customer: $customerEfua,
            actor: $ordersManager,
            zone: $accraZone,
            method: $sameDay,
            warehouse: $warehouse,
            orderAttributes: [
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'fulfillment_status' => 'unfulfilled',
                'shipping_amount' => 2500,
                'placed_at' => now()->subHours(8),
                'notes' => 'Awaiting customer payment authorization.',
            ],
            address: [
                'name' => 'Efua Nkrumah',
                'phone' => '+233270000113',
                'country' => 'Ghana',
                'region' => 'Greater Accra',
                'city' => 'Accra',
                'district' => 'East Legon',
                'address_line_1' => '7 East Legon Avenue',
                'address_line_2' => null,
                'landmark' => 'Adjiringanor Junction',
                'postal_code' => null,
            ],
            items: [
                ['sku' => 'LAS-SND-002', 'quantity' => 1],
                ['sku' => 'LAS-CAP-004', 'quantity' => 1],
            ],
            payment: [
                'reference' => 'PAY-DEMO-1003',
                'status' => 'pending',
                'gateway_response' => 'Awaiting authorization',
                'paid_at' => null,
            ],
            shipment: null,
            statusHistory: [
                ['from_status' => null, 'to_status' => 'pending', 'note' => 'Order created.', 'created_at' => now()->subHours(8)],
            ],
        );

        $this->seedOrder(
            orderNumber: 'ORD-DEMO-1004',
            customer: $customerAbena,
            actor: $ordersManager,
            zone: $nationwideZone,
            method: $pickup,
            warehouse: $warehouse,
            orderAttributes: [
                'status' => 'confirmed',
                'payment_status' => 'paid',
                'fulfillment_status' => 'unfulfilled',
                'shipping_amount' => 0,
                'placed_at' => now()->subDays(1),
                'notes' => 'Pickup station order confirmed and waiting collection prep.',
            ],
            address: [
                'name' => 'Abena Owusu',
                'phone' => '+233500000114',
                'country' => 'Ghana',
                'region' => 'Western',
                'city' => 'Takoradi',
                'district' => 'Market Circle',
                'address_line_1' => '4 Beach Road',
                'address_line_2' => null,
                'landmark' => 'Near Market Circle',
                'postal_code' => null,
            ],
            items: [
                ['sku' => 'GCC-FRG-013', 'quantity' => 1],
                ['sku' => 'LAS-XBD-012', 'quantity' => 1],
            ],
            payment: [
                'reference' => 'PAY-DEMO-1004',
                'status' => 'paid',
                'gateway_response' => 'Paid',
                'paid_at' => now()->subDay()->addMinutes(25),
            ],
            shipment: null,
            statusHistory: [
                ['from_status' => null, 'to_status' => 'pending', 'note' => 'Order created.', 'created_at' => now()->subDay()],
                ['from_status' => 'pending', 'to_status' => 'confirmed', 'note' => 'Pickup payment cleared and order confirmed.', 'created_at' => now()->subDay()->addMinutes(25)],
            ],
        );

        $this->seedOrder(
            orderNumber: 'ORD-DEMO-1005',
            customer: $customerKweku,
            actor: $ordersManager,
            zone: $northernZone,
            method: $northernEconomy,
            warehouse: $tamaleWarehouse ?? $warehouse,
            orderAttributes: [
                'status' => 'processing',
                'payment_status' => 'paid',
                'fulfillment_status' => 'partially_fulfilled',
                'shipping_amount' => 6500,
                'placed_at' => now()->subDays(3),
                'notes' => 'Northern route order already handed to long-haul dispatch.',
            ],
            address: [
                'name' => 'Kweku Badu',
                'phone' => '+233550000115',
                'country' => 'Ghana',
                'region' => 'Northern',
                'city' => 'Tamale',
                'district' => 'Central',
                'address_line_1' => '16 Central Road',
                'address_line_2' => null,
                'landmark' => 'Close to the stadium',
                'postal_code' => null,
            ],
            items: [
                ['sku' => 'ADB-TRS-009', 'quantity' => 1],
                ['sku' => 'NHL-BTH-016', 'quantity' => 1],
            ],
            payment: [
                'reference' => 'PAY-DEMO-1005',
                'status' => 'paid',
                'gateway_response' => 'Paid',
                'paid_at' => now()->subDays(3)->addMinutes(35),
            ],
            shipment: [
                'status' => 'shipped',
                'carrier_name' => 'Lasid Northern Line',
                'rider_name' => 'Fuseini Driver',
                'rider_phone' => '+233201111005',
                'tracking_number' => 'TRK-DEMO-1005',
                'tracking_url' => 'https://example.com/track/TRK-DEMO-1005',
                'notes' => 'Loaded onto long-haul route.',
                'packed_at' => now()->subDays(3)->addHour(),
                'shipped_at' => now()->subDays(2),
            ],
            statusHistory: [
                ['from_status' => null, 'to_status' => 'pending', 'note' => 'Order created.', 'created_at' => now()->subDays(3)],
                ['from_status' => 'pending', 'to_status' => 'processing', 'note' => 'Payment captured and inventory allocated.', 'created_at' => now()->subDays(3)->addMinutes(35)],
            ],
        );

        $this->seedOrder(
            orderNumber: 'ORD-DEMO-1006',
            customer: $customerAma,
            actor: $ordersManager,
            zone: $ashantiZone,
            method: $expressAshanti,
            warehouse: $kumasiWarehouse ?? $warehouse,
            orderAttributes: [
                'status' => 'cancelled',
                'payment_status' => 'unpaid',
                'fulfillment_status' => 'unfulfilled',
                'shipping_amount' => 4200,
                'placed_at' => now()->subDays(5),
                'cancelled_at' => now()->subDays(4),
                'notes' => 'Customer requested cancellation before payment completion.',
            ],
            address: [
                'name' => 'Ama Mensah',
                'phone' => '+233240000111',
                'country' => 'Ghana',
                'region' => 'Ashanti',
                'city' => 'Kumasi',
                'district' => 'Asokwa',
                'address_line_1' => '18 Osu Ringway',
                'address_line_2' => null,
                'landmark' => 'Near transport terminal',
                'postal_code' => null,
            ],
            items: [
                ['sku' => 'ADM-DRS-006', 'quantity' => 1],
            ],
            payment: [
                'reference' => 'PAY-DEMO-1006',
                'status' => 'failed',
                'gateway_response' => 'Cancelled by customer',
                'paid_at' => null,
            ],
            shipment: null,
            statusHistory: [
                ['from_status' => null, 'to_status' => 'pending', 'note' => 'Order created.', 'created_at' => now()->subDays(5)],
                ['from_status' => 'pending', 'to_status' => 'cancelled', 'note' => 'Cancelled before payment.', 'created_at' => now()->subDays(4)],
            ],
        );
    }

    /**
     * @param array<int, array{sku: string, quantity: int}> $items
     * @param array<int, array{from_status: ?string, to_status: string, note: ?string, created_at: \Illuminate\Support\Carbon}> $statusHistory
     * @param array<string, mixed>|null $shipment
     */
    private function seedOrder(
        string $orderNumber,
        ?User $customer,
        ?User $actor,
        ?ShippingZone $zone,
        ?ShippingMethod $method,
        ?WarehouseLocation $warehouse,
        array $orderAttributes,
        array $address,
        array $items,
        array $payment,
        ?array $shipment,
        array $statusHistory,
    ): void {
        if (!$customer) {
            return;
        }

        $lineItems = [];
        $subtotal = 0;

        foreach ($items as $item) {
            $product = Product::query()->where('sku', $item['sku'])->first();

            if (!$product) {
                continue;
            }

            $unitPrice = (int) $product->base_price;
            $lineTotal = $unitPrice * $item['quantity'];
            $subtotal += $lineTotal;

            $lineItems[] = [
                'product' => $product,
                'quantity' => $item['quantity'],
                'unit_price' => $unitPrice,
                'line_total' => $lineTotal,
            ];
        }

        $shippingAmount = (int) ($orderAttributes['shipping_amount'] ?? 0);
        $discountAmount = (int) ($orderAttributes['discount_amount'] ?? 0);
        $taxAmount = (int) ($orderAttributes['tax_amount'] ?? 0);

        $order = Order::query()->updateOrCreate(
            ['order_number' => $orderNumber],
            array_merge($orderAttributes, [
                'user_id' => $customer->getKey(),
                'email' => $customer->email,
                'phone' => $customer->phone,
                'currency_code' => 'GHS',
                'subtotal_amount' => $subtotal,
                'discount_amount' => $discountAmount,
                'tax_amount' => $taxAmount,
                'shipping_amount' => $shippingAmount,
                'total_amount' => $subtotal - $discountAmount + $taxAmount + $shippingAmount,
                'shipping_zone_id' => $zone?->getKey(),
                'shipping_method_id' => $method?->getKey(),
                'shipping_zone_name' => $zone?->name,
                'shipping_method_name' => $method?->name,
                'coupon_code' => null,
                'delivery_notes' => 'Demo delivery instructions.',
            ])
        );

        foreach (['shipping', 'billing'] as $type) {
            OrderAddress::query()->updateOrCreate(
                ['order_id' => $order->getKey(), 'type' => $type],
                array_merge($address, ['type' => $type])
            );
        }

        foreach ($lineItems as $index => $lineItem) {
            OrderItem::query()->updateOrCreate(
                ['order_id' => $order->getKey(), 'sku' => $lineItem['product']->sku],
                [
                    'product_id' => $lineItem['product']->getKey(),
                    'product_name' => $lineItem['product']->name,
                    'variant_name' => null,
                    'unit_price' => $lineItem['unit_price'],
                    'quantity' => $lineItem['quantity'],
                    'discount_amount' => 0,
                    'tax_amount' => 0,
                    'line_total' => $lineItem['line_total'],
                ]
            );
        }

        Payment::query()->updateOrCreate(
            ['reference' => $payment['reference']],
            [
                'order_id' => $order->getKey(),
                'user_id' => $customer->getKey(),
                'provider' => 'paystack',
                'provider_transaction_id' => $payment['reference'],
                'status' => $payment['status'],
                'amount' => $order->total_amount,
                'currency_code' => 'GHS',
                'gateway_response' => $payment['gateway_response'],
                'raw_payload_json' => ['demo' => true, 'order_number' => $orderNumber],
                'paid_at' => $payment['paid_at'],
                'failed_at' => null,
            ]
        );

        foreach ($statusHistory as $history) {
            OrderStatusHistory::query()->updateOrCreate(
                [
                    'order_id' => $order->getKey(),
                    'to_status' => $history['to_status'],
                    'created_at' => $history['created_at'],
                ],
                [
                    'from_status' => $history['from_status'],
                    'note' => $history['note'],
                    'changed_by' => $actor?->getKey(),
                ]
            );
        }

        if ($shipment !== null) {
            $shipmentRecord = Shipment::query()->updateOrCreate(
                ['tracking_number' => $shipment['tracking_number']],
                [
                    'order_id' => $order->getKey(),
                    'warehouse_location_id' => $warehouse?->getKey(),
                    'shipping_method_id' => $method?->getKey(),
                    'status' => $shipment['status'],
                    'carrier_name' => $shipment['carrier_name'],
                    'rider_name' => $shipment['rider_name'],
                    'rider_phone' => $shipment['rider_phone'],
                    'tracking_url' => $shipment['tracking_url'],
                    'notes' => $shipment['notes'],
                    'packed_at' => $shipment['packed_at'] ?? null,
                    'shipped_at' => $shipment['shipped_at'] ?? null,
                    'delivered_at' => $shipment['delivered_at'] ?? null,
                    'failed_at' => null,
                    'returned_at' => null,
                ]
            );

            $orderItems = OrderItem::query()->where('order_id', $order->getKey())->get()->keyBy('sku');

            foreach ($lineItems as $lineItem) {
                $orderItem = $orderItems->get($lineItem['product']->sku);

                if (!$orderItem) {
                    continue;
                }

                ShipmentItem::query()->updateOrCreate(
                    [
                        'shipment_id' => $shipmentRecord->getKey(),
                        'order_item_id' => $orderItem->getKey(),
                    ],
                    ['quantity' => $orderItem->quantity]
                );
            }
        }
    }
}
