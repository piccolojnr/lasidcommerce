# Admin Categories — Design Spec

**Date:** 2026-04-11
**Batch:** 5 — Catalog Management (first vertical slice)
**Status:** Approved

---

## Overview

Implement the Categories feature end-to-end as the first vertical slice of Batch 5. This covers the full CRUD cycle (no delete in this batch), a tree-view index, a shared form component with auto-slug generation and image upload, and a read-only show page.

---

## Decisions

| Decision | Choice | Reason |
|---|---|---|
| Create/Edit UI | Full separate pages | Consistent with project pattern; form complexity warrants full-page space |
| Index display | Full tree (no pagination) | Categories dataset is small; tree context is valuable |
| Slug generation | Auto-generate from name, user-overridable | Standard admin UX; backend also ensures uniqueness |
| Delete | Deferred | Deleting categories with children requires extra logic; out of scope for this batch |

---

## Architecture & Data Flow

```
Browser → Inertia → CategoryController → Domain Action/Query → Model → DB
```

### Index (tree view)
`ListAdminCategoriesQuery` loads all root categories with `children` recursively eager-loaded. Returns a **flat array with a `depth` field** (not a nested structure) — simpler to render in a table without recursive React components. No pagination.

### Create / Store
1. `create()` passes a flat list of all categories for the parent selector
2. `StoreCategoryRequest` validates input
3. `CategorySlugGenerator::generate($name, $excludeId)` ensures a unique slug if none provided
4. `CreateCategoryAction` creates the record
5. `SyncCategoryMediaAction` attaches the image if uploaded
6. Redirect to index with flash success

### Edit / Update
Same as create. `UpdateCategoryRequest` ignores the current category's slug in the uniqueness check. `UpdateCategoryAction` updates. `SyncCategoryMediaAction` replaces or removes the image.

### Show
Loads category with parent, children, and media. Read-only.

---

## Domain Layer (`app/Domain/Catalog/`)

### `CategorySlugGenerator`
- Input: `string $name`, `?int $excludeId = null`
- Generates via `Str::slug($name)`
- Checks uniqueness against `categories.slug` (excluding `$excludeId`)
- Appends incrementing suffix on collision: `shoes`, `shoes-2`, `shoes-3`

### `ListAdminCategoriesQuery`
- Loads root categories (`parent_id = null`) with `children` recursively eager-loaded (max 3 levels)
- Flattens the tree into a single array, adding a `depth` integer to each item
- Each item includes: `id`, `name`, `slug`, `is_active`, `sort_order`, `parent_id`, `parent_name`, `depth`, `image_url`, `children_count`

### `CreateCategoryAction`
- Accepts array of attributes
- Calls `CategorySlugGenerator` if `slug` is empty
- Creates and returns the `Category` model

### `UpdateCategoryAction`
- Updates an existing `Category`
- Regenerates slug only if name changed and no explicit slug provided
- Returns updated model

### `ToggleCategoryStatusAction`
- Flips `is_active` on a given `Category`
- Returns updated model

### `SyncCategoryMediaAction`
- Accepts `Category` and `?UploadedFile $image`, `bool $removeImage = false`
- If `$image` present: clears `images` collection, adds new file
- If `$removeImage = true` and no file: clears `images` collection
- Uses Spatie MediaLibrary `images` collection

---

## Backend

### Controller — `app/Http/Controllers/Admin/Catalog/CategoryController.php`
- `authorizeResource(Category::class, 'category')` in constructor
- Methods: `index`, `create`, `store`, `show`, `edit`, `update`, `toggleStatus`
- No `destroy` in this batch
- Delegates all logic to domain layer
- Returns Inertia responses or redirects with flash

### Form Requests — `app/Http/Requests/Admin/Catalog/`

**`StoreCategoryRequest`**
```
name        required|string|max:255
slug        nullable|string|max:255|unique:categories,slug
parent_id   nullable|integer|exists:categories,id
description nullable|string
is_active   boolean (default true)
image       nullable|file|mimes:jpg,jpeg,png,webp|max:2048
```

**`UpdateCategoryRequest`**
Same rules. Slug uniqueness: `Rule::unique('categories','slug')->ignore($this->route('category')->id)`.

### Policy — `app/Policies/CategoryPolicy.php`
All five methods (`viewAny`, `view`, `create`, `update`, `delete`) check `$user->can('manage categories')`.

### Routes — `routes/web.php` (inside catalog group)
```php
Route::resource('categories', CategoryController::class)->except(['destroy']);
Route::patch('categories/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])
    ->name('catalog.categories.toggle-status');
```

---

## Frontend

### Types — `resources/js/types/admin/catalog.ts`

```ts
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

### Pages

| Page | Path | Props |
|---|---|---|
| Index | `pages/admin/catalog/categories/index.tsx` | `{ categories: AdminCategory[] }` |
| Create | `pages/admin/catalog/categories/create.tsx` | `{ categories: AdminCategory[] }` |
| Edit | `pages/admin/catalog/categories/edit.tsx` | `{ category: AdminCategory, categories: AdminCategory[] }` |
| Show | `pages/admin/catalog/categories/show.tsx` | `{ category: AdminCategory & { children: AdminCategory[] } }` |

### Components

**`_components/category-table.tsx`**
- Accepts flat `AdminCategory[]` with `depth` field
- Name cell indented via `pl-{depth * 4}` with tree connector icon for non-root items
- Columns: Name (indented), Slug, Parent, Status badge, Sort order, Actions (View / Edit)
- No pagination

**`_components/category-form.tsx`**
- Props: `category?: AdminCategory`, `categories: AdminCategory[]`
- Uses Inertia `useForm`
- Name field: on change, auto-generates slug if not in manual mode
- Slug field: shows lock/unlock icon; once manually edited, auto-generation stops
- Parent select: dropdown excluding self and own descendants on edit
- Description textarea
- Is Active switch/checkbox
- Image upload: shows current thumbnail on edit, file input to replace, clear button to remove
- `FormSection` + `FormActions` shared components

### All pages use:
- `AdminLayout` wrapper
- `PageHeader` with title and contextual action buttons
- Flash success messages via redirect

---

## Inertia Controller Props Detail

**`index()`** — passes `categories` as flattened tree array from `ListAdminCategoriesQuery`

**`create()`** — passes `categories` (flat list, all active, for parent selector)

**`store()`** — validates → generate slug → create → sync media → redirect to index with flash

**`show(Category $category)`** — passes `category` with children and media loaded

**`edit(Category $category)`** — passes `category` + `categories` list

**`update()`** — validates → update → sync media → redirect to index with flash

**`toggleStatus()`** — toggles → redirect back with flash

---

## Out of Scope (this batch)

- Delete with child-category handling
- Reordering / sort_order drag-and-drop
- Bulk actions
- Category-product relationship views
