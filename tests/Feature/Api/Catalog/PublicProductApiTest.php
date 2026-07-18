<?php

namespace Tests\Feature\Api\Catalog;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Product;
use App\Models\ProductOptionType;
use App\Models\ProductOptionValue;
use App\Models\ProductVariant;
use App\Models\StockItem;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicProductApiTest extends TestCase
{
    use RefreshDatabase;

    private function visibleProduct(array $attrs = []): Product
    {
        return Product::factory()->create(array_merge([
            'status' => 'active',
            'published_at' => now()->subDay(),
        ], $attrs));
    }

    // --- index: visibility ---

    public function test_index_returns_active_published_products(): void
    {
        $visible = $this->visibleProduct();

        $response = $this->getJson('/api/v1/catalog/products');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonFragment(['id' => $visible->id]);
    }

    public function test_index_excludes_draft_products(): void
    {
        $draft = Product::factory()->create(['status' => 'draft', 'published_at' => now()->subDay()]);

        $response = $this->getJson('/api/v1/catalog/products');

        $ids = collect($response->json('data'))->pluck('id');
        $this->assertNotContains($draft->id, $ids->toArray());
    }

    public function test_index_excludes_future_published_products(): void
    {
        $future = Product::factory()->create(['status' => 'active', 'published_at' => now()->addDay()]);

        $response = $this->getJson('/api/v1/catalog/products');

        $ids = collect($response->json('data'))->pluck('id');
        $this->assertNotContains($future->id, $ids->toArray());
    }

    public function test_index_includes_null_published_at_active_products(): void
    {
        $alwaysOn = Product::factory()->create(['status' => 'active', 'published_at' => null]);

        $response = $this->getJson('/api/v1/catalog/products');

        $response->assertOk()->assertJsonFragment(['id' => $alwaysOn->id]);
    }

    // --- index: filters ---

    public function test_index_filters_by_search_name(): void
    {
        $match = $this->visibleProduct(['name' => 'Classic Sneaker']);
        $other = $this->visibleProduct(['name' => 'Running Boot']);

        $response = $this->getJson('/api/v1/catalog/products?search=Sneaker');

        $ids = collect($response->json('data'))->pluck('id');
        $this->assertContains($match->id, $ids->toArray());
        $this->assertNotContains($other->id, $ids->toArray());
    }

    public function test_index_search_is_case_insensitive(): void
    {
        $match = $this->visibleProduct(['name' => 'Classic Sneaker']);
        $other = $this->visibleProduct(['name' => 'Running Boot']);

        $response = $this->getJson('/api/v1/catalog/products?search=sNeAkEr');

        $ids = collect($response->json('data'))->pluck('id');
        $this->assertContains($match->id, $ids->toArray());
        $this->assertNotContains($other->id, $ids->toArray());
    }

    public function test_index_filters_by_category_slug(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        $match = $this->visibleProduct(['category_id' => $category->id]);
        $other = $this->visibleProduct(['category_id' => null]);

        $response = $this->getJson("/api/v1/catalog/products?category={$category->slug}");

        $ids = collect($response->json('data'))->pluck('id');
        $this->assertContains($match->id, $ids->toArray());
        $this->assertNotContains($other->id, $ids->toArray());
    }

    public function test_index_filters_parent_category_including_descendants(): void
    {
        $parent = Category::factory()->create(['is_active' => true]);
        $child = Category::factory()->create([
            'is_active' => true,
            'parent_id' => $parent->id,
        ]);
        $grandchild = Category::factory()->create([
            'is_active' => true,
            'parent_id' => $child->id,
        ]);

        $directMatch = $this->visibleProduct(['category_id' => $parent->id]);
        $childMatch = $this->visibleProduct(['category_id' => $child->id]);
        $grandchildMatch = $this->visibleProduct(['category_id' => $grandchild->id]);
        $other = $this->visibleProduct();

        $response = $this->getJson("/api/v1/catalog/products?category={$parent->slug}");

        $ids = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertContains($directMatch->id, $ids);
        $this->assertContains($childMatch->id, $ids);
        $this->assertContains($grandchildMatch->id, $ids);
        $this->assertNotContains($other->id, $ids);
    }

    public function test_index_filters_by_brand_slug(): void
    {
        $brand = Brand::factory()->create(['is_active' => true]);
        $match = $this->visibleProduct(['brand_id' => $brand->id]);
        $other = $this->visibleProduct(['brand_id' => null]);

        $response = $this->getJson("/api/v1/catalog/products?brand={$brand->slug}");

        $ids = collect($response->json('data'))->pluck('id');
        $this->assertContains($match->id, $ids->toArray());
        $this->assertNotContains($other->id, $ids->toArray());
    }

    public function test_index_filters_featured_products(): void
    {
        $featured = $this->visibleProduct(['is_featured' => true]);
        $notFeatured = $this->visibleProduct(['is_featured' => false]);

        $response = $this->getJson('/api/v1/catalog/products?featured=1');

        $ids = collect($response->json('data'))->pluck('id');
        $this->assertContains($featured->id, $ids->toArray());
        $this->assertNotContains($notFeatured->id, $ids->toArray());
    }

    public function test_index_filters_by_tag_slug(): void
    {
        $tag = Tag::factory()->create(['is_active' => true]);
        $match = $this->visibleProduct();
        $match->tags()->attach($tag);
        $other = $this->visibleProduct();

        $response = $this->getJson("/api/v1/catalog/products?tag={$tag->slug}");

        $ids = collect($response->json('data'))->pluck('id');
        $this->assertContains($match->id, $ids->toArray());
        $this->assertNotContains($other->id, $ids->toArray());
    }

    public function test_index_filters_by_collection_slug(): void
    {
        $collection = Collection::factory()->create(['is_active' => true]);
        $match = $this->visibleProduct();
        $match->collections()->attach($collection, ['sort_order' => 10]);
        $other = $this->visibleProduct();

        $response = $this->getJson("/api/v1/catalog/products?collection={$collection->slug}");

        $ids = collect($response->json('data'))->pluck('id');
        $this->assertContains($match->id, $ids->toArray());
        $this->assertNotContains($other->id, $ids->toArray());
    }

    // --- index: sorting ---

    public function test_index_sort_by_price_asc(): void
    {
        $cheap = $this->visibleProduct(['base_price' => 1000]);
        $expensive = $this->visibleProduct(['base_price' => 9000]);

        $response = $this->getJson('/api/v1/catalog/products?sort=price_asc');

        $prices = collect($response->json('data'))->pluck('base_price')->toArray();
        $this->assertEquals(array_values($prices), $prices); // already ordered
        $this->assertLessThanOrEqual($prices[1] ?? PHP_INT_MAX, $prices[0]);
    }

    public function test_index_sort_by_price_desc(): void
    {
        $cheap = $this->visibleProduct(['base_price' => 1000]);
        $expensive = $this->visibleProduct(['base_price' => 9000]);

        $response = $this->getJson('/api/v1/catalog/products?sort=price_desc');

        $prices = collect($response->json('data'))->pluck('base_price')->toArray();
        $this->assertGreaterThanOrEqual($prices[1] ?? 0, $prices[0]);
    }

    // --- index: pagination ---

    public function test_index_returns_pagination_meta(): void
    {
        $this->visibleProduct();

        $response = $this->getJson('/api/v1/catalog/products');

        $response->assertOk()->assertJsonStructure([
            'meta' => ['current_page', 'last_page', 'per_page', 'total', 'from', 'to', 'path'],
        ]);
    }

    public function test_index_response_shape(): void
    {
        $this->visibleProduct();

        $response = $this->getJson('/api/v1/catalog/products');

        $response->assertOk()->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'slug',
                    'sku',
                    'base_price',
                    'is_featured',
                    'stock',
                    'primary_image_url',
                    'primary_image_thumb_url',
                    'primary_image_card_url',
                    'primary_image_gallery_url',
                ],
            ],
            'meta',
        ]);
    }

    public function test_index_includes_tags_collections_and_computed_badges(): void
    {
        $tag = Tag::factory()->create(['name' => 'Editor Pick', 'slug' => 'editor-pick']);
        $collection = Collection::factory()->create(['name' => 'Top Picks', 'slug' => 'top-picks']);
        $product = $this->visibleProduct([
            'base_price' => 1000,
            'compare_at_price' => 1500,
            'published_at' => now()->subDays(2),
        ]);
        $product->tags()->attach($tag);
        $product->collections()->attach($collection, ['sort_order' => 10]);

        $response = $this->getJson('/api/v1/catalog/products');

        $item = collect($response->json('data'))->firstWhere('id', $product->id);
        $this->assertNotNull($item);
        $this->assertSame('editor-pick', $item['tags'][0]['slug']);
        $this->assertSame('top-picks', $item['collections'][0]['slug']);
        $this->assertContains('new_arrival', collect($item['badges'])->pluck('key')->all());
        $this->assertContains('on_sale', collect($item['badges'])->pluck('key')->all());
    }

    public function test_index_includes_primary_image_conversion_urls(): void
    {
        Storage::fake('media');
        $product = $this->visibleProduct();
        $media = $product
            ->addMedia(UploadedFile::fake()->image('photo.jpg', 1600, 1200))
            ->toMediaCollection(Product::IMAGE_COLLECTION);
        $media = $media->fresh();

        $response = $this->getJson('/api/v1/catalog/products');

        $item = collect($response->json('data'))->firstWhere('id', $product->id);
        $this->assertNotNull($item);
        $this->assertSame($media->getUrl(), $item['primary_image_url']);
        $this->assertArrayHasKey('primary_image_thumb_url', $item);
        $this->assertArrayHasKey('primary_image_card_url', $item);
        $this->assertArrayHasKey('primary_image_gallery_url', $item);
    }

    // --- show ---

    public function test_show_returns_visible_product_by_slug(): void
    {
        $product = $this->visibleProduct();

        $response = $this->getJson("/api/v1/catalog/products/{$product->slug}");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $product->id)
            ->assertJsonPath('data.slug', $product->slug);
    }

    public function test_show_returns_404_for_draft_product(): void
    {
        $draft = Product::factory()->create(['status' => 'draft']);

        $response = $this->getJson("/api/v1/catalog/products/{$draft->slug}");

        $response->assertNotFound()->assertJsonPath('success', false);
    }

    public function test_show_returns_404_for_future_published_product(): void
    {
        $future = Product::factory()->create([
            'status' => 'active',
            'published_at' => now()->addDay(),
        ]);

        $response = $this->getJson("/api/v1/catalog/products/{$future->slug}");

        $response->assertNotFound();
    }

    public function test_show_returns_404_for_nonexistent_slug(): void
    {
        $response = $this->getJson('/api/v1/catalog/products/does-not-exist');

        $response->assertNotFound();
    }

    public function test_show_response_includes_images_array(): void
    {
        $product = $this->visibleProduct();

        $response = $this->getJson("/api/v1/catalog/products/{$product->slug}");

        $response->assertOk()->assertJsonPath('data.images', []);
    }

    public function test_show_includes_image_conversion_urls(): void
    {
        Storage::fake('media');
        $product = $this->visibleProduct();
        $media = $product
            ->addMedia(UploadedFile::fake()->image('photo.jpg', 1600, 1200))
            ->toMediaCollection(Product::IMAGE_COLLECTION);
        $media = $media->fresh();

        $response = $this->getJson("/api/v1/catalog/products/{$product->slug}");

        $response->assertOk()
            ->assertJsonPath('data.images.0.id', $media->id)
            ->assertJsonPath('data.images.0.url', $media->getUrl());

        $image = $response->json('data.images.0');
        $this->assertArrayHasKey('thumb_url', $image);
        $this->assertArrayHasKey('card_url', $image);
        $this->assertArrayHasKey('gallery_url', $image);
    }

    public function test_show_includes_category_when_present(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        $product = $this->visibleProduct(['category_id' => $category->id]);

        $response = $this->getJson("/api/v1/catalog/products/{$product->slug}");

        $response->assertOk()
            ->assertJsonPath('data.category.id', $category->id)
            ->assertJsonPath('data.category.slug', $category->slug);
    }

    public function test_show_includes_brand_when_present(): void
    {
        $brand = Brand::factory()->create(['is_active' => true]);
        $product = $this->visibleProduct(['brand_id' => $brand->id]);

        $response = $this->getJson("/api/v1/catalog/products/{$product->slug}");

        $response->assertOk()
            ->assertJsonPath('data.brand.id', $brand->id)
            ->assertJsonPath('data.brand.slug', $brand->slug);
    }

    public function test_show_includes_low_stock_summary(): void
    {
        $product = $this->visibleProduct();

        StockItem::query()->create([
            'product_id' => $product->id,
            'quantity_on_hand' => 5,
            'quantity_reserved' => 2,
            'reorder_level' => 3,
        ]);

        $response = $this->getJson("/api/v1/catalog/products/{$product->slug}");

        $response->assertOk()
            ->assertJsonPath('data.stock.quantity', 3)
            ->assertJsonPath('data.stock.status', 'low_stock')
            ->assertJsonPath('data.stock.is_backorderable', false);
    }

    public function test_show_includes_out_of_stock_summary_when_inventory_is_depleted(): void
    {
        $product = $this->visibleProduct();

        StockItem::query()->create([
            'product_id' => $product->id,
            'quantity_on_hand' => 1,
            'quantity_reserved' => 1,
            'reorder_level' => 1,
        ]);

        $response = $this->getJson("/api/v1/catalog/products/{$product->slug}");

        $response->assertOk()
            ->assertJsonPath('data.stock.quantity', 0)
            ->assertJsonPath('data.stock.status', 'out_of_stock')
            ->assertJsonPath('data.stock.is_backorderable', false);
    }

    public function test_show_includes_backorderable_stock_summary(): void
    {
        $product = $this->visibleProduct([
            'allow_backorders' => true,
        ]);

        StockItem::query()->create([
            'product_id' => $product->id,
            'quantity_on_hand' => 0,
            'quantity_reserved' => 0,
            'reorder_level' => 1,
        ]);

        $response = $this->getJson("/api/v1/catalog/products/{$product->slug}");

        $response->assertOk()
            ->assertJsonPath('data.stock.quantity', 0)
            ->assertJsonPath('data.stock.status', 'in_stock')
            ->assertJsonPath('data.stock.is_backorderable', true);
    }

    public function test_show_includes_option_types_and_variant_option_matrix(): void
    {
        $product = $this->visibleProduct();
        $size = ProductOptionType::factory()->create([
            'product_id' => $product->id,
            'name' => 'Size',
        ]);
        $medium = ProductOptionValue::factory()->create([
            'option_type_id' => $size->id,
            'value' => 'Medium',
        ]);
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'name' => 'Medium',
            'sku' => 'PUBLIC-MED',
            'price' => 3500,
            'is_active' => true,
        ]);
        $variant->optionValues()->attach($medium);
        StockItem::query()->create([
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity_on_hand' => 4,
            'quantity_reserved' => 1,
            'reorder_level' => 1,
        ]);

        $response = $this->getJson("/api/v1/catalog/products/{$product->slug}");

        $response->assertOk()
            ->assertJsonPath('data.option_types.0.name', 'Size')
            ->assertJsonPath('data.option_types.0.values.0.value', 'Medium')
            ->assertJsonPath('data.variants.0.sku', 'PUBLIC-MED')
            ->assertJsonPath('data.variants.0.option_values.0.option_type_name', 'Size')
            ->assertJsonPath('data.variants.0.stock.quantity', 3)
            ->assertJsonPath('data.variants.0.stock.status', 'in_stock');
    }

    public function test_show_includes_related_products_from_same_category(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        $product = $this->visibleProduct(['category_id' => $category->id]);
        $related = $this->visibleProduct(['category_id' => $category->id]);
        $unrelated = $this->visibleProduct(['category_id' => null]);

        $response = $this->getJson("/api/v1/catalog/products/{$product->slug}");

        $response->assertOk();
        $relatedIds = collect($response->json('data.related_products'))->pluck('id')->toArray();
        $this->assertContains($related->id, $relatedIds);
        $this->assertNotContains($product->id, $relatedIds);
        $this->assertNotContains($unrelated->id, $relatedIds);
    }

    public function test_show_related_products_excludes_inactive(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        $product = $this->visibleProduct(['category_id' => $category->id]);
        $draft = Product::factory()->create([
            'status' => 'draft',
            'category_id' => $category->id,
        ]);

        $response = $this->getJson("/api/v1/catalog/products/{$product->slug}");

        $relatedIds = collect($response->json('data.related_products'))->pluck('id')->toArray();
        $this->assertNotContains($draft->id, $relatedIds);
    }

    public function test_show_response_shape(): void
    {
        $product = $this->visibleProduct();

        $response = $this->getJson("/api/v1/catalog/products/{$product->slug}");

        $response->assertOk()->assertJsonStructure([
            'data' => [
                'id', 'name', 'slug', 'sku', 'product_type',
                'short_description', 'description',
                'base_price', 'compare_at_price',
                'is_featured', 'badges', 'stock', 'tags', 'collections', 'track_inventory', 'allow_backorders',
                'published_at', 'images', 'option_types', 'variants', 'related_products',
            ],
        ]);

        $response->assertJsonStructure([
            'data' => [
                'images' => [
                    '*' => ['id', 'url', 'thumb_url', 'card_url', 'gallery_url', 'is_primary'],
                ],
            ],
        ]);
    }
}
