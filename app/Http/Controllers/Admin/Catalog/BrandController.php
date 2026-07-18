<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Domain\Catalog\Actions\CreateBrandAction;
use App\Domain\Catalog\Actions\DeleteBrandAction;
use App\Domain\Catalog\Actions\SyncBrandMediaAction;
use App\Domain\Catalog\Actions\ToggleBrandStatusAction;
use App\Domain\Catalog\Actions\UpdateBrandAction;
use App\Domain\Catalog\Exceptions\CannotDeleteBrandException;
use App\Domain\Catalog\Queries\ListAdminBrandsQuery;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBrandRequest;
use App\Http\Requests\Admin\UpdateBrandRequest;
use App\Models\Brand;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class BrandController extends Controller
{
    public function __construct(
        private ListAdminBrandsQuery $listQuery,
        private CreateBrandAction $createAction,
        private UpdateBrandAction $updateAction,
        private ToggleBrandStatusAction $toggleAction,
        private SyncBrandMediaAction $syncMediaAction,
        private DeleteBrandAction $deleteAction,
    ) {
        $this->authorizeResource(Brand::class, 'brand');
    }

    public function index(Request $request): InertiaResponse
    {
        $filters = [
            'search' => $request->query('search') ?: null,
            'is_active' => $request->query('is_active') !== null
                ? filter_var($request->query('is_active'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
                : null,
        ];

        return Inertia::render('admin/catalog/brands/index', [
            'brands' => $this->listQuery->withFilters($filters)->paginate(),
            'filters' => $filters,
        ]);
    }

    public function create(): InertiaResponse
    {
        return Inertia::render('admin/catalog/brands/create');
    }

    public function store(StoreBrandRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['image', 'remove_image']);

        $brand = $this->createAction->execute($data);
        $this->syncMediaAction->execute($brand, $request->file('image'));

        return redirect()->route('admin.catalog.brands.index')
            ->with('success', 'Brand created successfully.');
    }

    public function show(Brand $brand): InertiaResponse
    {
        $brand->loadMedia('images');
        $brand->loadCount('products');

        return Inertia::render('admin/catalog/brands/show', [
            'brand' => [
                'id' => $brand->id,
                'name' => $brand->name,
                'slug' => $brand->slug,
                'description' => $brand->description,
                'is_active' => $brand->is_active,
                'image_url' => $brand->getFirstMediaUrl('images') ?: null,
                'products_count' => $brand->products_count,
                'created_at' => $brand->created_at?->toISOString(),
            ],
        ]);
    }

    public function edit(Brand $brand): InertiaResponse
    {
        $brand->loadMedia('images');
        $brand->loadCount('products');

        return Inertia::render('admin/catalog/brands/edit', [
            'brand' => [
                'id' => $brand->id,
                'name' => $brand->name,
                'slug' => $brand->slug,
                'description' => $brand->description,
                'is_active' => $brand->is_active,
                'image_url' => $brand->getFirstMediaUrl('images') ?: null,
                'products_count' => $brand->products_count,
                'created_at' => $brand->created_at?->toISOString(),
            ],
        ]);
    }

    public function update(UpdateBrandRequest $request, Brand $brand): RedirectResponse
    {
        $data = $request->safe()->except(['image', 'remove_image']);

        $this->updateAction->execute($brand, $data);
        $this->syncMediaAction->execute(
            $brand,
            $request->file('image'),
            $request->boolean('remove_image'),
        );

        return redirect()->route('admin.catalog.brands.index')
            ->with('success', 'Brand updated successfully.');
    }

    public function toggleStatus(Brand $brand): RedirectResponse
    {
        // toggleStatus is a custom route — authorizeResource does not cover it automatically.
        $this->authorize('update', $brand);
        $this->toggleAction->execute($brand);

        return back()->with('success', 'Brand status updated.');
    }

    public function destroy(Brand $brand): RedirectResponse
    {
        try {
            $this->deleteAction->execute($brand);
        } catch (CannotDeleteBrandException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.catalog.brands.index')
            ->with('success', 'Brand deleted.');
    }
}
