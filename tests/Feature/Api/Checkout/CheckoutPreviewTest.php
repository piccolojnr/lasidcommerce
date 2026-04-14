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
use Tests\TestCase;

class CheckoutPreviewTest extends TestCase
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
        ]);
    }

    // --- tests ---

    public function test_preview_succeeds_with_valid_inputs(): void
    {
        $user    = $this->user();
        $product = $this->activeProduct();
        $this->cartWithItem($user, $product);
        $zone    = $this->zone('Ghana');
        $method  = $this->method($zone, 1500);
        $address = $this->address($user, 'Ghana');

        $response = $this->actingAs($user)->postJson('/api/v1/checkout/preview', [
            'address_id'         => $address->id,
            'shipping_method_id' => $method->id,
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [
                    'cart',
                    'address',
                    'shipping_zone',
                    'shipping_method',
                    'totals' => ['subtotal_amount', 'shipping_amount', 'total_amount'],
                ],
            ]);
    }

    public function test_preview_fails_when_cart_is_empty(): void
    {
        $user    = $this->user();
        $zone    = $this->zone('Ghana');
        $method  = $this->method($zone);
        $address = $this->address($user, 'Ghana');

        // No cart items — GetOrCreateCartAction will create a new empty cart
        $response = $this->actingAs($user)->postJson('/api/v1/checkout/preview', [
            'address_id'         => $address->id,
            'shipping_method_id' => $method->id,
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('success', false);
    }

    public function test_preview_fails_for_another_users_address(): void
    {
        $user    = $this->user();
        $other   = $this->user();
        $product = $this->activeProduct();
        $this->cartWithItem($user, $product);
        $zone    = $this->zone('Ghana');
        $method  = $this->method($zone);
        $address = $this->address($other, 'Ghana'); // belongs to other user

        $response = $this->actingAs($user)->postJson('/api/v1/checkout/preview', [
            'address_id'         => $address->id,
            'shipping_method_id' => $method->id,
        ]);

        $response->assertNotFound();
    }

    public function test_preview_fails_when_shipping_method_does_not_belong_to_resolved_zone(): void
    {
        $user    = $this->user();
        $product = $this->activeProduct();
        $this->cartWithItem($user, $product);

        $zone    = $this->zone('Ghana');
        $otherZone = ShippingZone::factory()->create(['is_active' => true]);
        $method  = $this->method($otherZone); // method from a DIFFERENT zone
        $address = $this->address($user, 'Ghana');

        $response = $this->actingAs($user)->postJson('/api/v1/checkout/preview', [
            'address_id'         => $address->id,
            'shipping_method_id' => $method->id,
        ]);

        $response->assertUnprocessable();
    }

    public function test_preview_returns_correct_totals(): void
    {
        $user    = $this->user();
        $product = $this->activeProduct(['base_price' => 3000]);
        $this->cartWithItem($user, $product, 2); // 2 * 3000 = 6000
        $zone    = $this->zone('Ghana');
        $method  = $this->method($zone, 1500); // shipping = 1500
        $address = $this->address($user, 'Ghana');

        $response = $this->actingAs($user)->postJson('/api/v1/checkout/preview', [
            'address_id'         => $address->id,
            'shipping_method_id' => $method->id,
        ]);

        $response->assertOk();
        $this->assertSame(6000, $response->json('data.totals.subtotal_amount'));
        $this->assertSame(1500, $response->json('data.totals.shipping_amount'));
        $this->assertSame(7500, $response->json('data.totals.total_amount'));
        $this->assertSame(0,    $response->json('data.totals.tax_amount'));
        $this->assertSame(0,    $response->json('data.totals.discount_amount'));
    }

    public function test_preview_reflects_current_cart_contents(): void
    {
        $user     = $this->user();
        $product1 = $this->activeProduct(['base_price' => 1000]);
        $product2 = $this->activeProduct(['base_price' => 2000]);
        $cart     = $this->cartWithItem($user, $product1, 1);
        CartItem::factory()->create([
            'cart_id'               => $cart->id,
            'product_id'            => $product2->id,
            'unit_price'            => 2000,
            'quantity'              => 1,
            'line_total'            => 2000,
            'product_name_snapshot' => $product2->name,
            'sku_snapshot'          => $product2->sku,
        ]);
        $zone    = $this->zone('Ghana');
        $method  = $this->method($zone, 500);
        $address = $this->address($user, 'Ghana');

        $response = $this->actingAs($user)->postJson('/api/v1/checkout/preview', [
            'address_id'         => $address->id,
            'shipping_method_id' => $method->id,
        ]);

        $response->assertOk();
        $this->assertCount(2, $response->json('data.cart.items'));
        $this->assertSame(3000, $response->json('data.totals.subtotal_amount'));
        $this->assertSame(3500, $response->json('data.totals.total_amount'));
    }

    public function test_preview_requires_authentication(): void
    {
        $response = $this->postJson('/api/v1/checkout/preview', [
            'address_id'         => 1,
            'shipping_method_id' => 1,
        ]);

        $response->assertUnauthorized();
    }
}
