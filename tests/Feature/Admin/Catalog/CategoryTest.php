<?php

namespace Tests\Feature\Admin\Catalog;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class CategoryTest extends TestCase
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

    public function test_guests_are_redirected_from_categories_index(): void
    {
        $response = $this->get(route('admin.catalog.categories.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_admin_can_view_categories_index(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.catalog.categories.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('admin/catalog/categories/index'));
    }

    public function test_admin_can_view_create_form(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.catalog.categories.create'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('admin/catalog/categories/create'));
    }

    public function test_admin_can_create_category(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('admin.catalog.categories.store'), [
            'name' => 'Footwear',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('admin.catalog.categories.index'));
        $this->assertDatabaseHas('categories', ['name' => 'Footwear', 'slug' => 'footwear']);
    }

    public function test_store_auto_generates_slug_from_name(): void
    {
        $this->actingAs($this->admin);

        $this->post(route('admin.catalog.categories.store'), [
            'name' => 'New Arrivals',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('categories', ['slug' => 'new-arrivals']);
    }

    public function test_store_uses_provided_slug(): void
    {
        $this->actingAs($this->admin);

        $this->post(route('admin.catalog.categories.store'), [
            'name' => 'Footwear',
            'slug' => 'custom-slug',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('categories', ['slug' => 'custom-slug']);
    }

    public function test_admin_can_view_edit_form(): void
    {
        $this->actingAs($this->admin);
        $category = Category::factory()->create();

        $response = $this->get(route('admin.catalog.categories.edit', $category));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('admin/catalog/categories/edit'));
    }

    public function test_admin_can_update_category(): void
    {
        $this->actingAs($this->admin);
        $category = Category::factory()->create(['name' => 'Old Name', 'slug' => 'old-name']);

        $response = $this->put(route('admin.catalog.categories.update', $category), [
            'name' => 'New Name',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('admin.catalog.categories.index'));
        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'New Name']);
    }

    public function test_admin_can_toggle_category_status(): void
    {
        $this->actingAs($this->admin);
        $category = Category::factory()->create(['is_active' => true]);

        $response = $this->patch(route('admin.catalog.categories.toggle-status', $category));

        $response->assertRedirect();
        $this->assertDatabaseHas('categories', ['id' => $category->id, 'is_active' => false]);
    }

    public function test_admin_can_view_category_show(): void
    {
        $this->actingAs($this->admin);
        $category = Category::factory()->create();

        $response = $this->get(route('admin.catalog.categories.show', $category));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('admin/catalog/categories/show'));
    }
}
