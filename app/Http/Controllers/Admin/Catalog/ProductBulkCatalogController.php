<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Domain\Catalog\Actions\ImportCatalogCsvAction;
use App\Domain\Catalog\Actions\UpdateBulkCatalogProductsAction;
use App\Domain\Catalog\Queries\ListBulkEditableProductsQuery;
use App\Domain\Catalog\Services\CatalogCsvExporter;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ImportCatalogCsvRequest;
use App\Http\Requests\Admin\UpdateBulkCatalogProductsRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Product;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductBulkCatalogController extends Controller
{
    public function __construct(
        private CatalogCsvExporter $exporter,
        private ImportCatalogCsvAction $importCatalogCsvAction,
        private ListBulkEditableProductsQuery $bulkEditableProductsQuery,
        private UpdateBulkCatalogProductsAction $updateBulkCatalogProductsAction,
    ) {}

    public function edit(Request $request): InertiaResponse
    {
        $this->authorize('viewAny', Product::class);

        $filters = $this->filters($request);
        $products = $this->bulkEditableProductsQuery
            ->get($filters)
            ->map(fn (Product $product) => $this->formatBulkEditableProduct($product))
            ->values()
            ->all();

        return Inertia::render('admin/catalog/products/bulk-edit', [
            'products' => $products,
            'filters' => $filters,
            'bulkEditResult' => $request->session()->get('catalogBulkEdit'),
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'brands' => Brand::orderBy('name')->get(['id', 'name']),
            'tags' => Tag::orderBy('name')->get(['id', 'name']),
            'collections' => Collection::orderBy('sort_order')->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function export(): StreamedResponse
    {
        $this->authorize('viewAny', Product::class);

        return $this->exporter->stream();
    }

    public function template(): StreamedResponse
    {
        $this->authorize('viewAny', Product::class);

        return $this->exporter->template();
    }

    public function import(ImportCatalogCsvRequest $request): RedirectResponse
    {
        $this->authorize('create', Product::class);

        $result = $this->importCatalogCsvAction->execute($request->file('catalog_csv'), $request->user());

        return redirect()
            ->route('admin.catalog.products.index')
            ->with('catalogImport', $result->toArray())
            ->with(
                $result->failed > 0 ? 'warning' : 'success',
                sprintf(
                    'Catalog import processed %d rows: %d created, %d updated, %d failed.',
                    $result->processed,
                    $result->created,
                    $result->updated,
                    $result->failed,
                ),
            );
    }

    public function update(UpdateBulkCatalogProductsRequest $request): RedirectResponse
    {
        $this->authorize('viewAny', Product::class);

        $result = $this->updateBulkCatalogProductsAction->execute(
            $request->validated('products'),
            $request->user(),
        );

        return back()
            ->with('catalogBulkEdit', $result->toArray())
            ->with(
                $result->failed > 0 ? 'warning' : 'success',
                sprintf(
                    'Bulk editor saved %d of %d rows.',
                    $result->updated,
                    $result->processed,
                ),
            );
    }

    private function filters(Request $request): array
    {
        return [
            'search' => $request->query('search') ?: null,
            'status' => $request->query('status') ?: null,
            'category_id' => $request->query('category_id') ?: null,
            'brand_id' => $request->query('brand_id') ?: null,
            'tag_id' => $request->query('tag_id') ?: null,
            'collection_id' => $request->query('collection_id') ?: null,
        ];
    }

    private function formatBulkEditableProduct(Product $product): array
    {
        $stockItems = $product->relationLoaded('stockItems') ? $product->stockItems : $product->stockItems()->get();
        $primaryStockItem = $stockItems->count() === 1 ? $stockItems->first() : null;
        $primaryImage = $product->getFirstMedia(Product::IMAGE_COLLECTION);

        return [
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'status' => $product->status,
            'product_type' => $product->product_type,
            'category_id' => $product->category_id,
            'category_name' => $product->category?->name,
            'brand_id' => $product->brand_id,
            'brand_name' => $product->brand?->name,
            'base_price' => $product->base_price,
            'compare_at_price' => $product->compare_at_price,
            'cost_price' => $product->cost_price,
            'track_inventory' => $product->track_inventory,
            'allow_backorders' => $product->allow_backorders,
            'is_featured' => $product->is_featured,
            'quantity_on_hand' => $primaryStockItem?->quantity_on_hand,
            'reorder_level' => $primaryStockItem?->reorder_level,
            'reserved_quantity' => (int) $stockItems->sum('quantity_reserved'),
            'stock_item_count' => $stockItems->count(),
            'variants_count' => $product->variants_count ?? 0,
            'image_count' => $product->relationLoaded('media')
                ? $product->media->where('collection_name', Product::IMAGE_COLLECTION)->count()
                : $product->getMedia(Product::IMAGE_COLLECTION)->count(),
            'primary_image_thumb_url' => $primaryImage instanceof Media
                ? $this->safeConversionUrl($primaryImage, Product::IMAGE_CONVERSION_THUMB, $primaryImage->getUrl())
                : null,
        ];
    }

    private function safeConversionUrl(Media $media, string $conversionName, string $fallbackUrl): string
    {
        if (empty($media->conversions_disk)) {
            return $fallbackUrl;
        }

        try {
            return $media->getAvailableUrl([$conversionName]);
        } catch (\Throwable) {
            return $fallbackUrl;
        }
    }
}
