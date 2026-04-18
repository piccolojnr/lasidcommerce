<?php

namespace Tests\Feature\Api\Cart;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CartApiTest extends TestCase
{
    use RefreshDatabase;

    private function activeProduct(array $attrs = []): Product
    {
        return Product::factory()->create(array_merge([
            'status'       => 'active',
            'published_at' => now()->subDay(),
        ], $attrs));
    }

    // --- GET /api/v1/cart ---

    public function test_guest_gets_new_cart_when_no_token(): void
    {
        $response = $this->getJson('/api/v1/cart');

        $response->assertOk()
            ->assertJsonPath('success', true);

        $cartToken = $response->json('data.cart_token');
        $this->assertNotNull($cartToken);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $cartToken,
        );
    }

    public function test_guest_retrieves_same_cart_with_token(): void
    {
        $first  = $this->getJson('/api/v1/cart');
        $token  = $first->json('data.cart_token');

        $second = $this->getJson('/api/v1/cart', ['X-Cart-Token' => $token]);

        $second->assertOk();
        $this->assertSame($token, $second->json('data.cart_token'));
    }

    // --- POST /api/v1/cart/items ---

    public function test_guest_can_add_item(): void
    {
        $product = $this->activeProduct();

        $response = $this->postJson('/api/v1/cart/items', [
            'product_id' => $product->id,
            'quantity'   => 2,
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true);

        $items = $response->json('data.items');
        $this->assertCount(1, $items);
        $this->assertSame(2, $items[0]['quantity']);
    }

    public function test_cart_items_include_conversion_aware_primary_image_fields(): void
    {
        Storage::fake('media');
        $product = $this->activeProduct();
        $media = $product
            ->addMedia(UploadedFile::fake()->image('photo.jpg', 1600, 1200))
            ->toMediaCollection(Product::IMAGE_COLLECTION);
        $media = $media->fresh();

        $response = $this->postJson('/api/v1/cart/items', [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.items.0.primary_image_url', $media->getUrl());

        $item = $response->json('data.items.0');
        $this->assertArrayHasKey('primary_image_thumb_url', $item);
        $this->assertArrayHasKey('primary_image_card_url', $item);
        $this->assertArrayHasKey('primary_image_gallery_url', $item);
    }

    public function test_adding_same_product_increments_quantity(): void
    {
        $product = $this->activeProduct();
        $first   = $this->getJson('/api/v1/cart');
        $token   = $first->json('data.cart_token');

        $this->postJson('/api/v1/cart/items', ['product_id' => $product->id, 'quantity' => 1], ['X-Cart-Token' => $token]);
        $response = $this->postJson('/api/v1/cart/items', ['product_id' => $product->id, 'quantity' => 2], ['X-Cart-Token' => $token]);

        $response->assertCreated();
        $items = $response->json('data.items');
        $this->assertCount(1, $items);
        $this->assertSame(3, $items[0]['quantity']);
    }

    // --- PATCH /api/v1/cart/items/{cartItem} ---

    public function test_update_quantity_works(): void
    {
        $product  = $this->activeProduct();
        $cartInit = $this->getJson('/api/v1/cart');
        $token    = $cartInit->json('data.cart_token');

        $this->postJson('/api/v1/cart/items', ['product_id' => $product->id, 'quantity' => 1], ['X-Cart-Token' => $token]);

        $cart     = Cart::where('session_id', $token)->first();
        $cartItem = $cart->cartItems()->first();

        $response = $this->patchJson(
            "/api/v1/cart/items/{$cartItem->id}",
            ['quantity' => 5],
            ['X-Cart-Token' => $token],
        );

        $response->assertOk();
        $this->assertSame(5, $response->json('data.items.0.quantity'));
    }

    // --- DELETE /api/v1/cart/items/{cartItem} ---

    public function test_remove_item_works(): void
    {
        $product  = $this->activeProduct();
        $cartInit = $this->getJson('/api/v1/cart');
        $token    = $cartInit->json('data.cart_token');

        $this->postJson('/api/v1/cart/items', ['product_id' => $product->id, 'quantity' => 1], ['X-Cart-Token' => $token]);

        $cart     = Cart::where('session_id', $token)->first();
        $cartItem = $cart->cartItems()->first();

        $response = $this->deleteJson(
            "/api/v1/cart/items/{$cartItem->id}",
            [],
            ['X-Cart-Token' => $token],
        );

        $response->assertOk();
        $this->assertCount(0, $response->json('data.items'));
    }

    // --- totals ---

    public function test_totals_recalculate_after_add(): void
    {
        $product = $this->activeProduct(['base_price' => 2000]);

        $response = $this->postJson('/api/v1/cart/items', [
            'product_id' => $product->id,
            'quantity'   => 3,
        ]);

        $response->assertCreated();
        $this->assertSame(6000, $response->json('data.total_amount'));
    }

    public function test_totals_recalculate_after_update(): void
    {
        $product  = $this->activeProduct(['base_price' => 1000]);
        $cartInit = $this->getJson('/api/v1/cart');
        $token    = $cartInit->json('data.cart_token');

        $this->postJson('/api/v1/cart/items', ['product_id' => $product->id, 'quantity' => 1], ['X-Cart-Token' => $token]);

        $cart     = Cart::where('session_id', $token)->first();
        $cartItem = $cart->cartItems()->first();

        $response = $this->patchJson(
            "/api/v1/cart/items/{$cartItem->id}",
            ['quantity' => 4],
            ['X-Cart-Token' => $token],
        );

        $response->assertOk();
        $this->assertSame(4000, $response->json('data.total_amount'));
    }

    public function test_totals_recalculate_after_remove(): void
    {
        $product  = $this->activeProduct(['base_price' => 500]);
        $cartInit = $this->getJson('/api/v1/cart');
        $token    = $cartInit->json('data.cart_token');

        $this->postJson('/api/v1/cart/items', ['product_id' => $product->id, 'quantity' => 2], ['X-Cart-Token' => $token]);

        $cart     = Cart::where('session_id', $token)->first();
        $cartItem = $cart->cartItems()->first();

        $response = $this->deleteJson(
            "/api/v1/cart/items/{$cartItem->id}",
            [],
            ['X-Cart-Token' => $token],
        );

        $response->assertOk();
        $this->assertSame(0, $response->json('data.total_amount'));
    }

    // --- validation / purchasability ---

    public function test_cannot_add_draft_product(): void
    {
        $product = Product::factory()->create(['status' => 'draft']);

        $response = $this->postJson('/api/v1/cart/items', [
            'product_id' => $product->id,
            'quantity'   => 1,
        ]);

        $response->assertUnprocessable();
    }

    public function test_cannot_add_future_published_product(): void
    {
        $product = Product::factory()->create([
            'status'       => 'active',
            'published_at' => now()->addDay(),
        ]);

        $response = $this->postJson('/api/v1/cart/items', [
            'product_id' => $product->id,
            'quantity'   => 1,
        ]);

        $response->assertUnprocessable();
    }

    // --- ownership ---

    public function test_cannot_update_another_carts_item(): void
    {
        $product      = $this->activeProduct();
        $otherCart    = Cart::factory()->create(['status' => 'active', 'session_id' => \Str::uuid()]);
        $otherItem    = CartItem::factory()->create([
            'cart_id'    => $otherCart->id,
            'product_id' => $product->id,
        ]);

        $myCartInit = $this->getJson('/api/v1/cart');
        $myToken    = $myCartInit->json('data.cart_token');

        $response = $this->patchJson(
            "/api/v1/cart/items/{$otherItem->id}",
            ['quantity' => 5],
            ['X-Cart-Token' => $myToken],
        );

        $response->assertNotFound();
    }

    public function test_cannot_remove_another_carts_item(): void
    {
        $product   = $this->activeProduct();
        $otherCart = Cart::factory()->create(['status' => 'active', 'session_id' => \Str::uuid()]);
        $otherItem = CartItem::factory()->create([
            'cart_id'    => $otherCart->id,
            'product_id' => $product->id,
        ]);

        $myCartInit = $this->getJson('/api/v1/cart');
        $myToken    = $myCartInit->json('data.cart_token');

        $response = $this->deleteJson(
            "/api/v1/cart/items/{$otherItem->id}",
            [],
            ['X-Cart-Token' => $myToken],
        );

        $response->assertNotFound();
    }
}
