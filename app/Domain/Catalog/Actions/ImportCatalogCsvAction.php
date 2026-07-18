<?php

namespace App\Domain\Catalog\Actions;

use App\Domain\Catalog\DTOs\CatalogImportResult;
use App\Domain\Catalog\Services\BrandSlugGenerator;
use App\Domain\Catalog\Services\CatalogCsvSchema;
use App\Domain\Catalog\Services\CategorySlugGenerator;
use App\Domain\Catalog\Services\CollectionSlugGenerator;
use App\Domain\Catalog\Services\TagSlugGenerator;
use App\Domain\Inventory\Actions\CreateStockAdjustmentAction;
use App\Domain\Inventory\DTOs\StockAdjustmentData;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Product;
use App\Models\StockItem;
use App\Models\StockMovement;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ImportCatalogCsvAction
{
    public function __construct(
        private CreateProductAction $createProductAction,
        private UpdateProductAction $updateProductAction,
        private CategorySlugGenerator $categorySlugGenerator,
        private BrandSlugGenerator $brandSlugGenerator,
        private TagSlugGenerator $tagSlugGenerator,
        private CollectionSlugGenerator $collectionSlugGenerator,
        private CreateStockAdjustmentAction $stockAdjustmentAction,
    ) {}

    public function execute(UploadedFile $file, ?User $actor = null): CatalogImportResult
    {
        $result = new CatalogImportResult;
        $handle = fopen($file->getRealPath(), 'r');

        if ($handle === false) {
            $result->addError(1, 'The uploaded CSV could not be opened.');

            return $result;
        }

        $headers = $this->readHeaders($handle);
        $missingHeaders = array_diff(CatalogCsvSchema::HEADERS, $headers);

        if ($missingHeaders !== []) {
            fclose($handle);
            $result->addError(1, 'Missing required columns: '.implode(', ', $missingHeaders).'.');

            return $result;
        }

        $rowNumber = 1;

        while (($values = fgetcsv($handle)) !== false) {
            $rowNumber++;
            $row = $this->combineRow($headers, $values);

            if ($this->isBlankRow($row)) {
                $result->skipped++;

                continue;
            }

            $result->processed++;

            try {
                $created = $this->importRow($row, $actor);

                if ($created) {
                    $result->created++;
                } else {
                    $result->updated++;
                }
            } catch (\Throwable $exception) {
                $result->addError($rowNumber, $this->errorMessage($exception));
            }
        }

        fclose($handle);

        return $result;
    }

    /**
     * @return list<string>
     */
    private function readHeaders($handle): array
    {
        $headers = fgetcsv($handle);

        if ($headers === false) {
            return [];
        }

        return collect($headers)
            ->map(fn ($header) => Str::of((string) $header)->replace("\xEF\xBB\xBF", '')->trim()->lower()->toString())
            ->all();
    }

    /**
     * @param  list<string>  $headers
     * @param  list<string|null>  $values
     * @return array<string, string|null>
     */
    private function combineRow(array $headers, array $values): array
    {
        $row = [];

        foreach ($headers as $index => $header) {
            $value = $values[$index] ?? null;
            $row[$header] = is_string($value) ? trim($value) : $value;
        }

        return $row;
    }

    /**
     * @param  array<string, string|null>  $row
     */
    private function isBlankRow(array $row): bool
    {
        return collect($row)->every(fn ($value) => $value === null || $value === '');
    }

    /**
     * @param  array<string, string|null>  $row
     */
    private function importRow(array $row, ?User $actor): bool
    {
        $this->requireValue($row, 'sku');
        $this->requireValue($row, 'name');
        $this->requireValue($row, 'base_price_cents');

        return DB::transaction(function () use ($row, $actor): bool {
            $product = Product::query()->where('sku', $row['sku'])->first();
            $created = $product === null;

            $attributes = [
                'category_id' => $this->resolveCategory($row['category'] ?? null)?->id,
                'brand_id' => $this->resolveBrand($row['brand'] ?? null)?->id,
                'name' => (string) $row['name'],
                'slug' => $this->nullable($row['slug'] ?? null),
                'sku' => (string) $row['sku'],
                'status' => $this->nullable($row['status'] ?? null) ?? 'draft',
                'product_type' => $this->nullable($row['product_type'] ?? null) ?? 'physical',
                'base_price' => $this->integer($row['base_price_cents'] ?? null, 'base_price_cents', nullable: false),
                'compare_at_price' => $this->integer($row['compare_at_price_cents'] ?? null, 'compare_at_price_cents'),
                'cost_price' => $this->integer($row['cost_price_cents'] ?? null, 'cost_price_cents'),
                'track_inventory' => $this->boolean($row['track_inventory'] ?? null, default: true),
                'allow_backorders' => $this->boolean($row['allow_backorders'] ?? null, default: false),
                'is_featured' => $this->boolean($row['is_featured'] ?? null, default: false),
                'published_at' => $this->date($row['published_at'] ?? null),
                'short_description' => $this->nullable($row['short_description'] ?? null),
                'description' => $this->nullable($row['description'] ?? null),
                'tag_ids' => $this->resolveTags($row['tags'] ?? null),
                'collection_ids' => $this->resolveCollections($row['collections'] ?? null),
            ];

            if ($created) {
                $product = $this->createProductAction->execute($attributes);
            } else {
                $product = $this->updateProductAction->execute($product, $attributes);
            }

            $this->syncStock($product, $row, $actor);

            return $created;
        });
    }

    /**
     * @param  array<string, string|null>  $row
     */
    private function requireValue(array $row, string $field): void
    {
        if ($this->nullable($row[$field] ?? null) === null) {
            throw ValidationException::withMessages([$field => "The {$field} column is required."]);
        }
    }

    private function resolveCategory(?string $value): ?Category
    {
        $value = $this->nullable($value);

        if ($value === null) {
            return null;
        }

        return $this->resolveLookup(
            Category::class,
            $value,
            fn (string $name) => Category::query()->create([
                'name' => $name,
                'slug' => $this->categorySlugGenerator->generate($name),
                'is_active' => true,
            ]),
        );
    }

    private function resolveBrand(?string $value): ?Brand
    {
        $value = $this->nullable($value);

        if ($value === null) {
            return null;
        }

        return $this->resolveLookup(
            Brand::class,
            $value,
            fn (string $name) => Brand::query()->create([
                'name' => $name,
                'slug' => $this->brandSlugGenerator->generate($name),
                'is_active' => true,
            ]),
        );
    }

    /**
     * @return list<int>
     */
    private function resolveTags(?string $value): array
    {
        return collect($this->tokens($value))
            ->map(fn (string $token) => $this->resolveLookup(
                Tag::class,
                $token,
                fn (string $name) => Tag::query()->create([
                    'name' => $name,
                    'slug' => $this->tagSlugGenerator->generate($name),
                    'is_active' => true,
                ]),
            )->id)
            ->values()
            ->all();
    }

    /**
     * @return list<int>
     */
    private function resolveCollections(?string $value): array
    {
        return collect($this->tokens($value))
            ->map(fn (string $token) => $this->resolveLookup(
                Collection::class,
                $token,
                fn (string $name) => Collection::query()->create([
                    'name' => $name,
                    'slug' => $this->collectionSlugGenerator->generate($name),
                    'is_active' => true,
                ]),
            )->id)
            ->values()
            ->all();
    }

    /**
     * @template T of \Illuminate\Database\Eloquent\Model
     *
     * @param  class-string<T>  $modelClass
     * @param  callable(string):T  $create
     * @return T
     */
    private function resolveLookup(string $modelClass, string $value, callable $create)
    {
        $slug = Str::slug($value);
        $query = method_exists($modelClass, 'bootSoftDeletes') ? $modelClass::withTrashed() : $modelClass::query();
        $model = $query
            ->where(fn ($query) => $query
                ->when(ctype_digit($value), fn ($query) => $query->orWhereKey((int) $value))
                ->orWhere('slug', $value)
                ->orWhere('slug', $slug)
                ->orWhere('name', $value))
            ->first();

        if ($model !== null) {
            if (method_exists($model, 'trashed') && $model->trashed()) {
                $model->restore();
            }

            return $model;
        }

        return $create($value);
    }

    /**
     * @return list<string>
     */
    private function tokens(?string $value): array
    {
        $value = $this->nullable($value);

        if ($value === null) {
            return [];
        }

        return Str::of($value)
            ->explode('|')
            ->map(fn ($token) => trim((string) $token))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  array<string, string|null>  $row
     */
    private function syncStock(Product $product, array $row, ?User $actor): void
    {
        $quantity = $this->integer($row['quantity_on_hand'] ?? null, 'quantity_on_hand');
        $reorderLevel = $this->integer($row['reorder_level'] ?? null, 'reorder_level');

        if ($quantity === null && $reorderLevel === null && ! $product->track_inventory) {
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
            referenceType: 'catalog_import',
            referenceId: $product->id,
            note: 'Bulk catalog import set quantity on hand to '.$quantity.'.',
            createdBy: $actor?->id,
        ));
    }

    private function nullable(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function integer(?string $value, string $field, bool $nullable = true): ?int
    {
        $value = $this->nullable($value);

        if ($value === null) {
            return $nullable ? null : 0;
        }

        if (! preg_match('/^-?\d+$/', $value)) {
            throw ValidationException::withMessages([$field => "The {$field} column must be an integer."]);
        }

        $integer = (int) $value;

        if ($integer < 0) {
            throw ValidationException::withMessages([$field => "The {$field} column must be at least zero."]);
        }

        return $integer;
    }

    private function boolean(?string $value, bool $default): bool
    {
        $value = $this->nullable($value);

        if ($value === null) {
            return $default;
        }

        return match (Str::lower($value)) {
            '1', 'true', 'yes', 'y' => true,
            '0', 'false', 'no', 'n' => false,
            default => throw ValidationException::withMessages(['boolean' => "The value '{$value}' is not a valid boolean."]),
        };
    }

    private function date(?string $value): ?Carbon
    {
        $value = $this->nullable($value);

        if ($value === null) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            throw ValidationException::withMessages(['published_at' => 'The published_at column must be a valid date.']);
        }
    }

    private function errorMessage(\Throwable $exception): string
    {
        if ($exception instanceof ValidationException) {
            return collect($exception->errors())->flatten()->first() ?? $exception->getMessage();
        }

        return $exception->getMessage();
    }
}
