<?php

namespace Tests\Feature\Api\Checkout;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ShippingMethod;
use App\Models\ShippingZone;
use App\Models\ShippingZoneArea;
use App\Models\User;
use App\Notifications\CustomerMagicLinkNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GuestCheckoutInitializeTest extends TestCase
{
    use RefreshDatabase;

    private function paystackOk(string $reference = 'PAY-GUEST-001'): void
    {
        Http::fake([
            'api.paystack.co/transaction/initialize' => Http::response([
                'status' => true,
                'data' => [
                    'authorization_url' => 'https://checkout.paystack.com/guest-init',
                    'access_code' => 'guest-code',
                    'reference' => $reference,
                ],
            ], 200),
        ]);
    }

    private function activeProduct(array $attrs = []): Product
    {
        return Product::factory()->create(array_merge([
            'status' => 'active',
            'published_at' => now()->subDay(),
            'base_price' => 2000,
        ], $attrs));
    }

    private function guestCartWithItem(Product $product, int $qty = 1, ?string $token = null): Cart
    {
        $cart = Cart::factory()->create([
            'user_id' => null,
            'session_id' => $token ?? 'guest-checkout-token',
            'status' => 'active',
        ]);

        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'unit_price' => $product->base_price,
            'quantity' => $qty,
            'line_total' => $product->base_price * $qty,
            'product_name_snapshot' => $product->name,
            'sku_snapshot' => $product->sku,
        ]);

        return $cart;
    }

    private function userCartWithItem(User $user, Product $product, int $qty = 1): Cart
    {
        $cart = Cart::factory()->create([
            'user_id' => $user->id,
            'session_id' => null,
            'status' => 'active',
        ]);

        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'unit_price' => $product->base_price,
            'quantity' => $qty,
            'line_total' => $product->base_price * $qty,
            'product_name_snapshot' => $product->name,
            'sku_snapshot' => $product->sku,
        ]);

        return $cart;
    }

    private function zone(string $countryName = 'Ghana'): ShippingZone
    {
        $zone = ShippingZone::factory()->create(['is_active' => true]);
        ShippingZoneArea::factory()->create([
            'shipping_zone_id' => $zone->id,
            'area_type' => 'country',
            'area_name' => $countryName,
        ]);

        return $zone;
    }

    private function method(ShippingZone $zone, int $fee = 1000): ShippingMethod
    {
        $method = ShippingMethod::factory()->create([
            'flat_rate_amount' => $fee,
            'is_active' => true,
        ]);

        $zone->shippingMethods()->attach($method);

        return $method;
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'email' => 'guest@example.com',
            'name' => 'Guest Buyer',
            'phone' => '+233240000000',
            'country' => 'Ghana',
            'region' => 'Greater Accra',
            'city' => 'Accra',
            'district' => 'Osu',
            'address_line_1' => '12 Market Street',
            'address_line_2' => null,
            'landmark' => 'Near the station',
            'postal_code' => 'GA-123-4567',
            'shipping_method_id' => null,
            'payment_provider' => 'paystack',
            'notes' => 'Leave at reception',
            'delivery_notes' => 'Call on arrival',
        ], $overrides);
    }

    public function test_guest_checkout_creates_customer_address_order_payment_and_logs_in(): void
    {
        Notification::fake();
        $this->paystackOk();

        $product = $this->activeProduct();
        $cart = $this->guestCartWithItem($product, 2, 'guest-checkout-token');
        $zone = $this->zone();
        $method = $this->method($zone);

        $response = $this
            ->withHeader('X-Cart-Token', $cart->session_id)
            ->postJson('/api/v1/checkout/guest/initialize', $this->payload([
                'shipping_method_id' => $method->id,
            ]));

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.order.status', 'pending')
            ->assertJsonPath('data.payment.authorization_url', 'https://checkout.paystack.com/guest-init');

        $this->assertAuthenticated('customer');

        $user = User::where('email', 'guest@example.com')->firstOrFail();
        $this->assertNotNull($user->email_verified_at);
        $this->assertDatabaseHas('addresses', [
            'user_id' => $user->id,
            'name' => 'Guest Buyer',
            'is_default' => true,
        ]);
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'email' => 'guest@example.com',
            'total_amount' => 5000,
        ]);
        $this->assertDatabaseHas('payments', [
            'user_id' => $user->id,
            'status' => 'pending',
        ]);
        Notification::assertSentOnDemand(CustomerMagicLinkNotification::class);
    }

    public function test_guest_checkout_reuses_existing_customer_and_merges_existing_cart(): void
    {
        Notification::fake();
        $this->paystackOk();

        $user = User::factory()->create([
            'email' => 'guest@example.com',
            'name' => '',
            'phone' => null,
        ]);
        $existingProduct = $this->activeProduct(['base_price' => 1000]);
        $guestProduct = $this->activeProduct(['base_price' => 2500]);
        $userCart = $this->userCartWithItem($user, $existingProduct, 1);
        $guestCart = $this->guestCartWithItem($guestProduct, 2, 'guest-merge-token');
        $zone = $this->zone();
        $method = $this->method($zone);

        $response = $this
            ->withHeader('X-Cart-Token', $guestCart->session_id)
            ->postJson('/api/v1/checkout/guest/initialize', $this->payload([
                'shipping_method_id' => $method->id,
            ]));

        $response->assertCreated()
            ->assertJsonPath('data.order.subtotal_amount', 6000)
            ->assertJsonPath('data.order.total_amount', 7000);

        $this->assertSame(1, User::where('email', 'guest@example.com')->count());
        $this->assertDatabaseHas('carts', [
            'id' => $guestCart->id,
            'status' => 'merged',
        ]);
        $this->assertDatabaseHas('carts', [
            'id' => $userCart->id,
            'status' => 'converted',
        ]);
        $user->refresh();
        $this->assertSame('Guest Buyer', $user->name);
        $this->assertSame('+233240000000', $user->phone);
    }

    public function test_guest_checkout_rejects_platform_user_email(): void
    {
        Notification::fake();
        $this->paystackOk();

        $staff = User::factory()->create(['email' => 'staff@example.com']);
        $staff->assignRole(Role::findOrCreate('support_agent', 'web'));
        $product = $this->activeProduct();
        $cart = $this->guestCartWithItem($product, 1, 'staff-cart-token');
        $zone = $this->zone();
        $method = $this->method($zone);

        $response = $this
            ->withHeader('X-Cart-Token', $cart->session_id)
            ->postJson('/api/v1/checkout/guest/initialize', $this->payload([
                'email' => 'staff@example.com',
                'shipping_method_id' => $method->id,
            ]));

        $response->assertUnprocessable()
            ->assertJsonPath('message', 'This account is not available on the storefront.');

        $this->assertGuest('customer');
    }

    public function test_guest_checkout_fails_when_cart_is_missing_or_empty(): void
    {
        $zone = $this->zone();
        $method = $this->method($zone);

        $response = $this->postJson('/api/v1/checkout/guest/initialize', $this->payload([
            'shipping_method_id' => $method->id,
        ]));

        $response->assertUnprocessable()
            ->assertJsonPath('message', 'Cart is empty.');
    }

    public function test_guest_checkout_returns_order_id_and_keeps_session_when_payment_setup_fails(): void
    {
        Notification::fake();
        Http::fake([
            'api.paystack.co/transaction/initialize' => Http::sequence()
                ->push([
                    'status' => false,
                    'message' => 'Gateway down',
                ], 500)
                ->push([
                    'status' => true,
                    'data' => [
                        'authorization_url' => 'https://checkout.paystack.com/retry',
                        'access_code' => 'retry-code',
                        'reference' => 'PAY-RETRY-001',
                    ],
                ], 200),
        ]);

        $product = $this->activeProduct();
        $cart = $this->guestCartWithItem($product, 1, 'payment-fail-cart');
        $zone = $this->zone();
        $method = $this->method($zone);

        $response = $this
            ->withHeader('X-Cart-Token', $cart->session_id)
            ->postJson('/api/v1/checkout/guest/initialize', $this->payload([
                'shipping_method_id' => $method->id,
            ]));

        $response->assertStatus(502)
            ->assertJsonPath('success', false);

        $orderId = $response->json('errors.order_id');
        $this->assertNotNull($orderId);
        $this->assertAuthenticated('customer');

        $retryResponse = $this->postJson('/api/v1/payments/initialize', [
            'order_id' => $orderId,
        ]);

        $retryResponse->assertOk()
            ->assertJsonPath('data.reference', 'PAY-RETRY-001')
            ->assertJsonPath('data.authorization_url', 'https://checkout.paystack.com/retry');

        $this->assertDatabaseHas('orders', [
            'id' => $orderId,
            'email' => 'guest@example.com',
        ]);
    }
}
