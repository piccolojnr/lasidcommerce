<?php

namespace Tests\Feature\Api\Catalog;

use App\Models\Brand;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicBrandApiTest extends TestCase
{
    use RefreshDatabase;

    private function visibleProduct(Brand $brand, array $attrs = []): Product
    {
        return Product::factory()->create(array_merge([
            'brand_id'      => $brand->id,
            'status'        => 'active',
            'published_at'  => now()->subDay(),
        ], $attrs));
    }

    public function test_index_returns_active_brands_only(): void
    {
        $active = Brand::factory()->create(['is_active' => true, 'name' => 'Adidas']);
        $inactive = Brand::factory()->create(['is_active' => false, 'name' => 'Nike']);

        $response = $this->getJson('/api/v1/catalog/brands');

        $response->assertOk()
            ->assertJsonPath('success', true);

        $ids = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertContains($active->id, $ids);
        $this->assertNotContains($inactive->id, $ids);
    }

    public function test_index_returns_brands_sorted_by_name(): void
    {
        $first = Brand::factory()->create(['is_active' => true, 'name' => 'Adidas']);
        $second = Brand::factory()->create(['is_active' => true, 'name' => 'Nike']);

        $response = $this->getJson('/api/v1/catalog/brands');

        $response->assertOk();

        $names = collect($response->json('data'))->pluck('name')->all();
        $this->assertSame([$first->name, $second->name], $names);
    }

    public function test_index_response_shape(): void
    {
        Brand::factory()->create(['is_active' => true]);

        $response = $this->getJson('/api/v1/catalog/brands');

        $response->assertOk()->assertJsonStructure([
            'success',
            'data' => [
                '*' => ['id', 'name', 'slug', 'description', 'image_url', 'products_count'],
            ],
        ]);
    }

    public function test_index_includes_visible_product_count(): void
    {
        $brand = Brand::factory()->create(['is_active' => true]);
        $this->visibleProduct($brand);
        $this->visibleProduct($brand, ['status' => 'draft']);

        $response = $this->getJson('/api/v1/catalog/brands');

        $response->assertOk();
        $brandData = collect($response->json('data'))->firstWhere('id', $brand->id);

        $this->assertSame(1, $brandData['products_count']);
    }

    public function test_show_returns_active_brand_by_slug(): void
    {
        $brand = Brand::factory()->create(['is_active' => true]);

        $response = $this->getJson("/api/v1/catalog/brands/{$brand->slug}");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $brand->id)
            ->assertJsonPath('data.slug', $brand->slug);
    }

    public function test_show_returns_404_for_inactive_brand(): void
    {
        $brand = Brand::factory()->create(['is_active' => false]);

        $response = $this->getJson("/api/v1/catalog/brands/{$brand->slug}");

        $response->assertNotFound()
            ->assertJsonPath('success', false);
    }

    public function test_show_returns_404_for_nonexistent_slug(): void
    {
        $response = $this->getJson('/api/v1/catalog/brands/does-not-exist');

        $response->assertNotFound();
    }

    public function test_show_includes_visible_product_count(): void
    {
        $brand = Brand::factory()->create(['is_active' => true]);
        $this->visibleProduct($brand);
        $this->visibleProduct($brand, ['status' => 'draft']);

        $response = $this->getJson("/api/v1/catalog/brands/{$brand->slug}");

        $response->assertOk()
            ->assertJsonPath('data.products_count', 1);
    }
}
