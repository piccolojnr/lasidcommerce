<?php

namespace Tests\Feature\Api\Catalog;

use App\Models\Product;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    public function test_show_returns_404_for_inactive_tag(): void
    {
        $tag = Tag::factory()->create(['is_active' => false]);

        $this->getJson("/api/v1/catalog/tags/{$tag->slug}")
            ->assertNotFound();
    }
}
