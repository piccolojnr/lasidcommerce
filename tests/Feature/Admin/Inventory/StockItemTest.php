<?php

namespace Tests\Feature\Admin\Inventory;

use App\Models\Product;
use App\Models\StockItem;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class StockItemTest extends TestCase
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

    public function test_admin_can_view_stock_item_index(): void
    {
        $product = Product::factory()->create(['name' => 'Classic Sneaker', 'sku' => 'SNK-001']);
        $stockItem = StockItem::query()->create([
            'product_id' => $product->id,
            'quantity_on_hand' => 8,
            'quantity_reserved' => 2,
            'reorder_level' => 3,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.inventory.stock-items.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('admin/inventory/stock-items/index')
            ->where('stockItems.data.0.id', $stockItem->id)
            ->where('stockItems.data.0.product_name', 'Classic Sneaker')
            ->where('stockItems.data.0.available_quantity', 6)
            ->where('stockItems.data.0.status', 'in_stock')
        );
    }

    public function test_admin_can_view_stock_item_detail(): void
    {
        $product = Product::factory()->create(['name' => 'Classic Sneaker', 'sku' => 'SNK-001']);
        $stockItem = StockItem::query()->create([
            'product_id' => $product->id,
            'quantity_on_hand' => 5,
            'quantity_reserved' => 1,
            'reorder_level' => 4,
        ]);
        $movement = StockMovement::query()->create([
            'stock_item_id' => $stockItem->id,
            'type' => StockMovement::TYPE_RESTOCK,
            'quantity' => 5,
            'note' => 'Opening balance',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.inventory.stock-items.show', $stockItem));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('admin/inventory/stock-items/show')
            ->where('stockItem.id', $stockItem->id)
            ->where('stockItem.status', 'low_stock')
            ->where('stockItem.movements.0.id', $movement->id)
            ->where('stockItem.movements.0.stock_delta', 5)
        );
    }

    public function test_admin_can_update_reorder_level(): void
    {
        $product = Product::factory()->create();
        $stockItem = StockItem::query()->create([
            'product_id' => $product->id,
            'quantity_on_hand' => 5,
            'quantity_reserved' => 1,
            'reorder_level' => 2,
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.inventory.stock-items.update', $stockItem), [
            'reorder_level' => 7,
        ]);

        $response->assertRedirect(route('admin.inventory.stock-items.show', $stockItem));
        $this->assertDatabaseHas('stock_items', ['id' => $stockItem->id, 'reorder_level' => 7]);
    }

    public function test_admin_can_record_positive_stock_adjustment(): void
    {
        $product = Product::factory()->create();
        $stockItem = StockItem::query()->create([
            'product_id' => $product->id,
            'quantity_on_hand' => 5,
            'quantity_reserved' => 1,
            'reorder_level' => 2,
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.inventory.stock-items.adjustments.store', $stockItem), [
            'type' => StockMovement::TYPE_RESTOCK,
            'quantity' => 4,
            'reference_type' => Product::class,
            'reference_id' => $product->id,
            'note' => 'Warehouse recount',
        ]);

        $response->assertRedirect(route('admin.inventory.stock-items.show', $stockItem));
        $this->assertDatabaseHas('stock_items', ['id' => $stockItem->id, 'quantity_on_hand' => 9]);
        $this->assertDatabaseHas('stock_movements', [
            'stock_item_id' => $stockItem->id,
            'type' => StockMovement::TYPE_RESTOCK,
            'quantity' => 4,
            'created_by' => $this->admin->id,
            'note' => 'Warehouse recount',
        ]);
    }

    public function test_admin_can_record_negative_stock_adjustment(): void
    {
        $product = Product::factory()->create();
        $stockItem = StockItem::query()->create([
            'product_id' => $product->id,
            'quantity_on_hand' => 8,
            'quantity_reserved' => 1,
            'reorder_level' => 2,
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.inventory.stock-items.adjustments.store', $stockItem), [
            'type' => StockMovement::TYPE_DAMAGE,
            'quantity' => 3,
            'note' => 'Damaged in transit',
        ]);

        $response->assertRedirect(route('admin.inventory.stock-items.show', $stockItem));
        $this->assertDatabaseHas('stock_items', ['id' => $stockItem->id, 'quantity_on_hand' => 5]);
        $this->assertDatabaseHas('stock_movements', [
            'stock_item_id' => $stockItem->id,
            'type' => StockMovement::TYPE_DAMAGE,
            'quantity' => 3,
        ]);
    }

    public function test_negative_adjustment_cannot_reduce_on_hand_below_zero(): void
    {
        $product = Product::factory()->create();
        $stockItem = StockItem::query()->create([
            'product_id' => $product->id,
            'quantity_on_hand' => 2,
            'quantity_reserved' => 0,
        ]);

        $response = $this->from(route('admin.inventory.stock-items.show', $stockItem))
            ->actingAs($this->admin)
            ->post(route('admin.inventory.stock-items.adjustments.store', $stockItem), [
                'type' => StockMovement::TYPE_DAMAGE,
                'quantity' => 3,
            ]);

        $response->assertRedirect(route('admin.inventory.stock-items.show', $stockItem));
        $response->assertSessionHasErrors('quantity');
        $this->assertDatabaseHas('stock_items', ['id' => $stockItem->id, 'quantity_on_hand' => 2]);
        $this->assertDatabaseCount('stock_movements', 0);
    }

    public function test_user_without_permission_cannot_access_inventory_routes(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $stockItem = StockItem::query()->create([
            'product_id' => $product->id,
            'quantity_on_hand' => 2,
        ]);

        $this->actingAs($user)
            ->get(route('admin.inventory.stock-items.index'))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('admin.inventory.stock-items.show', $stockItem))
            ->assertForbidden();
    }
}
