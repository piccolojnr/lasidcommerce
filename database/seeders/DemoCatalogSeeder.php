<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Product;
use App\Models\StockItem;
use App\Models\StockMovement;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoCatalogSeeder extends Seeder
{
    /**
     * Path to the demo products CSV, relative to the seeders directory.
     */
    private const CSV_FILE = __DIR__.'/demo-products.csv';

    public function run(): void
    {
        $actor = User::query()->where('email', 'catalog.manager@example.com')->first()
            ?? User::query()->where('email', 'admin@example.com')->first();

        $rows = $this->readCsv(self::CSV_FILE);

        // ----------------------------------------------------------------
        // Pre-load categories and brands that CatalogSetupSeeder already
        // created, keyed by slug for O(1) lookup.
        // ----------------------------------------------------------------
        $categories = Category::query()->pluck('id', 'slug');
        $brands = Brand::query()->pluck('id', 'slug');

        // ----------------------------------------------------------------
        // Build (or upsert) every tag and collection referenced in the CSV
        // before touching products so we can sync them cleanly.
        // ----------------------------------------------------------------
        $tags = $this->upsertTags($rows);
        $collections = $this->upsertCollections($rows);

        // ----------------------------------------------------------------
        // Seed products row by row.
        // ----------------------------------------------------------------
        foreach ($rows as $row) {
            $categoryId = $categories[$row['category_slug']] ?? null;
            $brandId = $brands[$row['brand_slug']] ?? null;

            if ($categoryId === null) {
                $this->command->warn("Category not found: {$row['category_slug']} — skipping {$row['sku']}");

                continue;
            }

            if ($brandId === null) {
                $this->command->warn("Brand not found: {$row['brand_slug']} — skipping {$row['sku']}");

                continue;
            }

            $slug = $this->resolveSlug($row['slug'], $row['sku']);

            $attributes = [
                'category_id' => $categoryId,
                'brand_id' => $brandId,
                'name' => $row['name'],
                'slug' => $slug,
                'short_description' => $row['short_description'],
                'description' => $row['description'],
                'status' => $row['status'],
                'product_type' => $row['product_type'],
                'base_price' => $row['base_price_cents'],
                'compare_at_price' => $row['compare_at_price_cents'] ?: null,
                'cost_price' => $row['cost_price_cents'] ?: null,
                'track_inventory' => $row['track_inventory'],
                'allow_backorders' => $row['allow_backorders'],
                'is_featured' => $row['is_featured'],
                'published_at' => $row['published_at'],
            ];

            // Use withTrashed() so a previously soft-deleted product with the
            // same SKU doesn't cause a unique-constraint violation on re-seed.
            $product = Product::withTrashed()->where('sku', $row['sku'])->first();

            if ($product === null) {
                $product = Product::create(array_merge(['sku' => $row['sku']], $attributes));
            } else {
                if ($product->trashed()) {
                    $product->restore();
                }
                $product->update($attributes);
            }

            $this->syncStock($product, $row, $actor);
            $this->syncTags($product, $row['tags'], $tags);
            $this->syncCollections($product, $row['collections'], $collections);
            $this->syncImages($product, $row);
        }
    }

    // -------------------------------------------------------------------------
    // CSV parsing
    // -------------------------------------------------------------------------

    /**
     * Parse the CSV file and return a normalised array of row data.
     *
     * @return array<int, array<string, mixed>>
     */
    private function readCsv(string $path): array
    {
        $handle = fopen($path, 'r');

        if ($handle === false) {
            throw new \RuntimeException("Cannot open CSV file: {$path}");
        }

        // Read header row and normalise column names to lowercase.
        // The empty string escape disables the non-standard escape character
        // that PHP's fgetcsv adds by default (also silences the PHP 9 deprecation).
        $rawHeaders = fgetcsv($handle, separator: ',', escape: '');
        $headers = array_map(
            fn (string $h) => strtolower(trim($h)),
            $rawHeaders,
        );

        $rows = [];

        while (($raw = fgetcsv($handle, separator: ',', escape: '')) !== false) {
            if (count($raw) !== count($headers)) {
                continue; // skip malformed lines
            }

            $row = array_combine($headers, $raw);
            $rows[] = $this->normaliseRow($row);
        }

        fclose($handle);

        return $rows;
    }

    /**
     * Cast and normalise a raw CSV row into typed values.
     *
     * @param  array<string, string>  $row
     * @return array<string, mixed>
     */
    private function normaliseRow(array $row): array
    {
        return [
            'sku' => trim($row['sku']),
            'name' => $this->truncateName(trim($row['name'])),
            'slug' => $this->truncateSlug(trim($row['slug']), trim($row['sku'])),
            'status' => trim($row['status']),
            'product_type' => trim($row['product_type']),
            'category_slug' => trim($row['category']),
            'brand_slug' => trim($row['brand']),
            'base_price_cents' => (int) $row['base_price_cents'],
            'compare_at_price_cents' => $row['compare_at_price_cents'] !== '' ? (int) $row['compare_at_price_cents'] : null,
            'cost_price_cents' => $row['cost_price_cents'] !== '' ? (int) $row['cost_price_cents'] : null,
            'track_inventory' => (bool) (int) $row['track_inventory'],
            'allow_backorders' => (bool) (int) $row['allow_backorders'],
            'is_featured' => (bool) (int) $row['is_featured'],
            'published_at' => $row['published_at'] !== '' ? $row['published_at'] : null,
            'short_description' => trim($row['short_description']),
            'description' => trim($row['description']),
            'tags' => $this->splitPipe($row['tags']),
            'collections' => $this->splitPipe($row['collections']),
            'quantity_on_hand' => (int) $row['quantity_on_hand'],
            'reorder_level' => (int) $row['reorder_level'],
            'main_image_url' => trim($row['main_image_url']),
            'image_urls' => $this->splitPipe($row['image_urls']),
            'image_alt_text' => trim($row['image_alt_text']),
        ];
    }

    /**
     * Split a pipe-separated CSV cell into a trimmed string array,
     * discarding any blank segments.
     *
     * @return list<string>
     */
    private function splitPipe(string $value): array
    {
        if (trim($value) === '') {
            return [];
        }

        return array_values(
            array_filter(
                array_map('trim', explode('|', $value)),
                fn (string $v) => $v !== '',
            ),
        );
    }

    // -------------------------------------------------------------------------
    // Tags
    // -------------------------------------------------------------------------

    /**
     * Collect every unique tag value from all rows, upsert them, and return a
     * map of raw-value → Tag::id.
     *
     * Tag names come straight from the CSV (e.g. "Women", "Dress", "Midi").
     * Slugs are generated with Str::slug so they are URL-safe and consistent.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<string, int>
     */
    private function upsertTags(array $rows): array
    {
        $unique = [];

        foreach ($rows as $row) {
            foreach ($row['tags'] as $value) {
                $slug = Str::slug($value);
                $unique[$slug] = $value; // last-write wins for name, slug is stable
            }
        }

        $map = [];

        foreach ($unique as $slug => $name) {
            // withTrashed() so we don't trip over the unique constraint on
            // soft-deleted rows that still own the slug.
            $tag = Tag::withTrashed()->where('slug', $slug)->first();

            if ($tag === null) {
                $tag = Tag::create([
                    'slug' => $slug,
                    'name' => $name,
                    'is_active' => true,
                ]);
            } else {
                if ($tag->trashed()) {
                    $tag->restore();
                }
                $tag->update(['name' => $name, 'is_active' => true]);
            }

            $map[$name] = $tag->getKey(); // keyed by original value for easy lookup
        }

        return $map;
    }

    /**
     * Collect every unique collection value from all rows, upsert them, and
     * return a map of raw-value → Collection::id.
     *
     * Collection names come straight from the CSV (e.g. "Womens-Edit").
     * Sort order is assigned in discovery order × 10.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<string, int>
     */
    private function upsertCollections(array $rows): array
    {
        $unique = [];
        $order = 1;

        foreach ($rows as $row) {
            foreach ($row['collections'] as $value) {
                $slug = Str::slug($value);

                if (! isset($unique[$slug])) {
                    $unique[$slug] = ['name' => $value, 'sort_order' => $order * 10];
                    $order++;
                }
            }
        }

        $map = [];

        foreach ($unique as $slug => $definition) {
            // withTrashed() so we don't trip over the unique constraint on
            // soft-deleted rows that still own the slug.
            $collection = Collection::withTrashed()->where('slug', $slug)->first();

            if ($collection === null) {
                $collection = Collection::create([
                    'slug' => $slug,
                    'name' => $definition['name'],
                    'is_active' => true,
                    'sort_order' => $definition['sort_order'],
                ]);
            } else {
                if ($collection->trashed()) {
                    $collection->restore();
                }
                $collection->update([
                    'name' => $definition['name'],
                    'is_active' => true,
                    'sort_order' => $definition['sort_order'],
                ]);
            }

            $map[$definition['name']] = $collection->getKey();
        }

        return $map;
    }

    // -------------------------------------------------------------------------
    // Slug / name truncation and collision resolution
    // -------------------------------------------------------------------------

    /**
     * Truncate a product name to fit within varchar(512).
     * Cuts at the last space before the limit so we never split mid-word.
     */
    private function truncateName(string $name): string
    {
        $max = 512;

        if (mb_strlen($name) <= $max) {
            return $name;
        }

        $truncated = mb_substr($name, 0, $max);
        $lastSpace = mb_strrpos($truncated, ' ');

        return $lastSpace !== false
            ? mb_substr($truncated, 0, $lastSpace)
            : $truncated;
    }

    /**
     * Truncate a slug so it fits within varchar(512) while leaving room for a
     * SKU suffix (e.g. "-sku-591a70", up to 13 chars).
     *
     * Truncation always happens on a segment boundary so the result remains a
     * clean, readable slug.
     */
    private function truncateSlug(string $slug, string $sku): string
    {
        // Reserve room for the worst-case SKU suffix: "-sku-" + up to 8 hex chars = 13 chars.
        $maxBase = 512 - 13;

        if (mb_strlen($slug) <= $maxBase) {
            return $slug;
        }

        $truncated = mb_substr($slug, 0, $maxBase);
        $lastDash = mb_strrpos($truncated, '-');

        return $lastDash !== false
            ? mb_substr($truncated, 0, $lastDash)
            : $truncated;
    }

    /**
     * Return a slug that is safe to write for this SKU.
     *
     * If the desired slug already belongs to a *different* product, append a
     * short suffix derived from the SKU so the insert does not violate the
     * unique constraint. On a re-run the SKU match finds the same product and
     * updates it in place, so the suffix is stable across runs.
     */
    private function resolveSlug(string $desiredSlug, string $sku): string
    {
        $conflict = Product::query()
            ->where('slug', $desiredSlug)
            ->whereNot('sku', $sku)
            ->exists();

        if (! $conflict) {
            return $desiredSlug;
        }

        $suffix = strtolower(trim($sku, 'SKU-'));
        $resolved = $desiredSlug.'-'.Str::slug($suffix);

        $this->command->warn(
            "Slug collision: '{$desiredSlug}' already taken. Using '{$resolved}' for SKU {$sku}."
        );

        return $resolved;
    }

    // -------------------------------------------------------------------------
    // Stock
    // -------------------------------------------------------------------------

    /**
     * @param  array<string, mixed>  $row
     */
    private function syncStock(Product $product, array $row, ?User $actor): void
    {
        $stockItem = StockItem::query()->updateOrCreate(
            ['product_id' => $product->getKey(), 'product_variant_id' => null],
            [
                'quantity_on_hand' => $row['quantity_on_hand'],
                'quantity_reserved' => 0,
                'reorder_level' => $row['reorder_level'],
            ],
        );

        StockMovement::query()->updateOrCreate(
            [
                'stock_item_id' => $stockItem->getKey(),
                'type' => StockMovement::TYPE_RESTOCK,
                'reference_type' => Product::class,
                'reference_id' => $product->getKey(),
            ],
            [
                'quantity' => $row['quantity_on_hand'],
                'note' => 'Demo opening stock balance.',
                'created_by' => $actor?->getKey(),
            ],
        );
    }

    // -------------------------------------------------------------------------
    // Tag + collection syncing
    // -------------------------------------------------------------------------

    /**
     * @param  list<string>  $tagValues  Raw values from CSV (e.g. ["Women","Dress"])
     * @param  array<string, int>  $tagMap  name → id
     */
    private function syncTags(Product $product, array $tagValues, array $tagMap): void
    {
        $ids = array_values(
            array_filter(
                array_map(fn (string $v) => $tagMap[$v] ?? null, $tagValues),
            ),
        );

        $product->tags()->sync($ids);
    }

    /**
     * @param  list<string>  $collectionValues  Raw values from CSV
     * @param  array<string, int>  $collectionMap  name → id
     */
    private function syncCollections(Product $product, array $collectionValues, array $collectionMap): void
    {
        $pivotData = [];

        foreach (array_values($collectionValues) as $index => $value) {
            $id = $collectionMap[$value] ?? null;

            if ($id === null) {
                continue;
            }

            $pivotData[$id] = ['sort_order' => ($index + 1) * 10];
        }

        $product->collections()->sync($pivotData);
    }

    // -------------------------------------------------------------------------
    // Media / images
    // -------------------------------------------------------------------------

    /**
     * Clear and re-attach all product images from the CSV IMAGE_URLS column.
     *
     * Each pipe-separated URL is added as a separate media item.
     * The first URL is treated as the main image (position 1).
     * Spatie will queue the thumb / card / gallery conversions automatically.
     *
     * @param  array<string, mixed>  $row
     */
    private function syncImages(Product $product, array $row): void
    {
        $urls = $row['image_urls'];

        // Fall back to main_image_url when IMAGE_URLS is empty.
        if (empty($urls) && $row['main_image_url'] !== '') {
            $urls = [$row['main_image_url']];
        }

        if (empty($urls)) {
            return;
        }

        $product->clearMediaCollection(Product::IMAGE_COLLECTION);

        foreach ($urls as $position => $url) {
            try {
                $product
                    ->addMediaFromUrl($url)
                    ->usingFileName(
                        sprintf('%s-%d.jpg', Str::slug($row['sku']), $position + 1),
                    )
                    ->usingName(
                        $position === 0
                        ? $row['image_alt_text']
                        : sprintf('%s (%d)', $row['name'], $position + 1),
                    )
                    ->withCustomProperties([
                        'seeded' => true,
                        'position' => $position + 1,
                    ])
                    ->toMediaCollection(Product::IMAGE_COLLECTION);
            } catch (\Exception $e) {
                $this->command->warn(
                    "Image import failed for {$row['sku']} (position ".($position + 1)."): {$e->getMessage()}"
                );
            }
        }
    }
}
