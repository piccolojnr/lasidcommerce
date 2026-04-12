<?php

namespace Tests\Feature\Admin\Catalog;

use App\Models\Brand;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class BrandTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::firstOrCreate(['name' => 'manage brands', 'guard_name' => 'web']);

        $this->admin = User::factory()->create();
        $this->admin->givePermissionTo('manage brands');
    }

    public function test_guests_are_redirected_from_brands_index(): void
    {
        $response = $this->get(route('admin.catalog.brands.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_admin_can_view_brands_index(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.catalog.brands.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('admin/catalog/brands/index'));
    }

    public function test_admin_can_view_create_form(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.catalog.brands.create'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('admin/catalog/brands/create'));
    }

    public function test_admin_can_create_brand(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('admin.catalog.brands.store'), [
            'name' => 'Nike',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('admin.catalog.brands.index'));
        $this->assertDatabaseHas('brands', ['name' => 'Nike', 'slug' => 'nike']);
    }

    public function test_store_auto_generates_slug_from_name(): void
    {
        $this->actingAs($this->admin);

        $this->post(route('admin.catalog.brands.store'), [
            'name' => 'New Balance',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('brands', ['slug' => 'new-balance']);
    }

    public function test_store_uses_provided_slug(): void
    {
        $this->actingAs($this->admin);

        $this->post(route('admin.catalog.brands.store'), [
            'name' => 'Nike',
            'slug' => 'custom-slug',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('brands', ['slug' => 'custom-slug']);
    }

    public function test_admin_can_view_edit_form(): void
    {
        $this->actingAs($this->admin);
        $brand = Brand::factory()->create();

        $response = $this->get(route('admin.catalog.brands.edit', $brand));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('admin/catalog/brands/edit'));
    }

    public function test_admin_can_update_brand(): void
    {
        $this->actingAs($this->admin);
        $brand = Brand::factory()->create(['name' => 'Old Name', 'slug' => 'old-name']);

        $response = $this->put(route('admin.catalog.brands.update', $brand), [
            'name' => 'New Name',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('admin.catalog.brands.index'));
        $this->assertDatabaseHas('brands', ['id' => $brand->id, 'name' => 'New Name']);
    }

    public function test_admin_can_toggle_brand_status(): void
    {
        $this->actingAs($this->admin);
        $brand = Brand::factory()->create(['is_active' => true]);

        $response = $this->patch(route('admin.catalog.brands.toggle-status', $brand));

        $response->assertRedirect();
        $this->assertDatabaseHas('brands', ['id' => $brand->id, 'is_active' => false]);
    }

    public function test_admin_can_view_brand_show(): void
    {
        $this->actingAs($this->admin);
        $brand = Brand::factory()->create();

        $response = $this->get(route('admin.catalog.brands.show', $brand));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('admin/catalog/brands/show'));
    }

    public function test_admin_can_soft_delete_brand(): void
    {
        $this->actingAs($this->admin);
        $brand = Brand::factory()->create();

        $response = $this->delete(route('admin.catalog.brands.destroy', $brand));

        $response->assertRedirect(route('admin.catalog.brands.index'));
        $this->assertSoftDeleted('brands', ['id' => $brand->id]);
    }

    public function test_delete_is_blocked_when_brand_has_products(): void
    {
        $this->actingAs($this->admin);
        $brand = Brand::factory()->create();
        // No ProductFactory exists yet — insert directly.
        \DB::table('products')->insert([
            'brand_id' => $brand->id,
            'name' => 'Blocked Product',
            'slug' => 'blocked-product',
            'sku' => 'BLK-001',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->delete(route('admin.catalog.brands.destroy', $brand));

        $response->assertRedirect();
        $this->assertDatabaseHas('brands', ['id' => $brand->id, 'deleted_at' => null]);
    }

    public function test_guest_is_redirected_from_destroy(): void
    {
        $brand = Brand::factory()->create();

        $response = $this->delete(route('admin.catalog.brands.destroy', $brand));

        $response->assertRedirect(route('login'));
    }

    public function test_soft_deleted_brand_excluded_from_index(): void
    {
        $this->actingAs($this->admin);
        $live = Brand::factory()->create();
        $deleted = Brand::factory()->create();
        $deleted->delete();

        $response = $this->get(route('admin.catalog.brands.index'));

        $response->assertInertia(fn ($page) => $page
            ->component('admin/catalog/brands/index')
            ->where('brands.data', fn ($data) => collect($data)->contains('id', $live->id))
            ->where('brands.data', fn ($data) => collect($data)->doesntContain('id', $deleted->id))
        );
    }

    public function test_index_filters_brands_by_name(): void
    {
        $this->actingAs($this->admin);
        $match = Brand::factory()->create(['name' => 'Nike Running']);
        $other = Brand::factory()->create(['name' => 'Adidas Sport']);

        $response = $this->get(route('admin.catalog.brands.index', ['search' => 'Nike']));

        $response->assertInertia(fn ($page) => $page
            ->where('brands.data', fn ($data) => collect($data)->contains('id', $match->id))
            ->where('brands.data', fn ($data) => collect($data)->doesntContain('id', $other->id))
        );
    }

    public function test_index_filters_brands_by_active_state(): void
    {
        $this->actingAs($this->admin);
        $active   = Brand::factory()->create(['is_active' => true]);
        $inactive = Brand::factory()->create(['is_active' => false]);

        $response = $this->get(route('admin.catalog.brands.index', ['is_active' => '1']));

        $response->assertInertia(fn ($page) => $page
            ->where('brands.data', fn ($data) => collect($data)->contains('id', $active->id))
            ->where('brands.data', fn ($data) => collect($data)->doesntContain('id', $inactive->id))
        );
    }

    // --- Permission denial ---

    public function test_user_without_permission_cannot_view_brands_index(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.catalog.brands.index'));

        $response->assertForbidden();
    }

    public function test_user_without_permission_cannot_access_create_form(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.catalog.brands.create'));

        $response->assertForbidden();
    }

    // --- Media upload ---

    public function test_store_attaches_uploaded_image(): void
    {
        Storage::fake('media');
        $this->actingAs($this->admin);

        $this->post(route('admin.catalog.brands.store'), [
            'name'  => 'Logo Brand',
            'image' => UploadedFile::fake()->image('logo.png'),
        ]);

        $brand = Brand::where('name', 'Logo Brand')->first();
        $this->assertCount(1, $brand->getMedia('images'));
    }

    public function test_update_removes_image_when_flag_set(): void
    {
        Storage::fake('media');
        $this->actingAs($this->admin);
        $brand = Brand::factory()->create();
        $brand->addMedia(UploadedFile::fake()->image('logo.png'))->toMediaCollection('images');

        $this->put(route('admin.catalog.brands.update', $brand), [
            'name'         => $brand->name,
            'remove_image' => true,
        ]);

        $this->assertCount(0, $brand->fresh()->getMedia('images'));
    }
}
