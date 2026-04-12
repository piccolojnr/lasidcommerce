<?php

namespace App\Domain\Catalog\Queries;

use App\Models\Category;
use Illuminate\Support\Collection;

class ListAdminCategoriesQuery
{
    private ?string $search = null;
    private ?bool $isActive = null;

    public function withFilters(array $filters): static
    {
        $clone = clone $this;
        $clone->search   = $filters['search'] ?? null;
        $clone->isActive = isset($filters['is_active']) ? (bool) $filters['is_active'] : null;
        return $clone;
    }

    public function get(): array
    {
        $roots = Category::with($this->childrenRelation())
            ->with('media')
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $result = $this->flatten($roots, 0, null);

        if ($this->search !== null && $this->search !== '') {
            $lower  = strtolower($this->search);
            $result = array_values(array_filter($result, fn ($c) => str_contains(strtolower($c['name']), $lower)));
        }

        if ($this->isActive !== null) {
            $result = array_values(array_filter($result, fn ($c) => $c['is_active'] === $this->isActive));
        }

        return $result;
    }

    private function childrenRelation(): array
    {
        return [
            'children' => function ($query) {
                $query->orderBy('sort_order')->orderBy('name')
                    ->with('media')
                    ->with([
                        'children' => function ($q) {
                            $q->orderBy('sort_order')->orderBy('name')
                                ->with('media')
                                ->with([
                                    'children' => function ($q2) {
                                        $q2->orderBy('sort_order')->orderBy('name')
                                            ->with('media');
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
