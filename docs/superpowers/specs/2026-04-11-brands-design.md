# Admin Brands — Design Spec

**Date:** 2026-04-11
**Batch:** 5 — Catalog Management (second vertical slice)
**Status:** Approved

---

## Overview

Implement the Brands feature end-to-end, following the same pattern as Categories. Full CRUD (including soft delete), toggle status, auto-slug generation, image upload, and a paginated index. No hierarchy — brands are a flat list.

---

## Decisions

| Decision | Choice | Reason |
|---|---|---|
| Index display | Paginated (20 per page) | Brands can grow large; flat list suits pagination well |
| Slug generation | Auto-generate from name, user-overridable | Same as categories |
| Delete | Soft delete, block if products exist | Same as categories; FK changed to restrictOnDelete |
| Hierarchy | None | Brands are flat — no parent/depth |
| Toggle status | PATCH route, same as categories | Consistent admin UX |

---

## Architecture & Data Flow

```
Browser → Inertia → BrandController → Domain Action/Query → Model → DB
```

### Index (paginated)
`ListAdminBrandsQuery` paginates brands (20/page), eager-loads `media`, returns `LengthAwarePaginator`. Inertia serializes this to `{ data, links, meta }`.

### Create / Store
1. `create()` renders form (no extra props needed)
2. `StoreBrandRequest` validates input
3. `BrandSlugGenerator::generate($name, $excludeId)` auto-generates slug if empty
4. `CreateBrandAction` creates the record
5. `SyncBrandMediaAction` attaches image if uploaded
6. Redirect to index with flash success

### Edit / Update
Same as create. `UpdateBrandRequest` ignores current brand's slug in uniqueness check.

### Show
Loads brand with `products_count` and media. Read-only.

### Delete
`DeleteBrandAction` checks `$brand->products()->exists()` — throws `CannotDeleteBrandException` if true. Controller catches and redirects back with flash error. Otherwise soft deletes.

### Toggle Status
`ToggleBrandStatusAction` flips `is_active`. Redirects back with flash.

---

## Domain Layer (`app/Domain/Catalog/`)

### `BrandSlugGenerator`
- Same logic as `CategorySlugGenerator` but checks `brands.slug`
- `generate(string $name, ?int $excludeId = null): string`

### `ListAdminBrandsQuery`
- `Brand::withCount('products')->with('media')->orderBy('name')->paginate(20)`
- Returns `LengthAwarePaginator`
- Each item: `id`, `name`, `slug`, `description`, `is_active`, `image_url`, `products_count`, `created_at`

### `CreateBrandAction`
- Auto-generates slug via `BrandSlugGenerator` if empty
- Creates and returns `Brand`

### `UpdateBrandAction`
- Regenerates slug only if name changed and slug empty (dirty check)
- Calls `$brand->update()`, returns `$brand->fresh()`

### `ToggleBrandStatusAction`
- Flips `is_active`, returns `$brand->fresh()`

### `SyncBrandMediaAction`
- Same logic as `SyncCategoryMediaAction` but typed for `Brand`
- `execute(Brand $brand, ?UploadedFile $image, bool $removeImage = false): void`

### `DeleteBrandAction`
- If `$brand->products()->exists()` → throw `CannotDeleteBrandException('This brand has products assigned to it. Reassign them first.')`
- Otherwise → `$brand->delete()`

### `CannotDeleteBrandException`
- Extends `RuntimeException`
- In `app/Domain/Catalog/Exceptions/`

---

## Backend

### Migrations
1. Add `deleted_at` to `brands` table
2. Change `products.brand_id` FK from `nullOnDelete` → `restrictOnDelete`

### Model — `app/Models/Brand.php`
- Add `use SoftDeletes`
- Add `registerMediaCollections()` registering `singleFile()` `images` collection

### Form Requests

**`StoreBrandRequest`** (modify existing — slug currently `required`, needs to be `nullable`):
```
name        required|string|max:255
slug        nullable|string|max:255|unique:brands,slug
description nullable|string
is_active   sometimes|boolean
image       nullable|file|mimes:jpg,jpeg,png,webp|max:2048
remove_image sometimes|boolean
```

**`UpdateBrandRequest`** (modify existing — add image fields, name should be required):
```
name        required|string|max:255
slug        nullable|string|max:255|unique:brands,slug|ignore(brand->id)
description nullable|string
is_active   sometimes|boolean
image       nullable|file|mimes:jpg,jpeg,png,webp|max:2048
remove_image sometimes|boolean
```

### Policy — `app/Policies/BrandPolicy.php`
Already complete — all methods check `manage brands`. No changes needed.

### Controller — `app/Http/Controllers/Admin/Catalog/BrandController.php`
Replace skeleton. Inject all domain actions. Methods: `index`, `create`, `store`, `show`, `edit`, `update`, `destroy`, `toggleStatus`.

### Routes — `routes/web.php`
`Route::resource('brands', BrandController::class)` already registered. Add:
```php
Route::patch('brands/{brand}/toggle-status', [BrandController::class, 'toggleStatus'])
    ->name('brands.toggle-status');
```

---

## Frontend

### Types — `resources/js/types/admin/catalog.ts`

Expand existing `AdminBrand`:
```ts
export interface AdminBrand {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    is_active: boolean;
    image_url: string | null;
    products_count: number;
    created_at: string;
}
```

### Pages

| Page | Path | Props |
|---|---|---|
| Index | `pages/admin/catalog/brands/index.tsx` | `{ brands: AdminCatalogListPage<AdminBrand> }` |
| Create | `pages/admin/catalog/brands/create.tsx` | `{}` |
| Edit | `pages/admin/catalog/brands/edit.tsx` | `{ brand: AdminBrand }` |
| Show | `pages/admin/catalog/brands/show.tsx` | `{ brand: AdminBrand }` |

`AdminCatalogListPage<T>` is already defined in `types/admin/catalog.ts` as `{ data: T[], meta?: PaginationMeta }`. Laravel's paginator also serializes `links` (the full page link array) — use `meta.links` for pagination controls.

### Components

**`_components/brand-table.tsx`**
- Props: `brands: AdminBrand[]`
- Flat table — no indentation, no tree
- Columns: Name, Slug, Status badge, Products count, Actions (View / Edit / Delete)
- Delete button: Inertia `<Form>` with `BrandController.destroy.form.delete(brand)` + `window.confirm()`

**`_components/brand-form.tsx`**
- Props: `brand?: AdminBrand`
- Same structure as `CategoryForm` — name, slug (auto/manual toggle), description textarea, is_active checkbox, image upload
- No parent select, no sort_order
- Hidden inputs for `slug` and `is_active`

**Pagination (inline in index page, not a separate component)**
- Renders `meta.links` as `<Link>` components for page navigation
- Only shown when `meta.last_page > 1`

### All pages use:
- `AdminLayout` wrapper
- `PageHeader` with title and contextual action buttons
- Flash messages via redirect

---

## Inertia Controller Props Detail

**`index()`** — passes `brands` as paginated result from `ListAdminBrandsQuery`

**`create()`** — no extra props needed

**`store()`** — validates → generate slug → create → sync media → redirect to index with flash

**`show(Brand $brand)`** — loads brand with `products_count` and media

**`edit(Brand $brand)`** — loads brand with media

**`update()`** — validates → update → sync media → redirect to index with flash

**`destroy()`** — delete → redirect to index; on `CannotDeleteBrandException` → back with flash error

**`toggleStatus()`** — toggles → redirect back with flash

---

## Out of Scope (this batch)

- Brand–product relationship view (list products belonging to a brand)
- Bulk actions
- Sorting / reordering
