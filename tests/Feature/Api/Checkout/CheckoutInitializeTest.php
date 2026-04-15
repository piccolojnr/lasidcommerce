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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CheckoutInitializeTest extends TestCase
{
    use RefreshDatabase;

    private function paystackOk(string $reference = 'PAY-CHECKOUT-001'): void
    {
        Http::fake([
            'api.paystack.co/transaction/initialize' => Http::response([
                'status' => true,
                'data' => [
                    'authorization_url' => 'https://checkout.paystack.com/init',
                    'access_code' => 'init-code',
                    'reference' => $reference,
                ],
            ], 200),
        ]);
    }

    private function user(): User
    {
        return User::factory()->create();
    }

    private function activeProduct(array $attrs = []): Product
    {
        return Product::factory()->create(array_merge([
            'status' => 'active',
            'published_at' => now()->subDay(),
            'base_price' => 2000,
        ], $attrs));
    }

    private function cartWithItem(User $user, Product $product, int $qty = 1): Cart
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

    private function address(User $user, string $country = 'Ghana'): Address
    {
        return Address::factory()->create([
            'user_id' => $user->id,
            'country' => $country,
            'type' => 'shipping',
        ]);
    }

    public function test_initialize_creates_order_and_payment_session(): void
    {
        $this->paystackOk();

        $user = $this->user();
        $product = $this->activeProduct();
        $this->cartWithItem($user, $product);
        $zone = $this->zone();
        $method = $this->method($zone);
        $address = $this->address($user);

        $response = $this->actingAs($user)->postJson('/api/v1/checkout/initialize', [
            'address_id' => $address->id,
            'shipping_method_id' => $method->id,
            'payment_provider' => 'paystack',
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.order.status', 'pending')
            ->assertJsonPath('data.payment.provider', 'paystack')
            ->assertJsonPath('data.payment.authorization_url', 'https://checkout.paystack.com/init');

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'payment_status' => 'unpaid',
        ]);
        $this->assertDatabaseHas('payments', [
            'user_id' => $user->id,
            'provider' => 'paystack',
            'status' => 'pending',
        ]);
    }

    public function test_initialize_rejects_coupon_code_until_supported(): void
    {
        $this->paystackOk();

        $user = $this->user();
        $product = $this->activeProduct();
        $this->cartWithItem($user, $product);
        $zone = $this->zone();
        $method = $this->method($zone);
        $address = $this->address($user);

        $response = $this->actingAs($user)->postJson('/api/v1/checkout/initialize', [
            'address_id' => $address->id,
            'shipping_method_id' => $method->id,
            'payment_provider' => 'paystack',
            'coupon_code' => 'SAVE10',
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('success', false);
    }

    public function test_initialize_returns_order_id_when_payment_setup_fails(): void
    {
        Http::fake([
            'api.paystack.co/transaction/initialize' => Http::response([
                'status' => false,
                'message' => 'Gateway down',
            ], 500),
        ]);

        $user = $this->user();
        $product = $this->activeProduct();
        $this->cartWithItem($user, $product);
        $zone = $this->zone();
        $method = $this->method($zone);
        $address = $this->address($user);

        $response = $this->actingAs($user)->postJson('/api/v1/checkout/initialize', [
            'address_id' => $address->id,
            'shipping_method_id' => $method->id,
            'payment_provider' => 'paystack',
        ]);

        $response->assertStatus(502)
            ->assertJsonPath('success', false);

        $this->assertNotNull($response->json('errors.order_id'));
        $this->assertDatabaseHas('orders', [
            'id' => $response->json('errors.order_id'),
            'user_id' => $user->id,
        ]);
    }
}
