<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Domain\Catalog\Actions\CreateTagAction;
use App\Domain\Catalog\Actions\DeleteTagAction;
use App\Domain\Catalog\Actions\ToggleTagStatusAction;
use App\Domain\Catalog\Actions\UpdateTagAction;
use App\Domain\Catalog\Queries\ListAdminTagsQuery;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTagRequest;
use App\Http\Requests\Admin\UpdateTagRequest;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class TagController extends Controller
{
    public function __construct(
        private ListAdminTagsQuery $listQuery,
        private CreateTagAction $createAction,
        private UpdateTagAction $updateAction,
        private ToggleTagStatusAction $toggleAction,
        private DeleteTagAction $deleteAction,
    ) {
        $this->authorizeResource(Tag::class, 'tag');
    }

    public function index(Request $request): InertiaResponse
    {
        $filters = [
            'search' => $request->query('search') ?: null,
            'is_active' => $request->query('is_active') !== null
                ? filter_var($request->query('is_active'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
                : null,
        ];

        return Inertia::render('admin/catalog/tags/index', [
            'tags' => $this->listQuery->withFilters($filters)->paginate(),
            'filters' => $filters,
        ]);
    }

    public function create(): InertiaResponse
    {
        return Inertia::render('admin/catalog/tags/create');
    }

    public function store(StoreTagRequest $request): RedirectResponse
    {
        $this->createAction->execute($request->validated());

        return redirect()->route('admin.catalog.tags.index')
            ->with('success', 'Tag created successfully.');
    }

    public function show(Tag $tag): InertiaResponse
    {
        $tag->loadCount('products');

        return Inertia::render('admin/catalog/tags/show', [
            'tag' => $this->formatTag($tag),
        ]);
    }

    public function edit(Tag $tag): InertiaResponse
    {
        $tag->loadCount('products');

        return Inertia::render('admin/catalog/tags/edit', [
            'tag' => $this->formatTag($tag),
        ]);
    }

    public function update(UpdateTagRequest $request, Tag $tag): RedirectResponse
    {
        $this->updateAction->execute($tag, $request->validated());

        return redirect()->route('admin.catalog.tags.index')
            ->with('success', 'Tag updated successfully.');
    }

    public function toggleStatus(Tag $tag): RedirectResponse
    {
        $this->authorize('update', $tag);
        $this->toggleAction->execute($tag);

        return back()->with('success', 'Tag status updated.');
    }

    public function destroy(Tag $tag): RedirectResponse
    {
        $this->deleteAction->execute($tag);

        return redirect()->route('admin.catalog.tags.index')
            ->with('success', 'Tag deleted.');
    }

    private function formatTag(Tag $tag): array
    {
        return [
            'id' => $tag->id,
            'name' => $tag->name,
            'slug' => $tag->slug,
            'description' => $tag->description,
            'is_active' => $tag->is_active,
            'products_count' => $tag->products_count ?? 0,
            'created_at' => $tag->created_at?->toISOString(),
        ];
    }
}
