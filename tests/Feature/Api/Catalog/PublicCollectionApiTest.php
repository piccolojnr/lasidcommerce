<?php

namespace Tests\Feature\Api\Catalog;

use App\Models\Collection;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    public function test_show_returns_404_for_inactive_collection(): void
    {
        $collection = Collection::factory()->create(['is_active' => false]);

        $this->getJson("/api/v1/catalog/collections/{$collection->slug}")
            ->assertNotFound();
    }
}
