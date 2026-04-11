<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Domain\Catalog\Actions\CreateCategoryAction;
use App\Domain\Catalog\Actions\DeleteCategoryAction;
use App\Domain\Catalog\Actions\SyncCategoryMediaAction;
use App\Domain\Catalog\Actions\ToggleCategoryStatusAction;
use App\Domain\Catalog\Actions\UpdateCategoryAction;
use App\Domain\Catalog\Exceptions\CannotDeleteCategoryException;
use App\Domain\Catalog\Queries\ListAdminCategoriesQuery;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class CategoryController extends Controller
{
    public function __construct(
        private ListAdminCategoriesQuery $listQuery,
        private CreateCategoryAction $createAction,
        private UpdateCategoryAction $updateAction,
        private ToggleCategoryStatusAction $toggleAction,
        private SyncCategoryMediaAction $syncMediaAction,
        private DeleteCategoryAction $deleteAction,
    ) {
        $this->authorizeResource(Category::class, 'category');
    }

    public function index(): InertiaResponse
    {
        return Inertia::render('admin/catalog/categories/index', [
            'categories' => $this->listQuery->get(),
        ]);
    }

    public function create(): InertiaResponse
    {
        return Inertia::render('admin/catalog/categories/create', [
            'categories' => Category::orderBy('name')->get(['id', 'name', 'parent_id'])->toArray(),
        ]);
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['image', 'remove_image']);

        $category = $this->createAction->execute($data);
        $this->syncMediaAction->execute($category, $request->file('image'));

        return redirect()->route('admin.catalog.categories.index')
            ->with('success', 'Category created successfully.');
    }

    public function show(Category $category): InertiaResponse
    {
        $category->load('children', 'parent');
        $category->loadMedia('images');

        return Inertia::render('admin/catalog/categories/show', [
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'is_active' => $category->is_active,
                'sort_order' => $category->sort_order,
                'parent_id' => $category->parent_id,
                'parent_name' => $category->parent?->name,
                'depth' => 0,
                'image_url' => $category->getFirstMediaUrl('images') ?: null,
                'children_count' => $category->children->count(),
                'created_at' => $category->created_at?->toISOString(),
                'children' => $category->children->map(fn ($child) => [
                    'id' => $child->id,
                    'name' => $child->name,
                    'slug' => $child->slug,
                    'is_active' => $child->is_active,
                ])->toArray(),
            ],
        ]);
    }

    public function edit(Category $category): InertiaResponse
    {
        $category->load('parent');
        $category->loadMedia('images');

        return Inertia::render('admin/catalog/categories/edit', [
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'is_active' => $category->is_active,
                'sort_order' => $category->sort_order,
                'parent_id' => $category->parent_id,
                'parent_name' => $category->parent?->name,
                'depth' => 0,
                'image_url' => $category->getFirstMediaUrl('images') ?: null,
                'children_count' => $category->children()->count(),
                'created_at' => $category->created_at?->toISOString(),
            ],
            'categories' => Category::orderBy('name')->get(['id', 'name', 'parent_id'])->toArray(),
        ]);
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $data = $request->safe()->except(['image', 'remove_image']);

        $this->updateAction->execute($category, $data);
        $this->syncMediaAction->execute(
            $category,
            $request->file('image'),
            $request->boolean('remove_image'),
        );

        return redirect()->route('admin.catalog.categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function toggleStatus(Category $category): RedirectResponse
    {
        // toggleStatus is a custom route — authorizeResource does not cover it automatically.
        $this->authorize('update', $category);
        $this->toggleAction->execute($category);

        return back()->with('success', 'Category status updated.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        try {
            $this->deleteAction->execute($category);
        } catch (CannotDeleteCategoryException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.catalog.categories.index')
            ->with('success', 'Category deleted.');
    }
}
