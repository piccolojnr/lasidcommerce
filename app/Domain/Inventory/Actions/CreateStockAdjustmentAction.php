<?php

namespace App\Domain\Inventory\Actions;

use App\Domain\Inventory\DTOs\StockAdjustmentData;
use App\Models\StockItem;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateStockAdjustmentAction
{
    public function __construct(
        private AdjustStockAction $adjustStockAction,
        private RecordStockMovementAction $recordStockMovementAction,
    ) {}

    public function execute(StockItem $stockItem, StockAdjustmentData $data): StockMovement
    {
        $delta = $data->stockDelta();
        $nextQuantityOnHand = $stockItem->quantity_on_hand + $delta;

        if ($nextQuantityOnHand < 0) {
            throw ValidationException::withMessages([
                'quantity' => 'The requested adjustment would reduce quantity on hand below zero.',
            ]);
        }

        return DB::transaction(function () use ($stockItem, $data, $delta) {
            $freshStockItem = StockItem::query()->lockForUpdate()->findOrFail($stockItem->getKey());
            $nextQuantityOnHand = $freshStockItem->quantity_on_hand + $delta;

            if ($nextQuantityOnHand < 0) {
                throw ValidationException::withMessages([
                    'quantity' => 'The requested adjustment would reduce quantity on hand below zero.',
                ]);
            }

            $this->adjustStockAction->execute($freshStockItem, $delta);

            return $this->recordStockMovementAction->execute(
                $freshStockItem,
                $data->toMovementAttributes(),
            );
        });
    }
}
