<?php

namespace Tests\Feature\Admin\Catalog;

use App\Models\Collection;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class CollectionTest extends TestCase
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

    public function test_admin_can_view_collections_index(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.catalog.collections.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('admin/catalog/collections/index'));
    }

    public function test_admin_can_create_collection_with_products(): void
    {
        $product = Product::factory()->create();

        $this->actingAs($this->admin)
            ->post(route('admin.catalog.collections.store'), [
                'name' => 'Top Picks',
                'is_active' => true,
                'sort_order' => 10,
                'product_memberships' => [
                    ['product_id' => $product->id, 'sort_order' => 10],
                ],
            ])
            ->assertRedirect(route('admin.catalog.collections.index'));

        $collection = Collection::query()->where('slug', 'top-picks')->firstOrFail();
        $this->assertDatabaseHas('collection_product', [
            'collection_id' => $collection->id,
            'product_id' => $product->id,
            'sort_order' => 10,
        ]);
    }

    public function test_admin_can_update_collection(): void
    {
        $collection = Collection::factory()->create(['name' => 'Old Name']);

        $this->actingAs($this->admin)
            ->put(route('admin.catalog.collections.update', $collection), [
                'name' => 'New Name',
                'is_active' => true,
                'sort_order' => 20,
            ])
            ->assertRedirect(route('admin.catalog.collections.index'));

        $this->assertDatabaseHas('collections', ['id' => $collection->id, 'name' => 'New Name', 'sort_order' => 20]);
    }
}
