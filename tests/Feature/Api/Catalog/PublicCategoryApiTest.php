<?php

namespace Tests\Feature\Api\Catalog;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicCategoryApiTest extends TestCase
{
    use RefreshDatabase;

    // --- index ---

    public function test_index_returns_active_categories(): void
    {
        $active   = Category::factory()->create(['is_active' => true, 'parent_id' => null]);
        $inactive = Category::factory()->create(['is_active' => false, 'parent_id' => null]);

        $response = $this->getJson('/api/v1/catalog/categories');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonFragment(['id' => $active->id])
            ->assertJsonMissing(['id' => $inactive->id]);
    }

    public function test_index_excludes_inactive_categories(): void
    {
        Category::factory()->create(['name' => 'Visible', 'is_active' => true, 'parent_id' => null]);
        Category::factory()->create(['name' => 'Hidden', 'is_active' => false, 'parent_id' => null]);

        $response = $this->getJson('/api/v1/catalog/categories');

        $ids = collect($response->json('data'))->pluck('id');
        $this->assertNotContains(
            Category::where('name', 'Hidden')->first()->id,
            $ids,
        );
    }

    public function test_index_includes_children_by_default(): void
    {
        $parent = Category::factory()->create(['is_active' => true, 'parent_id' => null]);
        $child  = Category::factory()->create(['is_active' => true, 'parent_id' => $parent->id]);

        $response = $this->getJson('/api/v1/catalog/categories');

        $response->assertOk();

        $parentData = collect($response->json('data'))->firstWhere('id', $parent->id);
        $this->assertArrayHasKey('children', $parentData);
        $childIds = collect($parentData['children'])->pluck('id')->toArray();
        $this->assertContains($child->id, $childIds);
    }

    public function test_index_root_only_excludes_children_from_top_level(): void
    {
        $parent = Category::factory()->create(['is_active' => true, 'parent_id' => null]);
        $child  = Category::factory()->create(['is_active' => true, 'parent_id' => $parent->id]);

        $response = $this->getJson('/api/v1/catalog/categories?root_only=1');

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id');
        $this->assertContains($parent->id, $ids->toArray());
        $this->assertNotContains($child->id, $ids->toArray());
    }

    public function test_index_response_shape(): void
    {
        Category::factory()->create(['is_active' => true, 'parent_id' => null]);

        $response = $this->getJson('/api/v1/catalog/categories');

        $response->assertOk()->assertJsonStructure([
            'success',
            'data' => [
                '*' => ['id', 'name', 'slug', 'description', 'sort_order', 'image_url'],
            ],
        ]);
    }

    // --- show ---

    public function test_show_returns_active_category_by_slug(): void
    {
        $category = Category::factory()->create(['is_active' => true, 'parent_id' => null]);

        $response = $this->getJson("/api/v1/catalog/categories/{$category->slug}");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $category->id)
            ->assertJsonPath('data.slug', $category->slug);
    }

    public function test_show_returns_404_for_inactive_category(): void
    {
        $category = Category::factory()->create(['is_active' => false, 'parent_id' => null]);

        $response = $this->getJson("/api/v1/catalog/categories/{$category->slug}");

        $response->assertNotFound()
            ->assertJsonPath('success', false);
    }

    public function test_show_returns_404_for_nonexistent_slug(): void
    {
        $response = $this->getJson('/api/v1/catalog/categories/does-not-exist');

        $response->assertNotFound();
    }

    public function test_show_includes_active_children(): void
    {
        $parent       = Category::factory()->create(['is_active' => true, 'parent_id' => null]);
        $activeChild  = Category::factory()->create(['is_active' => true, 'parent_id' => $parent->id]);
        $inactiveChild = Category::factory()->create(['is_active' => false, 'parent_id' => $parent->id]);

        $response = $this->getJson("/api/v1/catalog/categories/{$parent->slug}");

        $response->assertOk();
        $childIds = collect($response->json('data.children'))->pluck('id')->toArray();
        $this->assertContains($activeChild->id, $childIds);
        $this->assertNotContains($inactiveChild->id, $childIds);
    }
}
