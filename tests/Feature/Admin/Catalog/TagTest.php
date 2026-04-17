<?php

namespace Tests\Feature\Admin\Catalog;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class TagTest extends TestCase
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

    public function test_admin_can_view_tags_index(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.catalog.tags.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('admin/catalog/tags/index'));
    }

    public function test_admin_can_create_tag(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.catalog.tags.store'), [
                'name' => 'Editor Pick',
                'is_active' => true,
            ])
            ->assertRedirect(route('admin.catalog.tags.index'));

        $this->assertDatabaseHas('tags', ['name' => 'Editor Pick', 'slug' => 'editor-pick']);
    }

    public function test_admin_can_update_tag(): void
    {
        $tag = Tag::factory()->create(['name' => 'Old']);

        $this->actingAs($this->admin)
            ->put(route('admin.catalog.tags.update', $tag), [
                'name' => 'New',
                'is_active' => true,
            ])
            ->assertRedirect(route('admin.catalog.tags.index'));

        $this->assertDatabaseHas('tags', ['id' => $tag->id, 'name' => 'New']);
    }
}
