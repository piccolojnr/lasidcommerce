# Category Soft Delete Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add soft delete to admin categories — blocked when children or products exist, no restore UI.

**Architecture:** Domain action enforces blocking rules and throws `CannotDeleteCategoryException`. Controller catches the exception and redirects back with a flash error. Eloquent `SoftDeletes` trait automatically excludes deleted rows from all existing queries.

**Tech Stack:** Laravel 12, Eloquent SoftDeletes, Inertia.js v3 `Form` component, React 19, Wayfinder.

---

## File Map

### New files
| File | Purpose |
|---|---|
| `database/migrations/YYYY_MM_DD_HHMMSS_add_soft_deletes_to_categories_table.php` | Add `deleted_at` column to categories |
| `app/Domain/Catalog/Exceptions/CannotDeleteCategoryException.php` | Domain exception for blocked deletions |
| `app/Domain/Catalog/Actions/DeleteCategoryAction.php` | Enforces blocking rules; soft deletes the category |
| `tests/Unit/Domain/Catalog/DeleteCategoryActionTest.php` | Unit tests for the action |

### Modified files
| File | What changes |
|---|---|
| `app/Models/Category.php` | Add `use SoftDeletes` |
| `app/Http/Controllers/Admin/Catalog/CategoryController.php` | Inject `DeleteCategoryAction`; add `destroy()` method |
| `routes/web.php` | Remove `'destroy'` from `->except([])` |
| `tests/Feature/Admin/Catalog/CategoryTest.php` | Add delete feature tests |
| `resources/js/pages/admin/catalog/categories/_components/category-table.tsx` | Add Delete button with confirm dialog |

---

## Task 1: Migration + SoftDeletes on Model

**Files:**
- Create: `database/migrations/<timestamp>_add_soft_deletes_to_categories_table.php`
- Modify: `app/Models/Category.php`

- [ ] **Step 1: Create the migration**

```bash
cd /path/to/project
php artisan make:migration add_soft_deletes_to_categories_table
```

Open the generated file (it will be in `database/migrations/` with a timestamp prefix) and replace its contents with:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
```

- [ ] **Step 2: Run the migration**

```bash
php artisan migrate
```

Expected output: `Migrating: ..._add_soft_deletes_to_categories_table` then `Migrated`.

- [ ] **Step 3: Add SoftDeletes to the Category model**

Open `app/Models/Category.php`. Add the `SoftDeletes` import and trait:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Category extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, SoftDeletes;

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

- [ ] **Step 4: Verify existing category tests still pass**

```bash
php artisan test tests/Feature/Admin/Catalog/CategoryTest.php
```

Expected: 10 tests pass. SoftDeletes should not break any existing behaviour.

- [ ] **Step 5: Commit**

```bash
git add database/migrations/ app/Models/Category.php
git commit -m "feat: add soft deletes to categories table and model"
```

---

## Task 2: CannotDeleteCategoryException + DeleteCategoryAction + Unit Tests

**Files:**
- Create: `app/Domain/Catalog/Exceptions/CannotDeleteCategoryException.php`
- Create: `app/Domain/Catalog/Actions/DeleteCategoryAction.php`
- Create: `tests/Unit/Domain/Catalog/DeleteCategoryActionTest.php`

- [ ] **Step 1: Write the failing unit tests**

Create `tests/Unit/Domain/Catalog/DeleteCategoryActionTest.php`:

```php
<?php

namespace Tests\Unit\Domain\Catalog;

use App\Domain\Catalog\Actions\DeleteCategoryAction;
use App\Domain\Catalog\Exceptions\CannotDeleteCategoryException;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteCategoryActionTest extends TestCase
{
    use RefreshDatabase;

    private DeleteCategoryAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new DeleteCategoryAction();
    }

    public function test_soft_deletes_category_with_no_children_or_products(): void
    {
        $category = Category::factory()->create();

        $this->action->execute($category);

        $this->assertSoftDeleted('categories', ['id' => $category->id]);
    }

    public function test_throws_when_category_has_children(): void
    {
        $parent = Category::factory()->create();
        Category::factory()->create(['parent_id' => $parent->id]);

        $this->expectException(CannotDeleteCategoryException::class);
        $this->expectExceptionMessage('subcategories');

        $this->action->execute($parent);
    }

    public function test_does_not_delete_when_children_exist(): void
    {
        $parent = Category::factory()->create();
        Category::factory()->create(['parent_id' => $parent->id]);

        try {
            $this->action->execute($parent);
        } catch (CannotDeleteCategoryException) {
        }

        $this->assertDatabaseHas('categories', ['id' => $parent->id, 'deleted_at' => null]);
    }

    public function test_throws_when_category_has_products(): void
    {
        $category = Category::factory()->create();
        \DB::table('products')->insert([
            'category_id' => $category->id,
            'name' => 'Test Product',
            'slug' => 'test-product',
            'sku' => 'TEST-001',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->expectException(CannotDeleteCategoryException::class);
        $this->expectExceptionMessage('products');

        $this->action->execute($category);
    }

    public function test_does_not_delete_when_products_exist(): void
    {
        $category = Category::factory()->create();
        \DB::table('products')->insert([
            'category_id' => $category->id,
            'name' => 'Test Product',
            'slug' => 'test-product-2',
            'sku' => 'TEST-002',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            $this->action->execute($category);
        } catch (CannotDeleteCategoryException) {
        }

        $this->assertDatabaseHas('categories', ['id' => $category->id, 'deleted_at' => null]);
    }
}
```

- [ ] **Step 2: Run tests to verify they fail**

```bash
php artisan test tests/Unit/Domain/Catalog/DeleteCategoryActionTest.php
```

Expected: FAIL — `DeleteCategoryAction` and `CannotDeleteCategoryException` do not exist yet.

- [ ] **Step 3: Create CannotDeleteCategoryException**

Create `app/Domain/Catalog/Exceptions/CannotDeleteCategoryException.php`:

```php
<?php

namespace App\Domain\Catalog\Exceptions;

use RuntimeException;

class CannotDeleteCategoryException extends RuntimeException
{
}
```

- [ ] **Step 4: Create DeleteCategoryAction**

Create `app/Domain/Catalog/Actions/DeleteCategoryAction.php`:

```php
<?php

namespace App\Domain\Catalog\Actions;

use App\Domain\Catalog\Exceptions\CannotDeleteCategoryException;
use App\Models\Category;

class DeleteCategoryAction
{
    public function execute(Category $category): void
    {
        if ($category->children()->exists()) {
            throw new CannotDeleteCategoryException(
                'This category has subcategories. Reassign or delete them first.'
            );
        }

        if ($category->products()->exists()) {
            throw new CannotDeleteCategoryException(
                'This category has products assigned to it. Reassign them first.'
            );
        }

        $category->delete();
    }
}
```

- [ ] **Step 5: Run tests to verify they pass**

```bash
php artisan test tests/Unit/Domain/Catalog/DeleteCategoryActionTest.php
```

Expected: 5 tests pass.

- [ ] **Step 6: Commit**

```bash
git add app/Domain/Catalog/Exceptions/CannotDeleteCategoryException.php \
        app/Domain/Catalog/Actions/DeleteCategoryAction.php \
        tests/Unit/Domain/Catalog/DeleteCategoryActionTest.php
git commit -m "feat: add DeleteCategoryAction with child/product blocking"
```

---

## Task 3: Controller + Routes + Feature Tests

**Files:**
- Modify: `app/Http/Controllers/Admin/Catalog/CategoryController.php`
- Modify: `routes/web.php`
- Modify: `tests/Feature/Admin/Catalog/CategoryTest.php`

- [ ] **Step 1: Write the failing feature tests**

Open `tests/Feature/Admin/Catalog/CategoryTest.php` and add these tests at the end of the class (before the closing `}`):

```php
    public function test_admin_can_soft_delete_category(): void
    {
        $this->actingAs($this->admin);
        $category = Category::factory()->create();

        $response = $this->delete(route('admin.catalog.categories.destroy', $category));

        $response->assertRedirect(route('admin.catalog.categories.index'));
        $this->assertSoftDeleted('categories', ['id' => $category->id]);
    }

    public function test_delete_is_blocked_when_category_has_children(): void
    {
        $this->actingAs($this->admin);
        $parent = Category::factory()->create();
        Category::factory()->create(['parent_id' => $parent->id]);

        $response = $this->delete(route('admin.catalog.categories.destroy', $parent));

        $response->assertRedirect();
        $this->assertDatabaseHas('categories', ['id' => $parent->id, 'deleted_at' => null]);
    }

    public function test_delete_is_blocked_when_category_has_products(): void
    {
        $this->actingAs($this->admin);
        $category = Category::factory()->create();
        \DB::table('products')->insert([
            'category_id' => $category->id,
            'name' => 'Blocked Product',
            'slug' => 'blocked-product',
            'sku' => 'BLK-001',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->delete(route('admin.catalog.categories.destroy', $category));

        $response->assertRedirect();
        $this->assertDatabaseHas('categories', ['id' => $category->id, 'deleted_at' => null]);
    }

    public function test_guest_is_redirected_from_destroy(): void
    {
        $category = Category::factory()->create();

        $response = $this->delete(route('admin.catalog.categories.destroy', $category));

        $response->assertRedirect(route('login'));
    }

    public function test_soft_deleted_category_excluded_from_index(): void
    {
        $this->actingAs($this->admin);
        $category = Category::factory()->create();
        $category->delete();

        $response = $this->get(route('admin.catalog.categories.index'));

        $response->assertInertia(fn ($page) => $page
            ->component('admin/catalog/categories/index')
            ->where('categories', fn ($cats) => collect($cats)->every(fn ($c) => $c['id'] !== $category->id))
        );
    }
```

- [ ] **Step 2: Run tests to verify they fail**

```bash
php artisan test tests/Feature/Admin/Catalog/CategoryTest.php
```

Expected: The 5 new tests fail — route `admin.catalog.categories.destroy` does not exist yet.

- [ ] **Step 3: Update routes/web.php**

Open `routes/web.php`. Find the categories resource route:

```php
// destroy is intentionally excluded — category deletion requires handling child categories
Route::resource('categories', CategoryController::class)->except(['destroy']);
```

Replace it with:

```php
Route::resource('categories', CategoryController::class);
```

- [ ] **Step 4: Update CategoryController**

Open `app/Http/Controllers/Admin/Catalog/CategoryController.php`.

Add two imports after the existing `use` statements:

```php
use App\Domain\Catalog\Actions\DeleteCategoryAction;
use App\Domain\Catalog\Exceptions\CannotDeleteCategoryException;
```

Add `$deleteAction` to the constructor:

```php
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
```

Add the `destroy` method at the end of the class (before the closing `}`):

```php
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
```

- [ ] **Step 5: Run tests to verify they pass**

```bash
php artisan test tests/Feature/Admin/Catalog/CategoryTest.php
```

Expected: All 15 tests pass.

- [ ] **Step 6: Commit**

```bash
git add app/Http/Controllers/Admin/Catalog/CategoryController.php \
        routes/web.php \
        tests/Feature/Admin/Catalog/CategoryTest.php
git commit -m "feat: wire destroy route and controller for category soft delete"
```

---

## Task 4: Frontend Delete Button

**Files:**
- Modify: `resources/js/pages/admin/catalog/categories/_components/category-table.tsx`

- [ ] **Step 1: Add the Delete button**

Open `resources/js/pages/admin/catalog/categories/_components/category-table.tsx`.

Add `Form` to the import from `@inertiajs/react`:

```tsx
import { Form, Link } from '@inertiajs/react';
```

Replace the Actions `<td>` content:

```tsx
                                <td className="px-4 py-3 align-middle">
                                    <div className="flex items-center gap-2">
                                        <Button variant="outline" size="sm" asChild>
                                            <Link href={CategoryController.show.url(category)}>View</Link>
                                        </Button>
                                        <Button variant="outline" size="sm" asChild>
                                            <Link href={CategoryController.edit.url(category)}>Edit</Link>
                                        </Button>
                                        <Form
                                            {...CategoryController.destroy.form.delete(category)}
                                            onSubmit={(e) => {
                                                if (!window.confirm('Are you sure you want to delete "' + category.name + '"?')) {
                                                    e.preventDefault();
                                                }
                                            }}
                                        >
                                            {() => (
                                                <Button
                                                    type="submit"
                                                    variant="outline"
                                                    size="sm"
                                                    className="text-destructive hover:text-destructive"
                                                >
                                                    Delete
                                                </Button>
                                            )}
                                        </Form>
                                    </div>
                                </td>
```

- [ ] **Step 2: Run TypeScript check**

```bash
npm run types:check
```

Expected: no errors. If `CategoryController.destroy.form.delete` is not typed, Vite may need a rebuild to regenerate the wayfinder types:

```bash
npm run build
npm run types:check
```

- [ ] **Step 3: Run lint check**

```bash
npm run lint:check
```

Fix any lint errors (likely import ordering — `@inertiajs/react` imports should be grouped together).

- [ ] **Step 4: Commit**

```bash
git add resources/js/pages/admin/catalog/categories/_components/category-table.tsx
git commit -m "feat: add delete button to category table with confirmation"
```

---

## Self-Review

**Spec coverage check:**

| Spec requirement | Task |
|---|---|
| Add `deleted_at` migration | Task 1 |
| `Category` model uses `SoftDeletes` | Task 1 |
| `CannotDeleteCategoryException` | Task 2 |
| `DeleteCategoryAction` blocks on children | Task 2 |
| `DeleteCategoryAction` blocks on products | Task 2 |
| `DeleteCategoryAction` soft deletes when clear | Task 2 |
| `CategoryPolicy::delete()` wired via `authorizeResource` | Task 3 (already in constructor — no change needed) |
| `destroy` route registered | Task 3 |
| Controller `destroy()` method | Task 3 |
| Redirect to index on success | Task 3 |
| Redirect back with error on block | Task 3 |
| Frontend delete button with `confirm()` | Task 4 |
| Soft-deleted rows excluded from index | Task 3 (verified by test) |

All spec requirements covered. No placeholders. Types consistent across tasks (`CannotDeleteCategoryException`, `DeleteCategoryAction`, `Category`).
