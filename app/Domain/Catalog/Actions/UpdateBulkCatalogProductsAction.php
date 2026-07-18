<?php

namespace App\Domain\Catalog\Actions;

use App\Domain\Catalog\DTOs\BulkCatalogUpdateResult;
use App\Domain\Inventory\Actions\CreateStockAdjustmentAction;
use App\Domain\Inventory\DTOs\StockAdjustmentData;
use App\Models\Product;
use App\Models\StockItem;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateBulkCatalogProductsAction
{
    public function __construct(
        private UpdateProductAction $updateProductAction,
        private CreateStockAdjustmentAction $stockAdjustmentAction,
    ) {}

    /**
     * @param  list<array<string, mixed>>  $rows
     */
    public function execute(array $rows, ?User $actor = null): BulkCatalogUpdateResult
    {
        $result = new BulkCatalogUpdateResult;
        $seenSkus = [];

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 1;
            $result->processed++;

            try {
                $sku = trim((string) ($row['sku'] ?? ''));

                if ($sku === '') {
                    throw ValidationException::withMessages(['sku' => 'The SKU field is required.']);
                }

                if (isset($seenSkus[$sku])) {
                    throw ValidationException::withMessages(['sku' => "The SKU '{$sku}' appears more than once in this edit batch."]);
                }

                $seenSkus[$sku] = true;
                $this->updateRow($row, $actor);
                $result->updated++;
            } catch (\Throwable $exception) {
                $result->addError($rowNumber, $this->errorMessage($exception));
            }
        }

        return $result;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function updateRow(array $row, ?User $actor): void
    {
        DB::transaction(function () use ($row, $actor) {
            $product = Product::query()
                ->with('stockItems')
                ->findOrFail((int) $row['id']);

            $sku = trim((string) $row['sku']);
            $skuExists = Product::query()
                ->where('sku', $sku)
                ->whereKeyNot($product->getKey())
                ->exists();

            if ($skuExists) {
                throw ValidationException::withMessages(['sku' => "The SKU '{$sku}' is already used by another product."]);
            }

            $this->updateProductAction->execute($product, [
                'category_id' => $this->nullableInteger($row['category_id'] ?? null),
                'brand_id' => $this->nullableInteger($row['brand_id'] ?? null),
                'name' => trim((string) $row['name']),
                'sku' => $sku,
                'status' => (string) $row['status'],
                'product_type' => (string) $row['product_type'],
                'base_price' => $this->integer($row['base_price'] ?? null, 'base_price'),
                'compare_at_price' => $this->nullableInteger($row['compare_at_price'] ?? null),
                'cost_price' => $this->nullableInteger($row['cost_price'] ?? null),
                'track_inventory' => (bool) ($row['track_inventory'] ?? false),
                'allow_backorders' => (bool) ($row['allow_backorders'] ?? false),
                'is_featured' => (bool) ($row['is_featured'] ?? false),
            ]);

            $this->syncStock($product, $row, $actor);
        });
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function syncStock(Product $product, array $row, ?User $actor): void
    {
        if ($product->variants()->exists()) {
            return;
        }

        $quantity = $this->nullableInteger($row['quantity_on_hand'] ?? null);
        $reorderLevel = $this->nullableInteger($row['reorder_level'] ?? null);

        if ($quantity === null && $reorderLevel === null && ! (bool) ($row['track_inventory'] ?? false)) {
            return;
        }

        $stockItem = StockItem::query()->firstOrCreate([
            'product_id' => $product->id,
            'product_variant_id' => null,
        ]);

        if ($reorderLevel !== null && $stockItem->reorder_level !== $reorderLevel) {
            $stockItem->update(['reorder_level' => $reorderLevel]);
        }

        if ($quantity === null || $stockItem->quantity_on_hand === $quantity) {
            return;
        }

        $delta = $quantity - $stockItem->quantity_on_hand;
        $this->stockAdjustmentAction->execute($stockItem, new StockAdjustmentData(
            type: $delta > 0 ? StockMovement::TYPE_CORRECTION_ADD : StockMovement::TYPE_CORRECTION_REMOVE,
            quantity: abs($delta),
            referenceType: 'catalog_bulk_edit',
            referenceId: $product->id,
            note: 'Spreadsheet bulk edit set quantity on hand to '.$quantity.'.',
            createdBy: $actor?->id,
        ));
    }

    private function integer(mixed $value, string $field): int
    {
        $integer = $this->nullableInteger($value);

        if ($integer === null) {
            throw ValidationException::withMessages([$field => "The {$field} field is required."]);
        }

        return $integer;
    }

    private function nullableInteger(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $integer = (int) $value;

        if ((string) $integer !== (string) $value || $integer < 0) {
            throw ValidationException::withMessages(['number' => 'Numeric fields must be whole numbers greater than or equal to zero.']);
        }

        return $integer;
    }

    private function errorMessage(\Throwable $exception): string
    {
        if ($exception instanceof ValidationException) {
            return collect($exception->errors())->flatten()->first() ?? $exception->getMessage();
        }

        return $exception->getMessage();
    }
}
