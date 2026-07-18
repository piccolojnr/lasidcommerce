<?php

namespace Tests\Feature\Admin\Catalog;

use App\Domain\Catalog\Services\CatalogCsvSchema;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Product;
use App\Models\StockItem;
use App\Models\StockMovement;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ProductBulkCatalogTest extends TestCase
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

    public function test_guest_cannot_export_catalog(): void
    {
        $this->get(route('admin.catalog.products.bulk.export'))
            ->assertRedirect(route('login'));
    }

    public function test_admin_can_export_catalog_csv(): void
    {
        $category = Category::factory()->create(['slug' => 'footwear']);
        $brand = Brand::factory()->create(['slug' => 'acme']);
        $tag = Tag::factory()->create(['slug' => 'summer']);
        $collection = Collection::factory()->create(['slug' => 'homepage']);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'sku' => 'SKU-EXPORT-1',
            'name' => 'Export Sneaker',
            'base_price' => 25000,
            'track_inventory' => true,
        ]);
        $product->tags()->attach($tag);
        $product->collections()->attach($collection, ['sort_order' => 10]);
        StockItem::query()->create([
            'product_id' => $product->id,
            'quantity_on_hand' => 12,
            'reorder_level' => 3,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.catalog.products.bulk.export'));

        $response->assertOk();
        $csv = $response->streamedContent();

        $this->assertStringContainsString(implode(',', CatalogCsvSchema::HEADERS), $csv);
        $this->assertStringContainsString('SKU-EXPORT-1', $csv);
        $this->assertStringContainsString('footwear', $csv);
        $this->assertStringContainsString('summer', $csv);
        $this->assertStringContainsString('homepage', $csv);
    }

    public function test_admin_can_download_import_template(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.catalog.products.bulk.template'));

        $response->assertOk();
        $csv = $response->streamedContent();

        $this->assertStringContainsString(implode(',', CatalogCsvSchema::HEADERS), $csv);
        $this->assertStringContainsString('SKU-001', $csv);
    }

    public function test_admin_can_import_new_catalog_rows(): void
    {
        $csv = $this->csv([
            [
                'SKU-IMPORT-1',
                'Imported Hoodie',
                '',
                'active',
                'physical',
                'Apparel',
                'Backthred',
                '19900',
                '',
                '9000',
                '1',
                '0',
                '1',
                '2026-01-01 10:00:00',
                'Short copy',
                'Long copy',
                'new|featured',
                'homepage|winter',
                '25',
                '4',
            ],
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.catalog.products.bulk.import'), [
                'catalog_csv' => UploadedFile::fake()->createWithContent('catalog.csv', $csv),
            ]);

        $response->assertRedirect(route('admin.catalog.products.index'));
        $response->assertSessionHas('catalogImport.created', 1);

        $product = Product::query()->where('sku', 'SKU-IMPORT-1')->firstOrFail();

        $this->assertSame('Imported Hoodie', $product->name);
        $this->assertSame('imported-hoodie', $product->slug);
        $this->assertSame('active', $product->status);
        $this->assertSame(19900, $product->base_price);
        $this->assertTrue($product->is_featured);
        $this->assertSame('Apparel', $product->category?->name);
        $this->assertSame('Backthred', $product->brand?->name);
        $this->assertSame(['featured', 'new'], $product->tags()->orderBy('slug')->pluck('slug')->all());
        $this->assertSame(['homepage', 'winter'], $product->collections()->orderBy('collections.slug')->pluck('collections.slug')->all());
        $this->assertDatabaseHas('stock_items', [
            'product_id' => $product->id,
            'quantity_on_hand' => 25,
            'reorder_level' => 4,
        ]);
        $this->assertDatabaseHas('stock_movements', [
            'type' => StockMovement::TYPE_CORRECTION_ADD,
            'quantity' => 25,
            'reference_type' => 'catalog_import',
            'reference_id' => $product->id,
            'created_by' => $this->admin->id,
        ]);
    }

    public function test_admin_can_update_existing_catalog_rows(): void
    {
        $product = Product::factory()->create([
            'sku' => 'SKU-UPDATE-1',
            'name' => 'Old Name',
            'base_price' => 10000,
        ]);
        StockItem::query()->create([
            'product_id' => $product->id,
            'quantity_on_hand' => 10,
            'reorder_level' => 2,
        ]);

        $csv = $this->csv([
            [
                'SKU-UPDATE-1',
                'Updated Name',
                '',
                'draft',
                'physical',
                '',
                '',
                '15000',
                '',
                '',
                '1',
                '0',
                '0',
                '',
                '',
                '',
                '',
                '',
                '7',
                '5',
            ],
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.catalog.products.bulk.import'), [
                'catalog_csv' => UploadedFile::fake()->createWithContent('catalog.csv', $csv),
            ])
            ->assertRedirect(route('admin.catalog.products.index'))
            ->assertSessionHas('catalogImport.updated', 1);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Name',
            'base_price' => 15000,
        ]);
        $this->assertDatabaseHas('stock_items', [
            'product_id' => $product->id,
            'quantity_on_hand' => 7,
            'reorder_level' => 5,
        ]);
        $this->assertDatabaseHas('stock_movements', [
            'type' => StockMovement::TYPE_CORRECTION_REMOVE,
            'quantity' => 3,
            'reference_type' => 'catalog_import',
            'reference_id' => $product->id,
        ]);
    }

    public function test_import_reports_row_errors_without_rolling_back_valid_rows(): void
    {
        $csv = $this->csv([
            [
                'SKU-VALID',
                'Valid Product',
                '',
                'draft',
                'physical',
                '',
                '',
                '1000',
                '',
                '',
                '1',
                '0',
                '0',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
            ],
            [
                'SKU-INVALID',
                'Invalid Product',
                '',
                'draft',
                'physical',
                '',
                '',
                'not-money',
                '',
                '',
                '1',
                '0',
                '0',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
            ],
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.catalog.products.bulk.import'), [
                'catalog_csv' => UploadedFile::fake()->createWithContent('catalog.csv', $csv),
            ])
            ->assertRedirect(route('admin.catalog.products.index'))
            ->assertSessionHas('catalogImport.created', 1)
            ->assertSessionHas('catalogImport.failed', 1);

        $this->assertDatabaseHas('products', ['sku' => 'SKU-VALID']);
        $this->assertDatabaseMissing('products', ['sku' => 'SKU-INVALID']);
    }

    /**
     * @param  list<list<string>>  $rows
     */
    private function csv(array $rows): string
    {
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, CatalogCsvSchema::HEADERS);

        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }

        rewind($handle);

        return stream_get_contents($handle) ?: '';
    }
}
