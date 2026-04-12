<?php

namespace Tests\Feature\Admin\Catalog;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class CategoryFilterTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::firstOrCreate(['name' => 'manage categories', 'guard_name' => 'web']);

        $this->admin = User::factory()->create();
        $this->admin->givePermissionTo('manage categories');
    }

    public function test_index_filters_categories_by_name(): void
    {
        $this->actingAs($this->admin);
        Category::factory()->create(['name' => 'Footwear', 'parent_id' => null]);
        Category::factory()->create(['name' => 'Accessories', 'parent_id' => null]);

        $response = $this->get(route('admin.catalog.categories.index', ['search' => 'Foot']));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('admin/catalog/categories/index')
            ->where('categories', fn ($data) => collect($data)->contains('name', 'Footwear'))
            ->where('categories', fn ($data) => collect($data)->doesntContain('name', 'Accessories'))
        );
    }

    public function test_index_filters_categories_by_active_state(): void
    {
        $this->actingAs($this->admin);
        Category::factory()->create(['name' => 'Active Cat', 'is_active' => true, 'parent_id' => null]);
        Category::factory()->create(['name' => 'Inactive Cat', 'is_active' => false, 'parent_id' => null]);

        $response = $this->get(route('admin.catalog.categories.index', ['is_active' => '1']));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('admin/catalog/categories/index')
            ->where('categories', fn ($data) => collect($data)->contains('name', 'Active Cat'))
            ->where('categories', fn ($data) => collect($data)->doesntContain('name', 'Inactive Cat'))
        );
    }

    public function test_index_passes_filter_props(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.catalog.categories.index', ['search' => 'shoes']));

        $response->assertInertia(fn ($page) => $page
            ->component('admin/catalog/categories/index')
            ->where('filters.search', 'shoes')
        );
    }
}
