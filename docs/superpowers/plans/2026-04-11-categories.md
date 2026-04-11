# Admin Categories Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement the Admin Categories CRUD end-to-end as a vertical slice — backend domain layer, controller, requests, routes, and all frontend Inertia pages.

**Architecture:** Thin controller delegates to domain actions/queries. `ListAdminCategoriesQuery` flattens the tree with depth metadata for the frontend to render. Frontend uses the Inertia `Form` component with auto-generated wayfinder helpers for routing.

**Tech Stack:** Laravel 12, Spatie MediaLibrary, Inertia.js v3, React 19, TypeScript, shadcn/ui (Select, Checkbox, Input, Label, Card), Lucide icons.

---

## File Map

### New files
| File | Purpose |
|---|---|
| `app/Domain/Catalog/Services/CategorySlugGenerator.php` | Unique slug generation with suffix collision handling |
| `app/Domain/Catalog/Actions/UpdateCategoryAction.php` | Update an existing category |
| `app/Domain/Catalog/Actions/ToggleCategoryStatusAction.php` | Flip is_active on a category |
| `app/Domain/Catalog/Actions/SyncCategoryMediaAction.php` | Attach / replace / clear category image |
| `app/Domain/Catalog/Queries/ListAdminCategoriesQuery.php` | Load full tree, flatten with depth field |
| `tests/Feature/Admin/Catalog/CategoryTest.php` | Feature tests for category routes |
| `tests/Unit/Domain/Catalog/CategorySlugGeneratorTest.php` | Unit tests for slug generator |
| `resources/js/pages/admin/catalog/categories/_components/category-table.tsx` | Tree table with depth indentation |

### Modified files
| File | What changes |
|---|---|
| `app/Domain/Catalog/Actions/CreateCategoryAction.php` | Fix: currently returns `new Category()` without saving |
| `app/Models/Category.php` | Add `registerMediaCollections()` for the `images` collection |
| `app/Http/Requests/Admin/StoreCategoryRequest.php` | Make slug nullable; add `image` and `remove_image` fields |
| `app/Http/Requests/Admin/UpdateCategoryRequest.php` | Add `image`, `remove_image`; prevent self-parent |
| `app/Http/Controllers/Admin/Catalog/CategoryController.php` | Wire all domain classes; implement all methods; add `toggleStatus` |
| `routes/web.php` | Change `categories` resource to `except(['destroy'])`; add toggle-status route |
| `resources/js/types/admin/catalog.ts` | Expand `AdminCategory` with `description`, `parent_id`, `depth`, `image_url`, `children_count`, `created_at` |
| `resources/js/pages/admin/catalog/categories/index.tsx` | Wire to Inertia props; use `CategoryTable` |
| `resources/js/pages/admin/catalog/categories/create.tsx` | Wire to Inertia props; pass to `CategoryForm` |
| `resources/js/pages/admin/catalog/categories/edit.tsx` | Wire to Inertia props; pass category to `CategoryForm` |
| `resources/js/pages/admin/catalog/categories/show.tsx` | Wire to Inertia props; full detail view |
| `resources/js/pages/admin/catalog/categories/_components/category-form.tsx` | Full form: auto-slug, parent select, image upload, is_active |

---

## Task 1: Fix CreateCategoryAction

The existing `CreateCategoryAction::execute()` returns `new Category($attributes)` without persisting it.

**Files:**
- Modify: `app/Domain/Catalog/Actions/CreateCategoryAction.php`

- [ ] **Step 1: Update the action to persist**

Replace the entire file content:

```php
<?php

namespace App\Domain\Catalog\Actions;

use App\Models\Category;

class CreateCategoryAction
{
    public function execute(array $attributes): Category
    {
        return Category::create($attributes);
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add app/Domain/Catalog/Actions/CreateCategoryAction.php
git commit -m "fix: CreateCategoryAction now persists the category"
```

---

## Task 2: Add UpdateCategoryAction and ToggleCategoryStatusAction

**Files:**
- Create: `app/Domain/Catalog/Actions/UpdateCategoryAction.php`
- Create: `app/Domain/Catalog/Actions/ToggleCategoryStatusAction.php`

- [ ] **Step 1: Create UpdateCategoryAction**

```php
<?php

namespace App\Domain\Catalog\Actions;

use App\Models\Category;

class UpdateCategoryAction
{
    public function execute(Category $category, array $attributes): Category
    {
        $category->update($attributes);

        return $category->fresh();
    }
}
```

- [ ] **Step 2: Create ToggleCategoryStatusAction**

```php
<?php

namespace App\Domain\Catalog\Actions;

use App\Models\Category;

class ToggleCategoryStatusAction
{
    public function execute(Category $category): Category
    {
        $category->update(['is_active' => ! $category->is_active]);

        return $category->fresh();
    }
}
```

- [ ] **Step 3: Commit**

```bash
git add app/Domain/Catalog/Actions/UpdateCategoryAction.php app/Domain/Catalog/Actions/ToggleCategoryStatusAction.php
git commit -m "feat: add UpdateCategoryAction and ToggleCategoryStatusAction"
```

---

## Task 3: CategorySlugGenerator

Generates a URL-safe slug from a name, ensuring uniqueness with numeric suffix collision resolution.

**Files:**
- Create: `app/Domain/Catalog/Services/CategorySlugGenerator.php`
- Create: `tests/Unit/Domain/Catalog/CategorySlugGeneratorTest.php`

- [ ] **Step 1: Write the failing unit test**

```php
<?php

namespace Tests\Unit\Domain\Catalog;

use App\Domain\Catalog\Services\CategorySlugGenerator;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategorySlugGeneratorTest extends TestCase
{
    use RefreshDatabase;

    private CategorySlugGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->generator = new CategorySlugGenerator();
    }

    public function test_generates_slug_from_name(): void
    {
        $slug = $this->generator->generate('New Arrivals');

        $this->assertSame('new-arrivals', $slug);
    }

    public function test_appends_suffix_when_slug_exists(): void
    {
        Category::factory()->create(['slug' => 'shoes']);

        $slug = $this->generator->generate('Shoes');

        $this->assertSame('shoes-2', $slug);
    }

    public function test_increments_suffix_until_unique(): void
    {
        Category::factory()->create(['slug' => 'shoes']);
        Category::factory()->create(['slug' => 'shoes-2']);

        $slug = $this->generator->generate('Shoes');

        $this->assertSame('shoes-3', $slug);
    }

    public function test_excludes_given_id_from_uniqueness_check(): void
    {
        $category = Category::factory()->create(['slug' => 'shoes']);

        $slug = $this->generator->generate('Shoes', $category->id);

        $this->assertSame('shoes', $slug);
    }
}
```

- [ ] **Step 2: Run test — expect failure**

```bash
php artisan test tests/Unit/Domain/Catalog/CategorySlugGeneratorTest.php
```

Expected: FAIL — class not found.

- [ ] **Step 3: Create CategorySlugGenerator**

```php
<?php

namespace App\Domain\Catalog\Services;

use App\Models\Category;
use Illuminate\Support\Str;

class CategorySlugGenerator
{
    public function generate(string $name, ?int $excludeId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $counter = 2;

        while ($this->slugExists($slug, $excludeId)) {
            $slug = $base . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function slugExists(string $slug, ?int $excludeId): bool
    {
        return Category::where('slug', $slug)
            ->when($excludeId !== null, fn ($q) => $q->where('id', '!=', $excludeId))
            ->exists();
    }
}
```

- [ ] **Step 4: Run test — expect pass**

```bash
php artisan test tests/Unit/Domain/Catalog/CategorySlugGeneratorTest.php
```

Expected: 4 tests PASS.

- [ ] **Step 5: Commit**

```bash
git add app/Domain/Catalog/Services/CategorySlugGenerator.php tests/Unit/Domain/Catalog/CategorySlugGeneratorTest.php
git commit -m "feat: add CategorySlugGenerator with uniqueness collision handling"
```

---

## Task 4: Register media collection on Category model

Spatie MediaLibrary requires an explicit `registerMediaCollections()` method to define collections.

**Files:**
- Modify: `app/Models/Category.php`

- [ ] **Step 1: Add registerMediaCollections to Category**

Add after the `isActive()` method, before the closing `}`:

```php
public function registerMediaCollections(): void
{
    $this->addMediaCollection('images')->singleFile();
}
```

The full file after editing:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Category extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'description',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')->singleFile();
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add app/Models/Category.php
git commit -m "feat: register images media collection on Category model"
```

---

## Task 5: SyncCategoryMediaAction

**Files:**
- Create: `app/Domain/Catalog/Actions/SyncCategoryMediaAction.php`

- [ ] **Step 1: Create SyncCategoryMediaAction**

```php
<?php

namespace App\Domain\Catalog\Actions;

use App\Models\Category;
use Illuminate\Http\UploadedFile;

class SyncCategoryMediaAction
{
    public function execute(Category $category, ?UploadedFile $image, bool $removeImage = false): void
    {
        if ($image !== null) {
            $category->clearMediaCollection('images');
            $category->addMedia($image)->toMediaCollection('images');
        } elseif ($removeImage) {
            $category->clearMediaCollection('images');
        }
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add app/Domain/Catalog/Actions/SyncCategoryMediaAction.php
git commit -m "feat: add SyncCategoryMediaAction for category image handling"
```

---

## Task 6: ListAdminCategoriesQuery

Loads all categories as a tree, flattens into an array with a `depth` field. No pagination — categories dataset is small.

**Files:**
- Create: `app/Domain/Catalog/Queries/ListAdminCategoriesQuery.php`

- [ ] **Step 1: Create the query class**

```php
<?php

namespace App\Domain\Catalog\Queries;

use App\Models\Category;
use Illuminate\Support\Collection;

class ListAdminCategoriesQuery
{
    public function get(): array
    {
        $roots = Category::with($this->childrenRelation())
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return $this->flatten($roots, 0, null);
    }

    private function childrenRelation(): array
    {
        return [
            'children' => function ($query) {
                $query->orderBy('sort_order')->orderBy('name')
                    ->with([
                        'children' => function ($q) {
                            $q->orderBy('sort_order')->orderBy('name')
                                ->with([
                                    'children' => function ($q2) {
                                        $q2->orderBy('sort_order')->orderBy('name');
                                    },
                                ]);
                        },
                    ]);
            },
        ];
    }

    private function flatten(Collection $categories, int $depth, ?string $parentName): array
    {
        $result = [];

        foreach ($categories as $category) {
            $result[] = $this->toArray($category, $depth, $parentName);

            if ($category->children->isNotEmpty()) {
                $result = array_merge(
                    $result,
                    $this->flatten($category->children, $depth + 1, $category->name),
                );
            }
        }

        return $result;
    }

    private function toArray(Category $category, int $depth, ?string $parentName): array
    {
        return [
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'description' => $category->description,
            'is_active' => $category->is_active,
            'sort_order' => $category->sort_order,
            'parent_id' => $category->parent_id,
            'parent_name' => $parentName,
            'depth' => $depth,
            'image_url' => $category->getFirstMediaUrl('images') ?: null,
            'children_count' => $category->children->count(),
            'created_at' => $category->created_at?->toISOString(),
        ];
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add app/Domain/Catalog/Queries/ListAdminCategoriesQuery.php
git commit -m "feat: add ListAdminCategoriesQuery with tree flattening"
```

---

## Task 7: Update Form Requests

The existing requests are missing the `image` field and the slug is incorrectly required on `StoreCategoryRequest`.

**Files:**
- Modify: `app/Http/Requests/Admin/StoreCategoryRequest.php`
- Modify: `app/Http/Requests/Admin/UpdateCategoryRequest.php`

- [ ] **Step 1: Update StoreCategoryRequest**

Replace the entire file:

```php
<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('categories', 'slug')],
            'parent_id' => ['nullable', 'integer', 'exists:categories,id'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_image' => ['boolean'],
        ];
    }
}
```

- [ ] **Step 2: Update UpdateCategoryRequest**

Replace the entire file:

```php
<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $categoryId = $this->route('category')?->getKey();

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('categories', 'slug')->ignore($categoryId)],
            'parent_id' => ['nullable', 'integer', 'exists:categories,id', Rule::notIn([$categoryId])],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_image' => ['boolean'],
        ];
    }
}
```

- [ ] **Step 3: Commit**

```bash
git add app/Http/Requests/Admin/StoreCategoryRequest.php app/Http/Requests/Admin/UpdateCategoryRequest.php
git commit -m "feat: update category form requests with image upload and nullable slug"
```

---

## Task 8: Wire up CategoryController

**Files:**
- Modify: `app/Http/Controllers/Admin/Catalog/CategoryController.php`

- [ ] **Step 1: Replace CategoryController with full implementation**

```php
<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Domain\Catalog\Actions\CreateCategoryAction;
use App\Domain\Catalog\Actions\SyncCategoryMediaAction;
use App\Domain\Catalog\Actions\ToggleCategoryStatusAction;
use App\Domain\Catalog\Actions\UpdateCategoryAction;
use App\Domain\Catalog\Queries\ListAdminCategoriesQuery;
use App\Domain\Catalog\Services\CategorySlugGenerator;
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
        private CategorySlugGenerator $slugGenerator,
        private CreateCategoryAction $createAction,
        private UpdateCategoryAction $updateAction,
        private ToggleCategoryStatusAction $toggleAction,
        private SyncCategoryMediaAction $syncMediaAction,
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

        if (empty($data['slug'])) {
            $data['slug'] = $this->slugGenerator->generate($data['name']);
        }

        $category = $this->createAction->execute($data);
        $this->syncMediaAction->execute($category, $request->file('image'));

        return redirect()->route('admin.catalog.categories.index')
            ->with('success', 'Category created successfully.');
    }

    public function show(Category $category): InertiaResponse
    {
        $category->load('children');
        $category->loadMedia();

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
        $category->loadMedia();

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
                'description' => $category->description,
            ],
            'categories' => Category::orderBy('name')->get(['id', 'name', 'parent_id'])->toArray(),
        ]);
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $data = $request->safe()->except(['image', 'remove_image']);

        if (isset($data['name']) && empty($data['slug'])) {
            $data['slug'] = $this->slugGenerator->generate($data['name'], $category->id);
        }

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
        $this->authorize('update', $category);
        $this->toggleAction->execute($category);

        return back()->with('success', 'Category status updated.');
    }
}
```

Note: the `edit()` method has `description` listed twice — remove the second one. The corrected `edit()` should be:

```php
public function edit(Category $category): InertiaResponse
{
    $category->loadMedia();

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
```

- [ ] **Step 2: Commit**

```bash
git add app/Http/Controllers/Admin/Catalog/CategoryController.php
git commit -m "feat: wire CategoryController to domain layer with full CRUD"
```

---

## Task 9: Update routes and regenerate wayfinder

**Files:**
- Modify: `routes/web.php`

- [ ] **Step 1: Update the categories resource route**

In `routes/web.php`, inside the `catalog` prefix group, find:
```php
Route::resource('categories', CategoryController::class);
```

Replace with:
```php
Route::resource('categories', CategoryController::class)->except(['destroy']);
Route::patch('categories/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])
    ->name('catalog.categories.toggle-status');
```

- [ ] **Step 2: Verify routes registered**

```bash
php artisan route:list --name=admin.catalog.categories
```

Expected output includes:
```
GET|HEAD   admin/catalog/categories ........................ admin.catalog.categories.index
GET|HEAD   admin/catalog/categories/create ................. admin.catalog.categories.create
POST       admin/catalog/categories ........................ admin.catalog.categories.store
GET|HEAD   admin/catalog/categories/{category} ............. admin.catalog.categories.show
GET|HEAD   admin/catalog/categories/{category}/edit ........ admin.catalog.categories.edit
PUT|PATCH  admin/catalog/categories/{category} ............. admin.catalog.categories.update
PATCH      admin/catalog/categories/{category}/toggle-status admin.catalog.categories.toggle-status
```

No `DELETE` route should appear.

- [ ] **Step 3: Regenerate wayfinder actions**

The wayfinder auto-generates when Vite is running. Start dev server to trigger regeneration:

```bash
npm run dev
```

After the server starts, check that the `toggleStatus` action was generated:

```bash
grep -l "toggleStatus" resources/js/actions/App/Http/Controllers/Admin/Catalog/CategoryController.ts
```

Expected: file path printed (means the function was generated). Stop the dev server (Ctrl+C) after confirming.

- [ ] **Step 4: Commit**

```bash
git add routes/web.php resources/js/actions/App/Http/Controllers/Admin/Catalog/CategoryController.ts
git commit -m "feat: add toggle-status route for categories and regenerate wayfinder"
```

---

## Task 10: Feature tests for CategoryController

**Files:**
- Create: `tests/Feature/Admin/Catalog/CategoryTest.php`

- [ ] **Step 1: Write the feature tests**

```php
<?php

namespace Tests\Feature\Admin\Catalog;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::firstOrCreate(['name' => 'manage categories', 'guard_name' => 'web']);

        $this->admin = User::factory()->create();
        $this->admin->givePermissionTo('manage categories');
    }

    public function test_guests_are_redirected_from_categories_index(): void
    {
        $response = $this->get(route('admin.catalog.categories.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_admin_can_view_categories_index(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.catalog.categories.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('admin/catalog/categories/index'));
    }

    public function test_admin_can_view_create_form(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.catalog.categories.create'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('admin/catalog/categories/create'));
    }

    public function test_admin_can_create_category(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('admin.catalog.categories.store'), [
            'name' => 'Footwear',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('admin.catalog.categories.index'));
        $this->assertDatabaseHas('categories', ['name' => 'Footwear', 'slug' => 'footwear']);
    }

    public function test_store_auto_generates_slug_from_name(): void
    {
        $this->actingAs($this->admin);

        $this->post(route('admin.catalog.categories.store'), [
            'name' => 'New Arrivals',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('categories', ['slug' => 'new-arrivals']);
    }

    public function test_store_uses_provided_slug(): void
    {
        $this->actingAs($this->admin);

        $this->post(route('admin.catalog.categories.store'), [
            'name' => 'Footwear',
            'slug' => 'custom-slug',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('categories', ['slug' => 'custom-slug']);
    }

    public function test_admin_can_view_edit_form(): void
    {
        $this->actingAs($this->admin);
        $category = Category::factory()->create();

        $response = $this->get(route('admin.catalog.categories.edit', $category));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('admin/catalog/categories/edit'));
    }

    public function test_admin_can_update_category(): void
    {
        $this->actingAs($this->admin);
        $category = Category::factory()->create(['name' => 'Old Name', 'slug' => 'old-name']);

        $response = $this->put(route('admin.catalog.categories.update', $category), [
            'name' => 'New Name',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('admin.catalog.categories.index'));
        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'New Name']);
    }

    public function test_admin_can_toggle_category_status(): void
    {
        $this->actingAs($this->admin);
        $category = Category::factory()->create(['is_active' => true]);

        $response = $this->patch(route('admin.catalog.categories.toggle-status', $category));

        $response->assertRedirect();
        $this->assertDatabaseHas('categories', ['id' => $category->id, 'is_active' => false]);
    }

    public function test_admin_can_view_category_show(): void
    {
        $this->actingAs($this->admin);
        $category = Category::factory()->create();

        $response = $this->get(route('admin.catalog.categories.show', $category));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('admin/catalog/categories/show'));
    }
}
```

- [ ] **Step 2: Check if CategoryFactory exists, create if needed**

```bash
php artisan make:factory CategoryFactory --model=Category 2>/dev/null || echo "Already exists"
```

If the factory doesn't exist, create `database/factories/CategoryFactory.php`:

```php
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CategoryFactory extends Factory
{
    public function definition(): array
    {
        $name = $this->faker->unique()->words(2, true);

        return [
            'name' => ucwords($name),
            'slug' => Str::slug($name) . '-' . $this->faker->unique()->numberBetween(1, 9999),
            'description' => $this->faker->optional()->sentence(),
            'is_active' => true,
            'sort_order' => 0,
            'parent_id' => null,
        ];
    }
}
```

- [ ] **Step 3: Run the tests**

```bash
php artisan test tests/Feature/Admin/Catalog/CategoryTest.php
```

Expected: all tests PASS. If `inertiaTesting` assertion fails, check that `inertia/testing` package is installed:
```bash
composer require inertiajs/inertia-laravel --dev 2>/dev/null; php artisan test tests/Feature/Admin/Catalog/CategoryTest.php
```

- [ ] **Step 4: Commit**

```bash
git add tests/Feature/Admin/Catalog/CategoryTest.php database/factories/CategoryFactory.php
git commit -m "test: add feature tests for admin category CRUD"
```

---

## Task 11: Update AdminCategory TypeScript type

**Files:**
- Modify: `resources/js/types/admin/catalog.ts`

- [ ] **Step 1: Expand AdminCategory interface**

Replace the `AdminCategory` interface (keep `AdminBrand`, `AdminProduct`, and `AdminCatalogListPage` unchanged):

```typescript
export interface AdminCategory {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    is_active: boolean;
    sort_order: number;
    parent_id: number | null;
    parent_name: string | null;
    depth: number;
    image_url: string | null;
    children_count: number;
    created_at: string;
}
```

The file after editing:

```typescript
import type { PaginationMeta } from '@/types/shared/pagination';

export interface AdminCategory {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    is_active: boolean;
    sort_order: number;
    parent_id: number | null;
    parent_name: string | null;
    depth: number;
    image_url: string | null;
    children_count: number;
    created_at: string;
}

export interface AdminBrand {
    id: number;
    name: string;
    slug: string;
    is_active: boolean;
}

export interface AdminProduct {
    id: number;
    name: string;
    slug: string;
    sku: string;
    status: string;
    product_type: string;
    base_price: number;
    is_featured: boolean;
}

export interface AdminCatalogListPage<T> {
    data: T[];
    meta?: PaginationMeta;
}
```

- [ ] **Step 2: Commit**

```bash
git add resources/js/types/admin/catalog.ts
git commit -m "feat: expand AdminCategory type with depth, image_url, and tree fields"
```

---

## Task 12: CategoryTable component

A table that renders the flattened tree with depth-based name indentation.

**Files:**
- Create: `resources/js/pages/admin/catalog/categories/_components/category-table.tsx`

- [ ] **Step 1: Create CategoryTable**

```tsx
import { Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { StatusBadge } from '@/components/shared/status-badge/status-badge';
import * as CategoryController from '@/actions/App/Http/Controllers/Admin/Catalog/CategoryController';
import type { AdminCategory } from '@/types/admin/catalog';

interface CategoryTableProps {
    categories: AdminCategory[];
}

export function CategoryTable({ categories }: CategoryTableProps) {
    if (categories.length === 0) {
        return (
            <div className="rounded-lg border bg-background px-6 py-12 text-center">
                <p className="text-sm text-muted-foreground">No categories yet. Create one to get started.</p>
            </div>
        );
    }

    return (
        <div className="overflow-hidden rounded-lg border bg-background">
            <div className="overflow-x-auto">
                <table className="min-w-full text-sm">
                    <thead className="bg-muted/40">
                        <tr>
                            <th className="px-4 py-3 text-left font-medium text-muted-foreground">Name</th>
                            <th className="px-4 py-3 text-left font-medium text-muted-foreground">Slug</th>
                            <th className="px-4 py-3 text-left font-medium text-muted-foreground">Parent</th>
                            <th className="px-4 py-3 text-left font-medium text-muted-foreground">Order</th>
                            <th className="px-4 py-3 text-left font-medium text-muted-foreground">Status</th>
                            <th className="px-4 py-3 text-left font-medium text-muted-foreground">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        {categories.map((category) => (
                            <tr key={category.id} className="border-t">
                                <td className="px-4 py-3 align-middle">
                                    <span
                                        className="flex items-center gap-1"
                                        style={{ paddingLeft: `${category.depth * 20}px` }}
                                    >
                                        {category.depth > 0 && (
                                            <span className="text-muted-foreground select-none">└</span>
                                        )}
                                        {category.name}
                                        {category.children_count > 0 && (
                                            <span className="ml-1 text-xs text-muted-foreground">
                                                ({category.children_count})
                                            </span>
                                        )}
                                    </span>
                                </td>
                                <td className="px-4 py-3 align-middle text-muted-foreground">{category.slug}</td>
                                <td className="px-4 py-3 align-middle text-muted-foreground">
                                    {category.parent_name ?? <span className="italic">Root</span>}
                                </td>
                                <td className="px-4 py-3 align-middle">{category.sort_order}</td>
                                <td className="px-4 py-3 align-middle">
                                    <StatusBadge status={category.is_active ? 'active' : 'inactive'} />
                                </td>
                                <td className="px-4 py-3 align-middle">
                                    <div className="flex items-center gap-2">
                                        <Button variant="outline" size="sm" asChild>
                                            <Link href={CategoryController.show.url(category)}>View</Link>
                                        </Button>
                                        <Button variant="outline" size="sm" asChild>
                                            <Link href={CategoryController.edit.url(category)}>Edit</Link>
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </div>
    );
}
```

- [ ] **Step 2: Commit**

```bash
git add resources/js/pages/admin/catalog/categories/_components/category-table.tsx
git commit -m "feat: add CategoryTable component with depth-indented tree display"
```

---

## Task 13: CategoryForm component

Full form handling create and edit. Uses Inertia `Form` component with auto-slug from name.

**Files:**
- Modify: `resources/js/pages/admin/catalog/categories/_components/category-form.tsx`

- [ ] **Step 1: Replace the stub with full implementation**

```tsx
import { Form } from '@inertiajs/react';
import { useState } from 'react';
import { FieldError } from '@/components/shared/forms/field-error';
import { FormActions } from '@/components/shared/forms/form-actions';
import { FormSection } from '@/components/shared/forms/form-section';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import * as CategoryController from '@/actions/App/Http/Controllers/Admin/Catalog/CategoryController';
import type { AdminCategory } from '@/types/admin/catalog';

interface ParentOption {
    id: number;
    name: string;
    parent_id: number | null;
}

interface CategoryFormProps {
    category?: AdminCategory;
    categories: ParentOption[];
}

function slugify(value: string): string {
    return value
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-');
}

export function CategoryForm({ category, categories }: CategoryFormProps) {
    const isEdit = category !== undefined;
    const [slugManual, setSlugManual] = useState(isEdit);

    const parentOptions = isEdit
        ? categories.filter((c) => c.id !== category.id)
        : categories;

    const formProps = isEdit
        ? CategoryController.update.form.patch(category)
        : CategoryController.store.form.post();

    return (
        <Form
            {...formProps}
            options={{ preserveScroll: true }}
            className="space-y-6"
        >
            {({ processing, errors, setData, data }) => (
                <>
                    <FormSection title="Category details" description="Basic information for organizing the catalog.">
                        <div className="grid gap-4 md:grid-cols-2">
                            <div className="space-y-2">
                                <Label htmlFor="name">Name</Label>
                                <Input
                                    id="name"
                                    name="name"
                                    defaultValue={category?.name ?? ''}
                                    placeholder="New arrivals"
                                    onChange={(e) => {
                                        if (!slugManual) {
                                            setData('slug', slugify(e.target.value));
                                        }
                                    }}
                                />
                                <FieldError message={errors.name} />
                            </div>

                            <div className="space-y-2">
                                <div className="flex items-center justify-between">
                                    <Label htmlFor="slug">Slug</Label>
                                    <button
                                        type="button"
                                        className="text-xs text-muted-foreground hover:text-foreground"
                                        onClick={() => setSlugManual((v) => !v)}
                                    >
                                        {slugManual ? '🔒 Manual' : '🔓 Auto'}
                                    </button>
                                </div>
                                <Input
                                    id="slug"
                                    name="slug"
                                    value={data.slug ?? category?.slug ?? ''}
                                    placeholder="new-arrivals"
                                    readOnly={!slugManual}
                                    className={!slugManual ? 'bg-muted text-muted-foreground' : ''}
                                    onChange={(e) => {
                                        if (slugManual) {
                                            setData('slug', e.target.value);
                                        }
                                    }}
                                />
                                <FieldError message={errors.slug} />
                            </div>
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="parent_id">Parent category</Label>
                            <Select
                                name="parent_id"
                                defaultValue={category?.parent_id?.toString() ?? ''}
                            >
                                <SelectTrigger id="parent_id">
                                    <SelectValue placeholder="None (root category)" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">None (root category)</SelectItem>
                                    {parentOptions.map((opt) => (
                                        <SelectItem key={opt.id} value={opt.id.toString()}>
                                            {opt.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            <FieldError message={errors.parent_id} />
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="description">Description</Label>
                            <textarea
                                id="description"
                                name="description"
                                defaultValue={category?.description ?? ''}
                                placeholder="Optional description shown on storefront..."
                                rows={3}
                                className="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                            />
                            <FieldError message={errors.description} />
                        </div>
                    </FormSection>

                    <FormSection title="Visibility" description="Control whether this category is shown on the storefront.">
                        <div className="flex items-center gap-3">
                            <Checkbox
                                id="is_active"
                                name="is_active"
                                defaultChecked={category?.is_active ?? true}
                            />
                            <Label htmlFor="is_active" className="cursor-pointer font-normal">
                                Active — visible on the storefront
                            </Label>
                        </div>
                        <FieldError message={errors.is_active} />
                    </FormSection>

                    <FormSection title="Image" description="Upload a category image. Recommended: square, at least 400×400px.">
                        {isEdit && category.image_url && (
                            <div className="space-y-2">
                                <p className="text-sm text-muted-foreground">Current image</p>
                                <div className="flex items-center gap-4">
                                    <img
                                        src={category.image_url}
                                        alt={category.name}
                                        className="h-20 w-20 rounded-md border object-cover"
                                    />
                                    <label className="flex cursor-pointer items-center gap-2 text-sm text-destructive hover:underline">
                                        <input type="checkbox" name="remove_image" value="1" className="sr-only" />
                                        Remove image
                                    </label>
                                </div>
                            </div>
                        )}
                        <div className="space-y-2">
                            <Label htmlFor="image">
                                {isEdit && category.image_url ? 'Replace image' : 'Upload image'}
                            </Label>
                            <Input
                                id="image"
                                name="image"
                                type="file"
                                accept="image/jpeg,image/png,image/webp"
                                className="cursor-pointer"
                            />
                            <FieldError message={errors.image} />
                        </div>
                    </FormSection>

                    <FormActions
                        submitLabel={isEdit ? 'Update category' : 'Create category'}
                        onCancel={() => window.history.back()}
                    />
                </>
            )}
        </Form>
    );
}
```

- [ ] **Step 2: Commit**

```bash
git add resources/js/pages/admin/catalog/categories/_components/category-form.tsx
git commit -m "feat: implement CategoryForm with auto-slug, parent select, image upload"
```

---

## Task 14: Index page

**Files:**
- Modify: `resources/js/pages/admin/catalog/categories/index.tsx`

- [ ] **Step 1: Replace stub with wired page**

```tsx
import { Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { PageHeader } from '@/components/shared/page-header/page-header';
import { AdminLayout } from '@/layouts/app/admin-layout';
import * as CategoryController from '@/actions/App/Http/Controllers/Admin/Catalog/CategoryController';
import { CategoryTable } from '@/pages/admin/catalog/categories/_components/category-table';
import type { AdminCategory } from '@/types/admin/catalog';

interface Props {
    categories: AdminCategory[];
}

export default function CategoryIndexPage({ categories }: Props) {
    return (
        <AdminLayout title="Categories">
            <div className="space-y-6">
                <PageHeader
                    title="Categories"
                    description="Manage hierarchy, ordering, and active visibility."
                    actions={
                        <Button asChild>
                            <Link href={CategoryController.create.url()}>Create category</Link>
                        </Button>
                    }
                />
                <CategoryTable categories={categories} />
            </div>
        </AdminLayout>
    );
}
```

- [ ] **Step 2: Commit**

```bash
git add resources/js/pages/admin/catalog/categories/index.tsx
git commit -m "feat: wire CategoryIndexPage to Inertia props"
```

---

## Task 15: Create page

**Files:**
- Modify: `resources/js/pages/admin/catalog/categories/create.tsx`

- [ ] **Step 1: Replace stub with wired page**

```tsx
import { Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { PageHeader } from '@/components/shared/page-header/page-header';
import { AdminLayout } from '@/layouts/app/admin-layout';
import * as CategoryController from '@/actions/App/Http/Controllers/Admin/Catalog/CategoryController';
import { CategoryForm } from '@/pages/admin/catalog/categories/_components/category-form';

interface ParentOption {
    id: number;
    name: string;
    parent_id: number | null;
}

interface Props {
    categories: ParentOption[];
}

export default function CategoryCreatePage({ categories }: Props) {
    return (
        <AdminLayout title="Create Category">
            <div className="space-y-6">
                <PageHeader
                    title="Create category"
                    description="Set up a new category for product organisation."
                    actions={
                        <Button variant="outline" asChild>
                            <Link href={CategoryController.index.url()}>Back to categories</Link>
                        </Button>
                    }
                />
                <CategoryForm categories={categories} />
            </div>
        </AdminLayout>
    );
}
```

- [ ] **Step 2: Commit**

```bash
git add resources/js/pages/admin/catalog/categories/create.tsx
git commit -m "feat: wire CategoryCreatePage to Inertia props"
```

---

## Task 16: Edit page

**Files:**
- Modify: `resources/js/pages/admin/catalog/categories/edit.tsx`

- [ ] **Step 1: Replace stub with wired page**

```tsx
import { Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { PageHeader } from '@/components/shared/page-header/page-header';
import { AdminLayout } from '@/layouts/app/admin-layout';
import * as CategoryController from '@/actions/App/Http/Controllers/Admin/Catalog/CategoryController';
import { CategoryForm } from '@/pages/admin/catalog/categories/_components/category-form';
import type { AdminCategory } from '@/types/admin/catalog';

interface ParentOption {
    id: number;
    name: string;
    parent_id: number | null;
}

interface Props {
    category: AdminCategory;
    categories: ParentOption[];
}

export default function CategoryEditPage({ category, categories }: Props) {
    return (
        <AdminLayout title="Edit Category">
            <div className="space-y-6">
                <PageHeader
                    title={`Edit: ${category.name}`}
                    description="Adjust metadata, hierarchy, and visibility."
                    actions={
                        <Button variant="outline" asChild>
                            <Link href={CategoryController.show.url(category)}>View category</Link>
                        </Button>
                    }
                />
                <CategoryForm category={category} categories={categories} />
            </div>
        </AdminLayout>
    );
}
```

- [ ] **Step 2: Commit**

```bash
git add resources/js/pages/admin/catalog/categories/edit.tsx
git commit -m "feat: wire CategoryEditPage to Inertia props"
```

---

## Task 17: Show page

**Files:**
- Modify: `resources/js/pages/admin/catalog/categories/show.tsx`

- [ ] **Step 1: Replace stub with full detail page**

```tsx
import { Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { PageHeader } from '@/components/shared/page-header/page-header';
import { StatusBadge } from '@/components/shared/status-badge/status-badge';
import { AdminLayout } from '@/layouts/app/admin-layout';
import * as CategoryController from '@/actions/App/Http/Controllers/Admin/Catalog/CategoryController';
import type { AdminCategory } from '@/types/admin/catalog';

interface ChildSummary {
    id: number;
    name: string;
    slug: string;
    is_active: boolean;
}

interface CategoryDetail extends AdminCategory {
    children: ChildSummary[];
}

interface Props {
    category: CategoryDetail;
}

export default function CategoryShowPage({ category }: Props) {
    return (
        <AdminLayout title="Category Details">
            <div className="space-y-6">
                <PageHeader
                    title={category.name}
                    description="Review this category's configuration."
                    actions={
                        <div className="flex items-center gap-2">
                            <Button variant="outline" asChild>
                                <Link href={CategoryController.index.url()}>Back to list</Link>
                            </Button>
                            <Button asChild>
                                <Link href={CategoryController.edit.url(category)}>Edit</Link>
                            </Button>
                        </div>
                    }
                />

                <div className="grid gap-6 md:grid-cols-3">
                    <div className="md:col-span-2 space-y-6">
                        <Card>
                            <CardHeader>
                                <CardTitle>Details</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-3 text-sm">
                                <div className="flex items-center justify-between border-b pb-3">
                                    <span className="text-muted-foreground">Name</span>
                                    <span className="font-medium">{category.name}</span>
                                </div>
                                <div className="flex items-center justify-between border-b pb-3">
                                    <span className="text-muted-foreground">Slug</span>
                                    <span className="font-mono text-xs">{category.slug}</span>
                                </div>
                                <div className="flex items-center justify-between border-b pb-3">
                                    <span className="text-muted-foreground">Parent</span>
                                    <span>{category.parent_name ?? <span className="italic text-muted-foreground">Root category</span>}</span>
                                </div>
                                <div className="flex items-center justify-between border-b pb-3">
                                    <span className="text-muted-foreground">Status</span>
                                    <StatusBadge status={category.is_active ? 'active' : 'inactive'} />
                                </div>
                                <div className="flex items-center justify-between">
                                    <span className="text-muted-foreground">Sort order</span>
                                    <span>{category.sort_order}</span>
                                </div>
                            </CardContent>
                        </Card>

                        {category.description && (
                            <Card>
                                <CardHeader>
                                    <CardTitle>Description</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <p className="text-sm text-muted-foreground">{category.description}</p>
                                </CardContent>
                            </Card>
                        )}

                        {category.children.length > 0 && (
                            <Card>
                                <CardHeader>
                                    <CardTitle>Sub-categories ({category.children.length})</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <ul className="space-y-2">
                                        {category.children.map((child) => (
                                            <li key={child.id} className="flex items-center justify-between text-sm">
                                                <Link
                                                    href={CategoryController.show.url(child)}
                                                    className="hover:underline"
                                                >
                                                    {child.name}
                                                </Link>
                                                <StatusBadge status={child.is_active ? 'active' : 'inactive'} />
                                            </li>
                                        ))}
                                    </ul>
                                </CardContent>
                            </Card>
                        )}
                    </div>

                    {category.image_url && (
                        <div>
                            <Card>
                                <CardHeader>
                                    <CardTitle>Image</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <img
                                        src={category.image_url}
                                        alt={category.name}
                                        className="w-full rounded-md border object-cover"
                                    />
                                </CardContent>
                            </Card>
                        </div>
                    )}
                </div>
            </div>
        </AdminLayout>
    );
}
```

- [ ] **Step 2: Commit**

```bash
git add resources/js/pages/admin/catalog/categories/show.tsx
git commit -m "feat: implement CategoryShowPage with full detail view"
```

---

## Task 18: TypeScript check

- [ ] **Step 1: Run TypeScript compiler**

```bash
npm run types:check
```

Expected: no errors. If errors appear, they will be in the TypeScript output — fix each one before proceeding.

Common issues to look for:
- `Form` component from `@inertiajs/react` — the render prop types. If `setData` or `data` are not typed, cast `data` to the form shape.
- The `Form` component's render prop signature in Inertia v3: `({ processing, errors, data, setData }) => ReactNode`

If `data` and `setData` are not in scope from the Form render prop (depends on Inertia v3 API), simplify the slug auto-generation by using controlled React state instead. Replace the `data.slug` reference with a local `useState` for the slug value:

```tsx
// At top of CategoryForm, add:
const [slugValue, setSlugValue] = useState(category?.slug ?? '');

// In the slug Input:
value={slugValue}
onChange={(e) => { if (slugManual) setSlugValue(e.target.value); }}

// In the name Input onChange:
onChange={(e) => { if (!slugManual) setSlugValue(slugify(e.target.value)); }}
```

And keep `<input type="hidden" name="slug" value={slugValue} />` before the FormActions so the slug is submitted with the form.

- [ ] **Step 2: Fix any TypeScript errors found**

- [ ] **Step 3: Run lint check**

```bash
npm run lint:check
```

Fix any lint errors before committing.

- [ ] **Step 4: Final commit**

```bash
git add -A
git commit -m "feat: complete admin categories vertical slice (Batch 5)"
```

---

## Task 19: Manual smoke test

- [ ] **Step 1: Run migrations and seed**

```bash
php artisan migrate:fresh --seed
```

- [ ] **Step 2: Start dev server**

```bash
npm run dev
```

In a second terminal:
```bash
php artisan serve
```

- [ ] **Step 3: Smoke test checklist**

- [ ] Log in as `admin@example.com` / `password123!`
- [ ] Navigate to `/admin/catalog/categories` — page loads, empty state shown
- [ ] Click "Create category" — form loads with all fields
- [ ] Create a root category (name: "Footwear") — auto-slug fills "footwear", form submits, redirected to index
- [ ] Create a child category (name: "Sneakers", parent: Footwear) — appears indented under Footwear in tree
- [ ] Click "View" on Footwear — show page displays name, slug, children list
- [ ] Click "Edit" on Sneakers — form pre-filled, update name, submit, redirected to index
- [ ] Upload an image when editing — image shown in show page after save
