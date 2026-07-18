<?php

namespace Tests\Feature\Admin\Catalog;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Product;
use App\Models\StockItem;
use App\Models\StockMovement;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\Conversions\Jobs\PerformConversionsJob;
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
            ->has('tags')
            ->has('collections')
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

        $product = Product::query()->where('sku', 'SNK-001')->firstOrFail();

        $response->assertRedirect(route('admin.catalog.products.edit', $product));
        $this->assertDatabaseHas('products', ['name' => 'Classic Sneaker', 'slug' => 'classic-sneaker']);
    }

    public function test_admin_can_create_product_with_initial_stock(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('admin.catalog.products.store'), [
            'name' => 'Stocked Sneaker',
            'sku' => 'SNK-STOCK-001',
            'status' => 'draft',
            'product_type' => 'physical',
            'base_price' => 25000,
            'track_inventory' => true,
            'initial_quantity_on_hand' => 18,
            'initial_reorder_level' => 4,
            'initial_stock_note' => 'Opening stock count.',
        ]);

        $product = Product::query()->where('sku', 'SNK-STOCK-001')->firstOrFail();

        $response->assertRedirect(route('admin.catalog.products.edit', $product));
        $this->assertDatabaseHas('stock_items', [
            'product_id' => $product->id,
            'product_variant_id' => null,
            'quantity_on_hand' => 18,
            'quantity_reserved' => 0,
            'reorder_level' => 4,
        ]);
        $this->assertDatabaseHas('stock_movements', [
            'type' => StockMovement::TYPE_CORRECTION_ADD,
            'quantity' => 18,
            'reference_type' => 'product_create',
            'reference_id' => $product->id,
            'note' => 'Opening stock count.',
            'created_by' => $this->admin->id,
        ]);
    }

    public function test_admin_can_create_product_with_tags_and_collections(): void
    {
        $this->actingAs($this->admin);
        $tag = Tag::factory()->create();
        $collection = Collection::factory()->create();

        $this->post(route('admin.catalog.products.store'), [
            'name' => 'Tagged Sneaker',
            'sku' => 'SNK-101',
            'status' => 'draft',
            'product_type' => 'physical',
            'base_price' => 25000,
            'tag_ids' => [$tag->id],
            'collection_ids' => [$collection->id],
        ])->assertRedirect();

        $product = Product::query()->where('sku', 'SNK-101')->firstOrFail();

        $this->assertDatabaseHas('products', ['id' => $product->id]);

        $this->assertDatabaseHas('product_tag', ['product_id' => $product->id, 'tag_id' => $tag->id]);
        $this->assertDatabaseHas('collection_product', ['product_id' => $product->id, 'collection_id' => $collection->id]);
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
            ->has('tags')
            ->has('collections')
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

    public function test_admin_can_update_product_tags_and_collections(): void
    {
        $this->actingAs($this->admin);
        $product = Product::factory()->create();
        $tag = Tag::factory()->create();
        $collection = Collection::factory()->create();

        $this->put(route('admin.catalog.products.update', $product), [
            'name' => $product->name,
            'sku' => $product->sku,
            'status' => 'draft',
            'product_type' => 'physical',
            'base_price' => $product->base_price,
            'tag_ids' => [$tag->id],
            'collection_ids' => [$collection->id],
        ])->assertRedirect();

        $this->assertDatabaseHas('product_tag', ['product_id' => $product->id, 'tag_id' => $tag->id]);
        $this->assertDatabaseHas('collection_product', ['product_id' => $product->id, 'collection_id' => $collection->id]);
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
        StockItem::query()->create([
            'product_id' => $product->id,
            'quantity_on_hand' => 7,
            'quantity_reserved' => 2,
            'reorder_level' => 4,
        ]);

        $response = $this->get(route('admin.catalog.products.show', $product));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('admin/catalog/products/show')
            ->where('product.inventory.available_quantity', 5)
            ->where('product.inventory.status', 'in_stock')
        );
    }

    public function test_index_and_show_include_admin_product_image_conversion_urls(): void
    {
        Storage::fake('media');
        $this->actingAs($this->admin);

        $product = Product::factory()->create();
        $media = $product
            ->addMedia(UploadedFile::fake()->image('photo.jpg', 1600, 1200))
            ->toMediaCollection(Product::IMAGE_COLLECTION);
        $media = $media->fresh();

        $indexResponse = $this->get(route('admin.catalog.products.index'));

        $indexResponse->assertInertia(fn ($page) => $page
            ->component('admin/catalog/products/index')
            ->where('products.data.0.id', $product->id)
            ->where('products.data.0.images.0.thumb_url', $media->getAvailableUrl([Product::IMAGE_CONVERSION_THUMB]))
            ->where('products.data.0.images.0.card_url', $media->getAvailableUrl([Product::IMAGE_CONVERSION_CARD]))
            ->where('products.data.0.images.0.gallery_url', $media->getAvailableUrl([Product::IMAGE_CONVERSION_GALLERY]))
        );

        $showResponse = $this->get(route('admin.catalog.products.show', $product));

        $showResponse->assertInertia(fn ($page) => $page
            ->component('admin/catalog/products/show')
            ->where('product.images.0.thumb_url', $media->getAvailableUrl([Product::IMAGE_CONVERSION_THUMB]))
            ->where('product.images.0.card_url', $media->getAvailableUrl([Product::IMAGE_CONVERSION_CARD]))
            ->where('product.images.0.gallery_url', $media->getAvailableUrl([Product::IMAGE_CONVERSION_GALLERY]))
        );
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
            'email' => 'test@example.com',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        \DB::table('order_items')->insert([
            'order_id' => $orderId,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'sku' => $product->sku,
            'quantity' => 1,
            'unit_price' => $product->base_price,
            'line_total' => $product->base_price,
            'created_at' => now(),
            'updated_at' => now(),
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

    public function test_index_filters_by_search_name(): void
    {
        $this->actingAs($this->admin);
        $match = Product::factory()->create(['name' => 'Classic Sneaker']);
        $other = Product::factory()->create(['name' => 'Running Boot']);

        $response = $this->get(route('admin.catalog.products.index', ['search' => 'Sneaker']));

        $response->assertInertia(fn ($page) => $page
            ->where('products.data', fn ($data) => collect($data)->contains('id', $match->id))
            ->where('products.data', fn ($data) => collect($data)->doesntContain('id', $other->id))
        );
    }

    public function test_index_filters_by_sku(): void
    {
        $this->actingAs($this->admin);
        $match = Product::factory()->create(['sku' => 'SNK-FIND-001']);
        $other = Product::factory()->create(['sku' => 'BOOT-999']);

        $response = $this->get(route('admin.catalog.products.index', ['search' => 'SNK-FIND']));

        $response->assertInertia(fn ($page) => $page
            ->where('products.data', fn ($data) => collect($data)->contains('id', $match->id))
            ->where('products.data', fn ($data) => collect($data)->doesntContain('id', $other->id))
        );
    }

    public function test_index_filters_by_status(): void
    {
        $this->actingAs($this->admin);
        $active = Product::factory()->create(['status' => 'active']);
        $draft = Product::factory()->create(['status' => 'draft']);

        $response = $this->get(route('admin.catalog.products.index', ['status' => 'active']));

        $response->assertInertia(fn ($page) => $page
            ->where('products.data', fn ($data) => collect($data)->contains('id', $active->id))
            ->where('products.data', fn ($data) => collect($data)->doesntContain('id', $draft->id))
        );
    }

    public function test_index_filters_by_category(): void
    {
        $this->actingAs($this->admin);
        $category = Category::factory()->create();
        $match = Product::factory()->create(['category_id' => $category->id]);
        $other = Product::factory()->create(['category_id' => null]);

        $response = $this->get(route('admin.catalog.products.index', ['category_id' => $category->id]));

        $response->assertInertia(fn ($page) => $page
            ->where('products.data', fn ($data) => collect($data)->contains('id', $match->id))
            ->where('products.data', fn ($data) => collect($data)->doesntContain('id', $other->id))
        );
    }

    public function test_index_filters_by_brand(): void
    {
        $this->actingAs($this->admin);
        $brand = Brand::factory()->create();
        $match = Product::factory()->create(['brand_id' => $brand->id]);
        $other = Product::factory()->create(['brand_id' => null]);

        $response = $this->get(route('admin.catalog.products.index', ['brand_id' => $brand->id]));

        $response->assertInertia(fn ($page) => $page
            ->where('products.data', fn ($data) => collect($data)->contains('id', $match->id))
            ->where('products.data', fn ($data) => collect($data)->doesntContain('id', $other->id))
        );
    }

    public function test_index_filters_by_tag(): void
    {
        $this->actingAs($this->admin);
        $tag = Tag::factory()->create();
        $match = Product::factory()->create();
        $match->tags()->attach($tag);
        $other = Product::factory()->create();

        $response = $this->get(route('admin.catalog.products.index', ['tag_id' => $tag->id]));

        $response->assertInertia(fn ($page) => $page
            ->where('products.data', fn ($data) => collect($data)->contains('id', $match->id))
            ->where('products.data', fn ($data) => collect($data)->doesntContain('id', $other->id))
        );
    }

    public function test_index_filters_by_collection(): void
    {
        $this->actingAs($this->admin);
        $collection = Collection::factory()->create();
        $match = Product::factory()->create();
        $match->collections()->attach($collection, ['sort_order' => 10]);
        $other = Product::factory()->create();

        $response = $this->get(route('admin.catalog.products.index', ['collection_id' => $collection->id]));

        $response->assertInertia(fn ($page) => $page
            ->where('products.data', fn ($data) => collect($data)->contains('id', $match->id))
            ->where('products.data', fn ($data) => collect($data)->doesntContain('id', $other->id))
        );
    }

    public function test_index_passes_filter_values_as_props(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.catalog.products.index', [
            'search' => 'shoe',
            'status' => 'active',
        ]));

        $response->assertInertia(fn ($page) => $page
            ->component('admin/catalog/products/index')
            ->where('filters.search', 'shoe')
            ->where('filters.status', 'active')
            ->has('categories')
            ->has('brands')
            ->has('tags')
            ->has('collections')
        );
    }

    // --- Permission denial ---

    public function test_user_without_permission_cannot_view_products_index(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.catalog.products.index'));

        $response->assertForbidden();
    }

    public function test_user_without_permission_cannot_access_create_form(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.catalog.products.create'));

        $response->assertForbidden();
    }

    public function test_user_without_permission_cannot_delete_product(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $response = $this->actingAs($user)->delete(route('admin.catalog.products.destroy', $product));

        $response->assertForbidden();
    }

    // --- Media upload ---

    public function test_store_attaches_uploaded_images(): void
    {
        Storage::fake('media');
        $this->actingAs($this->admin);

        $this->post(route('admin.catalog.products.store'), [
            'name' => 'Image Product',
            'sku' => 'IMG-001',
            'status' => 'draft',
            'product_type' => 'physical',
            'base_price' => 1000,
            'images' => [UploadedFile::fake()->image('photo.jpg')],
        ]);

        $product = Product::where('sku', 'IMG-001')->first();
        $this->assertCount(1, $product->getMedia('images'));
    }

    public function test_store_queues_product_image_conversions(): void
    {
        Storage::fake('media');
        Queue::fake();
        $this->actingAs($this->admin);

        $this->post(route('admin.catalog.products.store'), [
            'name' => 'Queued Image Product',
            'sku' => 'IMG-QUEUED-001',
            'status' => 'draft',
            'product_type' => 'physical',
            'base_price' => 1000,
            'images' => [UploadedFile::fake()->image('photo.jpg', 1600, 1200)],
        ])->assertRedirect();

        Queue::assertPushed(PerformConversionsJob::class);
    }

    public function test_product_images_register_expected_conversion_names(): void
    {
        Storage::fake('media');
        $product = Product::factory()->create();

        $media = $product
            ->addMedia(UploadedFile::fake()->image('photo.jpg', 1600, 1200))
            ->toMediaCollection(Product::IMAGE_COLLECTION);

        $this->assertSame(
            [
                Product::IMAGE_CONVERSION_THUMB,
                Product::IMAGE_CONVERSION_CARD,
                Product::IMAGE_CONVERSION_GALLERY,
            ],
            $media->getMediaConversionNames(),
        );
    }

    public function test_update_removes_specified_images(): void
    {
        Storage::fake('media');
        $this->actingAs($this->admin);
        $product = Product::factory()->create();
        $media = $product->addMedia(UploadedFile::fake()->image('old.jpg'))
            ->toMediaCollection('images');

        $this->put(route('admin.catalog.products.update', $product), [
            'name' => $product->name,
            'sku' => $product->sku,
            'status' => 'draft',
            'product_type' => 'physical',
            'base_price' => $product->base_price,
            'remove_image_ids' => [$media->id],
        ]);

        $this->assertCount(0, $product->fresh()->getMedia('images'));
    }

    public function test_remove_image_ids_cannot_delete_another_products_image(): void
    {
        Storage::fake('media');
        $this->actingAs($this->admin);
        $target = Product::factory()->create();
        $other = Product::factory()->create();
        $otherMedia = $other->addMedia(UploadedFile::fake()->image('other.jpg'))
            ->toMediaCollection('images');

        $this->put(route('admin.catalog.products.update', $target), [
            'name' => $target->name,
            'sku' => $target->sku,
            'status' => 'draft',
            'product_type' => 'physical',
            'base_price' => $target->base_price,
            'remove_image_ids' => [$otherMedia->id],
        ]);

        $this->assertCount(1, $other->fresh()->getMedia('images'));
    }
}
