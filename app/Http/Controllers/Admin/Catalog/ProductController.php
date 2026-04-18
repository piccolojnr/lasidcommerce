<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Domain\Catalog\Actions\CreateProductAction;
use App\Domain\Catalog\Actions\DeleteProductAction;
use App\Domain\Catalog\Actions\SyncProductMediaAction;
use App\Domain\Catalog\Actions\ToggleProductStatusAction;
use App\Domain\Catalog\Actions\UpdateProductAction;
use App\Domain\Catalog\Services\ProductBadgeService;
use App\Domain\Catalog\Exceptions\CannotDeleteProductException;
use App\Domain\Catalog\Queries\ListAdminProductsQuery;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
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

class ProductController extends Controller
{
    public function __construct(
        private ListAdminProductsQuery $listQuery,
        private CreateProductAction $createAction,
        private UpdateProductAction $updateAction,
        private ToggleProductStatusAction $toggleAction,
        private SyncProductMediaAction $syncMediaAction,
        private DeleteProductAction $deleteAction,
        private ProductBadgeService $badgeService,
    ) {
        $this->authorizeResource(Product::class, 'product');
    }

    public function index(Request $request): InertiaResponse
    {
        $filters = [
            'search' => $request->query('search') ?: null,
            'status' => $request->query('status') ?: null,
            'category_id' => $request->query('category_id') ?: null,
            'brand_id' => $request->query('brand_id') ?: null,
            'tag_id' => $request->query('tag_id') ?: null,
            'collection_id' => $request->query('collection_id') ?: null,
        ];

        $products = $this->listQuery->withFilters($filters)->paginate();
        $products->getCollection()->transform(fn(Product $product) => $this->formatProduct($product));

        return Inertia::render('admin/catalog/products/index', [
            'products' => $products,
            'filters' => $filters,
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'brands' => Brand::orderBy('name')->get(['id', 'name']),
            'tags' => Tag::orderBy('name')->get(['id', 'name']),
            'collections' => Collection::orderBy('sort_order')->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function create(): InertiaResponse
    {
        return Inertia::render('admin/catalog/products/create', [
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'brands' => Brand::orderBy('name')->get(['id', 'name']),
            'tags' => Tag::orderBy('name')->get(['id', 'name']),
            'collections' => Collection::orderBy('sort_order')->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['images', 'remove_image_ids']);

        $product = $this->createAction->execute($data);
        $this->syncMediaAction->execute($product, $request->file('images') ?? []);

        return redirect()->route('admin.catalog.products.index')
            ->with('success', 'Product created successfully.');
    }

    public function show(Product $product): InertiaResponse
    {
        $product->loadMedia('images');
        $product->loadCount('variants');
        $product->load(['category', 'brand', 'tags', 'collections']);

        return Inertia::render('admin/catalog/products/show', [
            'product' => $this->formatProduct($product, withImages: true),
        ]);
    }

    public function edit(Product $product): InertiaResponse
    {
        $product->loadMedia('images');
        $product->loadCount('variants');
        $product->load(['category', 'brand', 'tags', 'collections']);

        return Inertia::render('admin/catalog/products/edit', [
            'product' => $this->formatProduct($product, withImages: true),
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'brands' => Brand::orderBy('name')->get(['id', 'name']),
            'tags' => Tag::orderBy('name')->get(['id', 'name']),
            'collections' => Collection::orderBy('sort_order')->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $data = $request->safe()->except(['images', 'remove_image_ids']);

        $this->updateAction->execute($product, $data);
        $this->syncMediaAction->execute(
            $product,
            $request->file('images') ?? [],
            $request->input('remove_image_ids', []),
        );

        return redirect()->route('admin.catalog.products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function toggleStatus(Product $product): RedirectResponse
    {
        // toggleStatus is a custom route — authorizeResource does not cover it automatically.
        $this->authorize('update', $product);
        $this->toggleAction->execute($product);

        return back()->with('success', 'Product status updated.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        try {
            $this->deleteAction->execute($product);
        } catch (CannotDeleteProductException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.catalog.products.index')
            ->with('success', 'Product deleted.');
    }

    private function formatProduct(Product $product, bool $withImages = false): array
    {
        $includeImages = $withImages || $product->relationLoaded('media');

        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'sku' => $product->sku,
            'status' => $product->status,
            'product_type' => $product->product_type,
            'base_price' => $product->base_price,
            'compare_at_price' => $product->compare_at_price,
            'cost_price' => $product->cost_price,
            'is_featured' => $product->is_featured,
            'track_inventory' => $product->track_inventory,
            'allow_backorders' => $product->allow_backorders,
            'published_at' => $product->published_at?->toISOString(),
            'short_description' => $product->short_description,
            'description' => $product->description,
            'category_id' => $product->category_id,
            'category_name' => $product->category?->name,
            'brand_id' => $product->brand_id,
            'brand_name' => $product->brand?->name,
            'tags' => $product->relationLoaded('tags')
                ? $product->tags->map(fn(Tag $tag) => [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'slug' => $tag->slug,
                ])->values()->all()
                : [],
            'collections' => $product->relationLoaded('collections')
                ? $product->collections->map(fn(Collection $collection) => [
                    'id' => $collection->id,
                    'name' => $collection->name,
                    'slug' => $collection->slug,
                    'pivot_sort_order' => (int) ($collection->pivot?->sort_order ?? 0),
                ])->values()->all()
                : [],
            'badges' => $this->badgeService->resolve($product),
            'variants_count' => $product->variants_count ?? 0,
            'images' => $includeImages
                ? $product->getMedia(Product::IMAGE_COLLECTION)->map(
                    fn(Media $media, int $index) => $this->formatProductImage($media, $index)
                )->values()->toArray()
                : [],
            'created_at' => $product->created_at?->toISOString(),
        ];
    }

    private function formatProductImage(Media $media, int $index): array
    {
        $originalUrl = $media->getUrl();

        return [
            'id' => $media->id,
            'url' => $originalUrl,
            'thumb_url' => $this->safeConversionUrl($media, Product::IMAGE_CONVERSION_THUMB, $originalUrl),
            'card_url' => $this->safeConversionUrl($media, Product::IMAGE_CONVERSION_CARD, $originalUrl),
            'gallery_url' => $this->safeConversionUrl($media, Product::IMAGE_CONVERSION_GALLERY, $originalUrl),
            'is_primary' => $index === 0,
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
