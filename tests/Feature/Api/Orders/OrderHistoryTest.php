<?php

namespace Tests\Feature\Api\Orders;

use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OrderHistoryTest extends TestCase
{
    use RefreshDatabase;

    // --- helpers ---

    private function user(): User
    {
        return User::factory()->create();
    }

    private function orderFor(User $user, array $attrs = []): Order
    {
        return Order::factory()->create(array_merge(['user_id' => $user->id], $attrs));
    }

    // --- list ---

    public function test_guest_cannot_list_orders(): void
    {
        $response = $this->getJson(route('api.v1.orders.index'));

        $response->assertUnauthorized();
    }

    public function test_user_can_list_own_orders(): void
    {
        $user = $this->user();
        $this->orderFor($user);
        $this->orderFor($user);

        $response = $this->actingAsCustomer($user)->getJson(route('api.v1.orders.index'));

        $response->assertOk();
        $response->assertJsonCount(2, 'data');
    }

    public function test_user_only_sees_own_orders_in_list(): void
    {
        $userA = $this->user();
        $userB = $this->user();
        $this->orderFor($userA);
        $this->orderFor($userB);
        $this->orderFor($userB);

        $response = $this->actingAsCustomer($userA)->getJson(route('api.v1.orders.index'));

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
    }

    public function test_list_returns_paginated_response(): void
    {
        $user = $this->user();
        Order::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->actingAsCustomer($user)->getJson(route('api.v1.orders.index'));

        $response->assertOk();
        $response->assertJsonStructure(['data', 'meta' => ['total', 'current_page', 'per_page', 'last_page']]);
    }

    public function test_list_includes_summary_fields(): void
    {
        $user  = $this->user();
        $order = $this->orderFor($user);

        $response = $this->actingAsCustomer($user)->getJson(route('api.v1.orders.index'));

        $response->assertOk();
        $response->assertJsonFragment([
            'order_number'       => $order->order_number,
            'status'             => $order->status,
            'payment_status'     => $order->payment_status,
            'fulfillment_status' => $order->fulfillment_status,
            'total_amount'       => $order->total_amount,
        ]);
    }

    // --- detail ---

    public function test_guest_cannot_view_order_detail(): void
    {
        $order = Order::factory()->create();

        $response = $this->getJson(route('api.v1.orders.show', $order));

        $response->assertUnauthorized();
    }

    public function test_user_can_view_own_order(): void
    {
        $user  = $this->user();
        $order = $this->orderFor($user);

        $response = $this->actingAsCustomer($user)->getJson(route('api.v1.orders.show', $order));

        $response->assertOk();
        $response->assertJsonFragment(['order_number' => $order->order_number]);
    }

    public function test_user_cannot_view_another_users_order(): void
    {
        $userA = $this->user();
        $userB = $this->user();
        $order = $this->orderFor($userB);

        $response = $this->actingAsCustomer($userA)->getJson(route('api.v1.orders.show', $order));

        $response->assertNotFound();
    }

    public function test_order_detail_includes_items(): void
    {
        $user  = $this->user();
        $order = $this->orderFor($user);
        OrderItem::factory()->create(['order_id' => $order->id]);
        OrderItem::factory()->create(['order_id' => $order->id]);

        $response = $this->actingAsCustomer($user)->getJson(route('api.v1.orders.show', $order));

        $response->assertOk();
        $response->assertJsonCount(2, 'data.items');
    }

    public function test_order_detail_items_include_conversion_aware_primary_image_fields(): void
    {
        Storage::fake('media');
        $user = $this->user();
        $order = $this->orderFor($user);
        $product = Product::factory()->create();
        $media = $product
            ->addMedia(UploadedFile::fake()->image('photo.jpg', 1600, 1200))
            ->toMediaCollection(Product::IMAGE_COLLECTION);
        $media = $media->fresh();

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'sku' => $product->sku,
        ]);

        $response = $this->actingAsCustomer($user)->getJson(route('api.v1.orders.show', $order));

        $response->assertOk()
            ->assertJsonPath('data.items.0.primary_image_url', $media->getUrl());

        $item = $response->json('data.items.0');
        $this->assertArrayHasKey('primary_image_thumb_url', $item);
        $this->assertArrayHasKey('primary_image_card_url', $item);
        $this->assertArrayHasKey('primary_image_gallery_url', $item);
    }

    public function test_order_detail_includes_totals(): void
    {
        $user  = $this->user();
        $order = $this->orderFor($user, [
            'subtotal_amount' => 5000,
            'discount_amount' => 0,
            'tax_amount'      => 0,
            'shipping_amount' => 1000,
            'total_amount'    => 6000,
        ]);

        $response = $this->actingAsCustomer($user)->getJson(route('api.v1.orders.show', $order));

        $response->assertOk();
        $response->assertJsonFragment([
            'subtotal_amount' => 5000,
            'shipping_amount' => 1000,
            'total_amount'    => 6000,
        ]);
    }

    public function test_order_detail_includes_shipping_address(): void
    {
        $user    = $this->user();
        $order   = $this->orderFor($user);
        OrderAddress::factory()->create([
            'order_id' => $order->id,
            'type'     => 'shipping',
            'city'     => 'Kumasi',
        ]);

        $response = $this->actingAsCustomer($user)->getJson(route('api.v1.orders.show', $order));

        $response->assertOk();
        $response->assertJsonFragment(['city' => 'Kumasi']);
    }

    public function test_order_detail_returns_shipping_address_not_billing_address(): void
    {
        $user  = $this->user();
        $order = $this->orderFor($user);

        OrderAddress::factory()->create([
            'order_id' => $order->id,
            'type'     => 'billing',
            'city'     => 'Accra',
        ]);

        OrderAddress::factory()->create([
            'order_id' => $order->id,
            'type'     => 'shipping',
            'city'     => 'Kumasi',
        ]);

        $response = $this->actingAsCustomer($user)->getJson(route('api.v1.orders.show', $order));

        $response->assertOk();
        $response->assertJsonPath('data.shipping_address.type', 'shipping');
        $response->assertJsonPath('data.shipping_address.city', 'Kumasi');
    }

    public function test_order_detail_includes_shipment_tracking_when_present(): void
    {
        $user     = $this->user();
        $order    = $this->orderFor($user, ['status' => 'shipped']);
        $shipment = Shipment::factory()->shipped()->create([
            'order_id'        => $order->id,
            'carrier_name'    => 'GIG Logistics',
            'tracking_number' => 'GIG-9999',
        ]);

        $response = $this->actingAsCustomer($user)->getJson(route('api.v1.orders.show', $order));

        $response->assertOk();
        $response->assertJsonCount(1, 'data.shipments');
        $response->assertJsonFragment([
            'carrier_name'    => 'GIG Logistics',
            'tracking_number' => 'GIG-9999',
            'status'          => 'shipped',
        ]);
        $this->assertNotNull($response->json('data.shipments.0.shipped_at'));
    }

    public function test_order_detail_includes_failed_and_returned_shipment_timestamps(): void
    {
        $user  = $this->user();
        $order = $this->orderFor($user);

        Shipment::factory()->create([
            'order_id'     => $order->id,
            'status'       => 'returned',
            'packed_at'    => now()->subDay(),
            'shipped_at'   => now()->subHours(12),
            'failed_at'    => now()->subHours(6),
            'returned_at'  => now(),
        ]);

        $response = $this->actingAsCustomer($user)->getJson(route('api.v1.orders.show', $order));

        $response->assertOk();
        $this->assertNotNull($response->json('data.shipments.0.failed_at'));
        $this->assertNotNull($response->json('data.shipments.0.returned_at'));
    }

    public function test_order_detail_has_empty_shipments_when_none_exist(): void
    {
        $user  = $this->user();
        $order = $this->orderFor($user);

        $response = $this->actingAsCustomer($user)->getJson(route('api.v1.orders.show', $order));

        $response->assertOk();
        $response->assertJsonCount(0, 'data.shipments');
    }

    public function test_order_detail_includes_payment_and_fulfillment_status(): void
    {
        $user  = $this->user();
        $order = $this->orderFor($user, [
            'payment_status'     => 'paid',
            'fulfillment_status' => 'unfulfilled',
        ]);

        $response = $this->actingAsCustomer($user)->getJson(route('api.v1.orders.show', $order));

        $response->assertOk();
        $response->assertJsonFragment([
            'payment_status'     => 'paid',
            'fulfillment_status' => 'unfulfilled',
        ]);
    }
}

