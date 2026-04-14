<?php

namespace Tests\Feature\Admin\Shipments;

use App\Models\ShippingMethod;
use App\Models\ShippingZone;
use App\Models\User;
use App\Models\WarehouseLocation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ShippingSetupTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::firstOrCreate(['name' => 'manage shipments', 'guard_name' => 'web']);

        $this->admin = User::factory()->create();
        $this->admin->givePermissionTo('manage shipments');
    }

    public function test_admin_can_view_warehouse_show_page(): void
    {
        $warehouse = WarehouseLocation::query()->create([
            'name' => 'Main Fulfillment Center',
            'code' => 'MAIN-FC',
            'country' => 'Ghana',
            'region' => 'Greater Accra',
            'city' => 'Accra',
            'address_line_1' => 'Spintex Road',
            'address_line_2' => null,
            'phone' => '+233200000001',
            'email' => 'warehouse@example.com',
            'is_active' => true,
            'is_default' => true,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.shipping.warehouse-locations.show', $warehouse));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('admin/shipping/warehouses/show')
            ->where('warehouse.id', $warehouse->id)
        );
    }

    public function test_admin_can_view_shipping_methods_index(): void
    {
        ShippingMethod::factory()->count(2)->create();

        $response = $this->actingAs($this->admin)->get(route('admin.shipping.methods.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('admin/shipping/methods/index'));
    }

    public function test_admin_can_attach_existing_method_to_zone(): void
    {
        $zone = ShippingZone::factory()->create();
        $method = ShippingMethod::factory()->create();

        $response = $this->actingAs($this->admin)->post(route('admin.shipping.zones.methods.attach', $zone), [
            'shipping_method_id' => $method->id,
        ]);

        $response->assertRedirect(route('admin.shipping.zones.show', $zone));
        $this->assertDatabaseHas('shipping_method_shipping_zone', [
            'shipping_zone_id' => $zone->id,
            'shipping_method_id' => $method->id,
        ]);
    }

    public function test_inactive_method_cannot_be_attached_to_zone(): void
    {
        $zone = ShippingZone::factory()->create();
        $method = ShippingMethod::factory()->create(['is_active' => false]);

        $response = $this->actingAs($this->admin)->from(route('admin.shipping.zones.show', $zone))
            ->post(route('admin.shipping.zones.methods.attach', $zone), [
                'shipping_method_id' => $method->id,
            ]);

        $response->assertRedirect(route('admin.shipping.zones.show', $zone));
        $response->assertSessionHasErrors('shipping_method_id');
        $this->assertDatabaseMissing('shipping_method_shipping_zone', [
            'shipping_zone_id' => $zone->id,
            'shipping_method_id' => $method->id,
        ]);
    }

    public function test_admin_can_detach_method_from_zone(): void
    {
        $zone = ShippingZone::factory()->create();
        $method = ShippingMethod::factory()->create();
        $zone->shippingMethods()->attach($method);

        $response = $this->actingAs($this->admin)->delete(route('admin.shipping.zones.methods.detach', [
            'zone' => $zone,
            'method' => $method,
        ]));

        $response->assertRedirect(route('admin.shipping.zones.show', $zone));
        $this->assertDatabaseMissing('shipping_method_shipping_zone', [
            'shipping_zone_id' => $zone->id,
            'shipping_method_id' => $method->id,
        ]);
    }
}
