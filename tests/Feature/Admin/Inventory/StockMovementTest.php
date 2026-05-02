<?php

namespace Tests\Feature\Admin\Inventory;

use App\Models\Product;
use App\Models\StockItem;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class StockMovementTest extends TestCase
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

    public function test_admin_can_view_stock_movement_index(): void
    {
        $product = Product::factory()->create(['name' => 'Classic Sneaker', 'sku' => 'SNK-001']);
        $stockItem = StockItem::query()->create([
            'product_id' => $product->id,
            'quantity_on_hand' => 10,
        ]);
        $movement = StockMovement::query()->create([
            'stock_item_id' => $stockItem->id,
            'type' => StockMovement::TYPE_RESTOCK,
            'quantity' => 10,
            'note' => 'Opening balance',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.inventory.stock-movements.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('admin/inventory/stock-movements/index')
            ->where('stockMovements.data.0.id', $movement->id)
            ->where('stockMovements.data.0.product_name', 'Classic Sneaker')
            ->where('stockMovements.data.0.stock_delta', 10)
        );
    }

    public function test_admin_can_view_stock_movement_detail(): void
    {
        $product = Product::factory()->create(['name' => 'Classic Sneaker', 'sku' => 'SNK-001']);
        $stockItem = StockItem::query()->create([
            'product_id' => $product->id,
            'quantity_on_hand' => 10,
        ]);
        $movement = StockMovement::query()->create([
            'stock_item_id' => $stockItem->id,
            'type' => StockMovement::TYPE_DAMAGE,
            'quantity' => 2,
            'note' => 'Damaged pair',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.inventory.stock-movements.show', $movement));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('admin/inventory/stock-movements/show')
            ->where('stockMovement.id', $movement->id)
            ->where('stockMovement.stock_delta', -2)
            ->where('stockMovement.note', 'Damaged pair')
        );
    }
}
