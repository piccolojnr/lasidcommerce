<?php

namespace Tests\Unit\Domain\Inventory;

use App\Domain\Inventory\Actions\CreateStockAdjustmentAction;
use App\Domain\Inventory\DTOs\StockAdjustmentData;
use App\Models\Product;
use App\Models\StockItem;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CreateStockAdjustmentActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_persists_stock_and_movement_atomically(): void
    {
        $product = Product::factory()->create();
        $user = User::factory()->create();
        $stockItem = StockItem::query()->create([
            'product_id' => $product->id,
            'quantity_on_hand' => 5,
            'quantity_reserved' => 0,
        ]);

        $movement = app(CreateStockAdjustmentAction::class)->execute(
            $stockItem,
            StockAdjustmentData::fromArray([
                'type' => StockMovement::TYPE_RESTOCK,
                'quantity' => 4,
                'reference_type' => Product::class,
                'reference_id' => $product->id,
                'note' => 'Cycle count correction',
            ], $user->id),
        );

        $this->assertSame(9, $stockItem->fresh()->quantity_on_hand);
        $this->assertSame($stockItem->id, $movement->stock_item_id);
        $this->assertSame($user->id, $movement->created_by);
        $this->assertDatabaseHas('stock_movements', [
            'id' => $movement->id,
            'type' => StockMovement::TYPE_RESTOCK,
            'quantity' => 4,
        ]);
    }

    public function test_it_rejects_adjustments_that_would_make_on_hand_negative(): void
    {
        $product = Product::factory()->create();
        $stockItem = StockItem::query()->create([
            'product_id' => $product->id,
            'quantity_on_hand' => 1,
            'quantity_reserved' => 0,
        ]);

        $this->expectException(ValidationException::class);

        try {
            app(CreateStockAdjustmentAction::class)->execute(
                $stockItem,
                StockAdjustmentData::fromArray([
                    'type' => StockMovement::TYPE_DAMAGE,
                    'quantity' => 2,
                ]),
            );
        } finally {
            $this->assertSame(1, $stockItem->fresh()->quantity_on_hand);
            $this->assertDatabaseCount('stock_movements', 0);
        }
    }
}
