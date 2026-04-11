# Admin Categories — Soft Delete Design Spec

**Date:** 2026-04-11
**Batch:** 5 addendum — Catalog Management
**Status:** Approved

---

## Overview

Add soft delete to the Admin Categories feature. Deletion is blocked if the category has children or has products assigned. Deleted rows are retained in the database with a `deleted_at` timestamp but there is no restore UI.

---

## Decisions

| Decision | Choice | Reason |
|---|---|---|
| Delete strategy | Soft delete (`SoftDeletes` trait) | Consistent with User and Product models; preserves referential history |
| Children blocking | Block deletion if children exist | Avoids orphaned subtrees; user must reassign or delete children first |
| Products blocking | Block deletion if products exist | Avoids orphaned products; user must reassign them first |
| Restore UI | None | Out of scope; rows persist in DB for data integrity only |
| Blocking enforcement | Domain action throws exception | Consistent with existing pattern; business logic stays in domain layer |
| Confirmation UX | Native `confirm()` dialog | No shared confirmation dialog component exists yet; keeps it simple |

---

## Architecture & Data Flow

```
Browser → DELETE /admin/catalog/categories/{category}
       → CategoryController::destroy()
       → CategoryPolicy::delete()  [authorize]
       → DeleteCategoryAction::execute()
           → children exist? → throw CannotDeleteCategoryException
           → products exist? → throw CannotDeleteCategoryException
           → $category->delete()  [soft delete]
       → redirect to index with flash
       → on exception: redirect back with flash error
```

---

## Domain Layer (`app/Domain/Catalog/`)

### `Exceptions/CannotDeleteCategoryException.php`
- Extends `\RuntimeException`
- Message describes the blocking reason (children or products)

### `Actions/DeleteCategoryAction.php`
- `execute(Category $category): void`
- If `$category->children()->exists()` → throw `CannotDeleteCategoryException('This category has subcategories. Reassign or delete them first.')`
- If `$category->products()->exists()` → throw `CannotDeleteCategoryException('This category has products assigned to it. Reassign them first.')`
- Otherwise → `$category->delete()`

---

## Backend

### Migration
- Add `$table->softDeletes()` to the `categories` table

### Model — `app/Models/Category.php`
- Add `use SoftDeletes`
- No other changes; Eloquent automatically excludes soft-deleted rows from all queries

### Policy — `app/Policies/CategoryPolicy.php`
- `delete()` already checks `manage categories` — no change needed

### Controller — `app/Http/Controllers/Admin/Catalog/CategoryController.php`
- Add `DeleteCategoryAction` injection to constructor
- Add `destroy(Category $category)` method:
  ```php
  public function destroy(Category $category): RedirectResponse
  {
      $this->authorize('delete', $category);

      try {
          $this->deleteCategoryAction->execute($category);
      } catch (CannotDeleteCategoryException $e) {
          return redirect()->back()->with('error', $e->getMessage());
      }

      return redirect()->route('catalog.categories.index')
          ->with('success', 'Category deleted.');
  }
  ```

### Routes — `routes/web.php`
- Remove `destroy` from the `->except([])` list (currently `->except(['destroy'])`)
- The `toggleStatus` PATCH route remains unchanged

---

## Frontend

### `category-table.tsx`
- Add a Delete button in the Actions column, after View and Edit
- Rendered as an Inertia `<Form>` with `method="delete"` targeting the wayfinder `destroy` route
- The form's submit button triggers `window.confirm('Are you sure you want to delete this category?')` via `onSubmit`; cancels submission if user declines
- If the flash `error` key is set (blocked delete), the existing flash message system displays it

---

## Out of Scope

- Restore / trash view
- Bulk delete
- Cascade delete of children
- Reassignment UI for products or children before delete

---

## Testing

### Unit
- `DeleteCategoryAction`: throws on children, throws on products, soft deletes when clear

### Feature
- Authenticated admin can soft delete a category with no children and no products
- Delete is blocked when children exist (redirects back with error flash)
- Delete is blocked when products exist (redirects back with error flash)
- Guest is redirected from the destroy route
- Soft-deleted category is excluded from the index
