<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Domain\Catalog\Actions\CreateCollectionAction;
use App\Domain\Catalog\Actions\DeleteCollectionAction;
use App\Domain\Catalog\Actions\ToggleCollectionStatusAction;
use App\Domain\Catalog\Actions\UpdateCollectionAction;
use App\Domain\Catalog\Queries\ListAdminCollectionsQuery;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCollectionRequest;
use App\Http\Requests\Admin\UpdateCollectionRequest;
use App\Models\Collection;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class CollectionController extends Controller
{
    public function __construct(
        private ListAdminCollectionsQuery $listQuery,
        private CreateCollectionAction $createAction,
        private UpdateCollectionAction $updateAction,
        private ToggleCollectionStatusAction $toggleAction,
        private DeleteCollectionAction $deleteAction,
    ) {
        $this->authorizeResource(Collection::class, 'collection');
    }

    public function index(Request $request): InertiaResponse
    {
        $filters = [
            'search' => $request->query('search') ?: null,
            'is_active' => $request->query('is_active') !== null
                ? filter_var($request->query('is_active'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
                : null,
        ];

        return Inertia::render('admin/catalog/collections/index', [
            'collections' => $this->listQuery->withFilters($filters)->paginate(),
            'filters' => $filters,
        ]);
    }

    public function create(): InertiaResponse
    {
        return Inertia::render('admin/catalog/collections/create', [
            'products' => $this->productOptions(),
        ]);
    }

    public function store(StoreCollectionRequest $request): RedirectResponse
    {
        $this->createAction->execute($request->validated());

        return redirect()->route('admin.catalog.collections.index')
            ->with('success', 'Collection created successfully.');
    }

    public function show(Collection $collection): InertiaResponse
    {
        $collection->load(['products.media', 'products.category', 'products.brand']);
        $collection->loadCount('products');

        return Inertia::render('admin/catalog/collections/show', [
            'collection' => $this->formatCollection($collection, withProducts: true),
        ]);
    }

    public function edit(Collection $collection): InertiaResponse
    {
        $collection->load(['products']);
        $collection->loadCount('products');

        return Inertia::render('admin/catalog/collections/edit', [
            'collection' => $this->formatCollection($collection, withProducts: true),
            'products' => $this->productOptions(),
        ]);
    }

    public function update(UpdateCollectionRequest $request, Collection $collection): RedirectResponse
    {
        $this->updateAction->execute($collection, $request->validated());

        return redirect()->route('admin.catalog.collections.index')
            ->with('success', 'Collection updated successfully.');
    }

    public function toggleStatus(Collection $collection): RedirectResponse
    {
        $this->authorize('update', $collection);
        $this->toggleAction->execute($collection);

        return back()->with('success', 'Collection status updated.');
    }

    public function destroy(Collection $collection): RedirectResponse
    {
        $this->deleteAction->execute($collection);

        return redirect()->route('admin.catalog.collections.index')
            ->with('success', 'Collection deleted.');
    }

    private function productOptions(): array
    {
        return Product::query()
            ->orderBy('name')
            ->get(['id', 'name', 'sku', 'status'])
            ->map(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'status' => $product->status,
            ])
            ->all();
    }

    private function formatCollection(Collection $collection, bool $withProducts = false): array
    {
        return [
            'id' => $collection->id,
            'name' => $collection->name,
            'slug' => $collection->slug,
            'description' => $collection->description,
            'is_active' => $collection->is_active,
            'sort_order' => $collection->sort_order,
            'products_count' => $collection->products_count ?? 0,
            'products' => $withProducts
                ? $collection->products->map(fn (Product $product) => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'status' => $product->status,
                    'sort_order' => (int) ($product->pivot?->sort_order ?? 0),
                    'primary_image_url' => $product->getFirstMediaUrl('images') ?: null,
                    'category_name' => $product->category?->name,
                    'brand_name' => $product->brand?->name,
                ])->values()->all()
                : [],
            'created_at' => $collection->created_at?->toISOString(),
        ];
    }
}
