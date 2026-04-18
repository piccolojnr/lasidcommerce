<?php

namespace Tests\Feature\Api\Catalog;

use App\Models\Product;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicTagApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_active_tags_only(): void
    {
        $active = Tag::factory()->create(['is_active' => true]);
        $inactive = Tag::factory()->create(['is_active' => false]);

        $response = $this->getJson('/api/v1/catalog/tags');

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        $this->assertContains($active->id, $ids);
        $this->assertNotContains($inactive->id, $ids);
    }

    public function test_show_returns_products_for_tag(): void
    {
        $tag = Tag::factory()->create(['is_active' => true]);
        $match = Product::factory()->create(['status' => 'active', 'published_at' => now()->subDay()]);
        $match->tags()->attach($tag);
        $other = Product::factory()->create(['status' => 'active', 'published_at' => now()->subDay()]);

        $response = $this->getJson("/api/v1/catalog/tags/{$tag->slug}");

        $response->assertOk()
            ->assertJsonPath('data.tag.id', $tag->id);
        $ids = collect($response->json('data.products'))->pluck('id')->all();
        $this->assertContains($match->id, $ids);
        $this->assertNotContains($other->id, $ids);
    }

    public function test_show_includes_conversion_aware_primary_image_fields_for_products(): void
    {
        Storage::fake('media');
        $tag = Tag::factory()->create(['is_active' => true]);
        $product = Product::factory()->create(['status' => 'active', 'published_at' => now()->subDay()]);
        $product->tags()->attach($tag);
        $media = $product
            ->addMedia(UploadedFile::fake()->image('photo.jpg', 1600, 1200))
            ->toMediaCollection(Product::IMAGE_COLLECTION);
        $media = $media->fresh();

        $response = $this->getJson("/api/v1/catalog/tags/{$tag->slug}");

        $item = collect($response->json('data.products'))->firstWhere('id', $product->id);
        $this->assertNotNull($item);
        $this->assertSame($media->getUrl(), $item['primary_image_url']);
        $this->assertArrayHasKey('primary_image_thumb_url', $item);
        $this->assertArrayHasKey('primary_image_card_url', $item);
        $this->assertArrayHasKey('primary_image_gallery_url', $item);
    }

    public function test_show_returns_404_for_inactive_tag(): void
    {
        $tag = Tag::factory()->create(['is_active' => false]);

        $this->getJson("/api/v1/catalog/tags/{$tag->slug}")
            ->assertNotFound();
    }
}
