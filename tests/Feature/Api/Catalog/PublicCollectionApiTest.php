<?php

namespace Tests\Feature\Api\Catalog;

use App\Models\Collection;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicCollectionApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_active_collections_only(): void
    {
        $active = Collection::factory()->create(['is_active' => true]);
        $inactive = Collection::factory()->create(['is_active' => false]);

        $response = $this->getJson('/api/v1/catalog/collections');

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        $this->assertContains($active->id, $ids);
        $this->assertNotContains($inactive->id, $ids);
    }

    public function test_show_returns_products_for_collection(): void
    {
        $collection = Collection::factory()->create(['is_active' => true]);
        $match = Product::factory()->create(['status' => 'active', 'published_at' => now()->subDay()]);
        $match->collections()->attach($collection, ['sort_order' => 10]);
        $other = Product::factory()->create(['status' => 'active', 'published_at' => now()->subDay()]);

        $response = $this->getJson("/api/v1/catalog/collections/{$collection->slug}");

        $response->assertOk()
            ->assertJsonPath('data.collection.id', $collection->id);
        $ids = collect($response->json('data.products'))->pluck('id')->all();
        $this->assertContains($match->id, $ids);
        $this->assertNotContains($other->id, $ids);
    }

    public function test_show_includes_conversion_aware_primary_image_fields_for_products(): void
    {
        Storage::fake('media');
        $collection = Collection::factory()->create(['is_active' => true]);
        $product = Product::factory()->create(['status' => 'active', 'published_at' => now()->subDay()]);
        $product->collections()->attach($collection, ['sort_order' => 10]);
        $media = $product
            ->addMedia(UploadedFile::fake()->image('photo.jpg', 1600, 1200))
            ->toMediaCollection(Product::IMAGE_COLLECTION);
        $media = $media->fresh();

        $response = $this->getJson("/api/v1/catalog/collections/{$collection->slug}");

        $item = collect($response->json('data.products'))->firstWhere('id', $product->id);
        $this->assertNotNull($item);
        $this->assertSame($media->getUrl(), $item['primary_image_url']);
        $this->assertArrayHasKey('primary_image_thumb_url', $item);
        $this->assertArrayHasKey('primary_image_card_url', $item);
        $this->assertArrayHasKey('primary_image_gallery_url', $item);
    }

    public function test_show_returns_404_for_inactive_collection(): void
    {
        $collection = Collection::factory()->create(['is_active' => false]);

        $this->getJson("/api/v1/catalog/collections/{$collection->slug}")
            ->assertNotFound();
    }
}
