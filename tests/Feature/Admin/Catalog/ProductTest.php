<?php

namespace Tests\Feature\Admin\Catalog;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::firstOrCreate(['name' => 'manage products', 'guard_name' => 'web']);

        $this->admin = User::factory()->create();
        $this->admin->givePermissionTo('manage products');
    }

    public function test_guests_are_redirected_from_products_index(): void
    {
        $response = $this->get(route('admin.catalog.products.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_admin_can_view_products_index(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.catalog.products.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('admin/catalog/products/index'));
    }

    public function test_admin_can_view_create_form(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.catalog.products.create'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('admin/catalog/products/create')
            ->has('categories')
            ->has('brands')
        );
    }

    public function test_admin_can_create_product(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('admin.catalog.products.store'), [
            'name' => 'Classic Sneaker',
            'sku' => 'SNK-001',
            'status' => 'draft',
            'product_type' => 'physical',
            'base_price' => 25000,
        ]);

        $response->assertRedirect(route('admin.catalog.products.index'));
        $this->assertDatabaseHas('products', ['name' => 'Classic Sneaker', 'slug' => 'classic-sneaker']);
    }

    public function test_store_auto_generates_slug_from_name(): void
    {
        $this->actingAs($this->admin);

        $this->post(route('admin.catalog.products.store'), [
            'name' => 'Running Shoe Pro',
            'sku' => 'RSP-001',
            'status' => 'draft',
            'product_type' => 'physical',
            'base_price' => 15000,
        ]);

        $this->assertDatabaseHas('products', ['slug' => 'running-shoe-pro']);
    }

    public function test_store_uses_provided_slug(): void
    {
        $this->actingAs($this->admin);

        $this->post(route('admin.catalog.products.store'), [
            'name' => 'Classic Sneaker',
            'slug' => 'my-custom-slug',
            'sku' => 'SNK-002',
            'status' => 'draft',
            'product_type' => 'physical',
            'base_price' => 25000,
        ]);

        $this->assertDatabaseHas('products', ['slug' => 'my-custom-slug']);
    }

    public function test_admin_can_view_edit_form(): void
    {
        $this->actingAs($this->admin);
        $product = Product::factory()->create();

        $response = $this->get(route('admin.catalog.products.edit', $product));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('admin/catalog/products/edit')
            ->has('product')
            ->has('categories')
            ->has('brands')
        );
    }

    public function test_admin_can_update_product(): void
    {
        $this->actingAs($this->admin);
        $product = Product::factory()->create(['name' => 'Old Name', 'slug' => 'old-name']);

        $response = $this->put(route('admin.catalog.products.update', $product), [
            'name' => 'New Name',
            'sku' => $product->sku,
            'status' => 'draft',
            'product_type' => 'physical',
            'base_price' => $product->base_price,
        ]);

        $response->assertRedirect(route('admin.catalog.products.index'));
        $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'New Name']);
    }

    public function test_admin_can_toggle_product_status(): void
    {
        $this->actingAs($this->admin);
        $product = Product::factory()->create(['status' => 'draft']);

        $response = $this->patch(route('admin.catalog.products.toggle-status', $product));

        $response->assertRedirect();
        $this->assertDatabaseHas('products', ['id' => $product->id, 'status' => 'active']);
    }

    public function test_admin_can_view_product_show(): void
    {
        $this->actingAs($this->admin);
        $product = Product::factory()->create();

        $response = $this->get(route('admin.catalog.products.show', $product));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('admin/catalog/products/show'));
    }

    public function test_admin_can_soft_delete_product(): void
    {
        $this->actingAs($this->admin);
        $product = Product::factory()->create();

        $response = $this->delete(route('admin.catalog.products.destroy', $product));

        $response->assertRedirect(route('admin.catalog.products.index'));
        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }

    public function test_delete_is_blocked_when_product_has_order_items(): void
    {
        $this->actingAs($this->admin);
        $product = Product::factory()->create();

        // Insert a parent order first (required NOT NULL FK)
        $orderId = \DB::table('orders')->insertGetId([
            'order_number' => 'TEST-001',
            'email'        => 'test@example.com',
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);
        \DB::table('order_items')->insert([
            'order_id'     => $orderId,
            'product_id'   => $product->id,
            'product_name' => $product->name,
            'sku'          => $product->sku,
            'quantity'     => 1,
            'unit_price'   => $product->base_price,
            'line_total'   => $product->base_price,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        $response = $this->delete(route('admin.catalog.products.destroy', $product));

        $response->assertRedirect();
        $this->assertDatabaseHas('products', ['id' => $product->id, 'deleted_at' => null]);
    }

    public function test_guest_is_redirected_from_destroy(): void
    {
        $product = Product::factory()->create();

        $response = $this->delete(route('admin.catalog.products.destroy', $product));

        $response->assertRedirect(route('login'));
    }

    public function test_soft_deleted_product_excluded_from_index(): void
    {
        $this->actingAs($this->admin);
        $live = Product::factory()->create();
        $deleted = Product::factory()->create();
        $deleted->delete();

        $response = $this->get(route('admin.catalog.products.index'));

        $response->assertInertia(fn ($page) => $page
            ->component('admin/catalog/products/index')
            ->where('products.data', fn ($data) => collect($data)->contains('id', $live->id))
            ->where('products.data', fn ($data) => collect($data)->doesntContain('id', $deleted->id))
        );
    }

    public function test_create_form_passes_categories_and_brands(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.catalog.products.create'));

        $response->assertInertia(fn ($page) => $page
            ->component('admin/catalog/products/create')
            ->has('categories')
            ->has('brands')
        );
    }
}
