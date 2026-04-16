<?php

namespace Tests\Feature\Api\Checkout;

use App\Models\Address;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ShippingMethod;
use App\Models\ShippingZone;
use App\Models\ShippingZoneArea;
use App\Models\User;
use App\Notifications\InternalOrderPlacedNotification;
use App\Notifications\OrderPlacedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CreateOrderTest extends TestCase
{
    use RefreshDatabase;

    // --- helpers ---

    private function user(): User
    {
        return User::factory()->create();
    }

    private function activeProduct(array $attrs = []): Product
    {
        return Product::factory()->create(array_merge([
            'status'       => 'active',
            'published_at' => now()->subDay(),
            'base_price'   => 2000,
        ], $attrs));
    }

    private function cartWithItem(User $user, Product $product, int $qty = 1): Cart
    {
        $cart = Cart::factory()->create([
            'user_id'    => $user->id,
            'session_id' => null,
            'status'     => 'active',
        ]);

        CartItem::factory()->create([
            'cart_id'               => $cart->id,
            'product_id'            => $product->id,
            'unit_price'            => $product->base_price,
            'quantity'              => $qty,
            'line_total'            => $product->base_price * $qty,
            'product_name_snapshot' => $product->name,
            'sku_snapshot'          => $product->sku,
        ]);

        return $cart;
    }

    private function zone(string $countryName = 'Ghana'): ShippingZone
    {
        $zone = ShippingZone::factory()->create(['is_active' => true]);
        ShippingZoneArea::factory()->create([
            'shipping_zone_id' => $zone->id,
            'area_type'        => 'country',
            'area_name'        => $countryName,
        ]);

        return $zone;
    }

    private function method(ShippingZone $zone, int $fee = 1000): ShippingMethod
    {
        $method = ShippingMethod::factory()->create([
            'flat_rate_amount' => $fee,
            'is_active'        => true,
        ]);

        $zone->shippingMethods()->attach($method);

        return $method;
    }

    private function address(User $user, string $country = 'Ghana'): Address
    {
        return Address::factory()->create([
            'user_id' => $user->id,
            'country' => $country,
            'type'    => 'shipping',
        ]);
    }

    private function payload(Address $address, ShippingMethod $method, array $extra = []): array
    {
        return array_merge([
            'address_id'         => $address->id,
            'shipping_method_id' => $method->id,
        ], $extra);
    }

    // --- tests ---

    public function test_creates_order_successfully(): void
    {
        Notification::fake();
        config()->set('notifications.internal.recipients', ['ops@example.com']);

        $user    = $this->user();
        $product = $this->activeProduct();
        $this->cartWithItem($user, $product);
        $zone    = $this->zone();
        $method  = $this->method($zone);
        $address = $this->address($user);

        $response = $this->actingAsCustomer($user)->postJson(
            '/api/v1/checkout/orders',
            $this->payload($address, $method),
        );

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.payment_status', 'unpaid')
            ->assertJsonPath('data.fulfillment_status', 'unfulfilled');

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'email'   => $user->email,
            'status'  => 'pending',
        ]);
        Notification::assertSentOnDemand(OrderPlacedNotification::class);
        Notification::assertSentOnDemand(InternalOrderPlacedNotification::class);
    }

    public function test_order_placed_customer_notification_can_be_opted_out_without_affecting_internal_alert(): void
    {
        Notification::fake();
        config()->set('notifications.internal.recipients', ['ops@example.com']);

        $user = User::factory()->create([
            'notification_preferences' => [
                'orders_placed' => false,
            ],
        ]);
        $product = $this->activeProduct();
        $this->cartWithItem($user, $product);
        $zone = $this->zone();
        $method = $this->method($zone);
        $address = $this->address($user);

        $this->actingAsCustomer($user)->postJson(
            '/api/v1/checkout/orders',
            $this->payload($address, $method),
        )->assertCreated();

        Notification::assertSentOnDemandTimes(OrderPlacedNotification::class, 0);
        Notification::assertSentOnDemand(InternalOrderPlacedNotification::class);
    }

    public function test_creates_order_items(): void
    {
        $user    = $this->user();
        $product = $this->activeProduct(['base_price' => 3000]);
        $this->cartWithItem($user, $product, 2);
        $zone    = $this->zone();
        $method  = $this->method($zone);
        $address = $this->address($user);

        $response = $this->actingAsCustomer($user)->postJson(
            '/api/v1/checkout/orders',
            $this->payload($address, $method),
        );

        $response->assertCreated();

        $orderId = $response->json('data.id');

        $this->assertDatabaseHas('order_items', [
            'order_id'    => $orderId,
            'product_id'  => $product->id,
            'quantity'    => 2,
            'unit_price'  => 3000,
            'line_total'  => 6000,
        ]);
        $this->assertCount(1, $response->json('data.items'));
        $this->assertSame(2, $response->json('data.items.0.quantity'));
    }

    public function test_creates_address_snapshot(): void
    {
        $user    = $this->user();
        $product = $this->activeProduct();
        $this->cartWithItem($user, $product);
        $zone    = $this->zone();
        $method  = $this->method($zone);
        $address = $this->address($user);

        $response = $this->actingAsCustomer($user)->postJson(
            '/api/v1/checkout/orders',
            $this->payload($address, $method),
        );

        $response->assertCreated();

        $orderId = $response->json('data.id');

        $this->assertDatabaseHas('order_addresses', [
            'order_id' => $orderId,
            'country'  => $address->country,
            'city'     => $address->city,
        ]);
    }

    public function test_creates_status_history(): void
    {
        $user    = $this->user();
        $product = $this->activeProduct();
        $this->cartWithItem($user, $product);
        $zone    = $this->zone();
        $method  = $this->method($zone);
        $address = $this->address($user);

        $response = $this->actingAsCustomer($user)->postJson(
            '/api/v1/checkout/orders',
            $this->payload($address, $method),
        );

        $response->assertCreated();

        $orderId = $response->json('data.id');

        $this->assertDatabaseHas('order_status_histories', [
            'order_id'    => $orderId,
            'from_status' => null,
            'to_status'   => 'pending',
        ]);
    }

    public function test_cart_no_longer_active_after_order(): void
    {
        $user    = $this->user();
        $product = $this->activeProduct();
        $cart    = $this->cartWithItem($user, $product);
        $zone    = $this->zone();
        $method  = $this->method($zone);
        $address = $this->address($user);

        $this->actingAsCustomer($user)->postJson(
            '/api/v1/checkout/orders',
            $this->payload($address, $method),
        );

        $this->assertDatabaseHas('carts', [
            'id'     => $cart->id,
            'status' => 'converted',
        ]);
    }

    public function test_fails_when_cart_is_empty(): void
    {
        $user    = $this->user();
        $zone    = $this->zone();
        $method  = $this->method($zone);
        $address = $this->address($user);

        $response = $this->actingAsCustomer($user)->postJson(
            '/api/v1/checkout/orders',
            $this->payload($address, $method),
        );

        $response->assertUnprocessable()
            ->assertJsonPath('success', false);
    }

    public function test_fails_when_address_belongs_to_another_user(): void
    {
        $user    = $this->user();
        $other   = $this->user();
        $product = $this->activeProduct();
        $this->cartWithItem($user, $product);
        $zone    = $this->zone();
        $method  = $this->method($zone);
        $address = $this->address($other);

        $response = $this->actingAsCustomer($user)->postJson(
            '/api/v1/checkout/orders',
            $this->payload($address, $method),
        );

        $response->assertNotFound();
    }

    public function test_fails_when_shipping_method_not_in_zone(): void
    {
        $user      = $this->user();
        $product   = $this->activeProduct();
        $this->cartWithItem($user, $product);
        $zone      = $this->zone('Ghana');
        $otherZone = ShippingZone::factory()->create(['is_active' => true]);
        $method    = $this->method($otherZone);
        $address   = $this->address($user, 'Ghana');

        $response = $this->actingAsCustomer($user)->postJson(
            '/api/v1/checkout/orders',
            $this->payload($address, $method),
        );

        $response->assertUnprocessable();
    }

    public function test_totals_stored_correctly(): void
    {
        $user    = $this->user();
        $product = $this->activeProduct(['base_price' => 5000]);
        $this->cartWithItem($user, $product, 2); // subtotal = 10000
        $zone    = $this->zone();
        $method  = $this->method($zone, 2000); // shipping = 2000
        $address = $this->address($user);

        $response = $this->actingAsCustomer($user)->postJson(
            '/api/v1/checkout/orders',
            $this->payload($address, $method),
        );

        $response->assertCreated();
        $this->assertSame(10000, $response->json('data.subtotal_amount'));
        $this->assertSame(2000,  $response->json('data.shipping_amount'));
        $this->assertSame(12000, $response->json('data.total_amount'));
        $this->assertSame(0,     $response->json('data.discount_amount'));
        $this->assertSame(0,     $response->json('data.tax_amount'));

        $orderId = $response->json('data.id');
        $this->assertDatabaseHas('orders', [
            'id'              => $orderId,
            'subtotal_amount' => 10000,
            'shipping_amount' => 2000,
            'total_amount'    => 12000,
        ]);
    }

    public function test_order_number_generated(): void
    {
        $user    = $this->user();
        $product = $this->activeProduct();
        $this->cartWithItem($user, $product);
        $zone    = $this->zone();
        $method  = $this->method($zone);
        $address = $this->address($user);

        $response = $this->actingAsCustomer($user)->postJson(
            '/api/v1/checkout/orders',
            $this->payload($address, $method),
        );

        $response->assertCreated();
        $orderNumber = $response->json('data.order_number');
        $this->assertNotNull($orderNumber);
        $this->assertStringStartsWith('ORD-', $orderNumber);
    }

    public function test_requires_authentication(): void
    {
        $response = $this->postJson('/api/v1/checkout/orders', [
            'address_id'         => 1,
            'shipping_method_id' => 1,
        ]);

        $response->assertUnauthorized();
    }
}

