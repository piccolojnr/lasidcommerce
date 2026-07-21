import { Link } from '@inertiajs/react';
import * as CategoryController from '@/actions/App/Http/Controllers/Admin/Catalog/CategoryController';
import { DataTablePagination } from '@/components/shared/data-table/data-table-pagination';
import { PageHeader } from '@/components/shared/page-header/page-header';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import {
    EMPTY_SENTINEL,
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useFilters } from '@/hooks/use-filters';
import { AdminLayout } from '@/layouts/app/admin-layout';
import { CategoryTable } from '@/pages/admin/catalog/categories/_components/category-table';
import type { AdminCategory } from '@/types/admin/catalog';

interface CategoryFilters {
    [key: string]: string | boolean | null;
    search: string | null;
    is_active: boolean | null;
}

interface Props {
    categories: AdminCategory[];
    filters: CategoryFilters;
}

export default function CategoryIndexPage({ categories, filters }: Props) {
    const { search, setSearch, setFilter } = useFilters(
        CategoryController.index.url(),
        filters,
    );

    const activeValue =
        filters.is_active === true
            ? '1'
            : filters.is_active === false
              ? '0'
              : EMPTY_SENTINEL;

    const rootCategories = categories.filter(
        (c) => c.parent_id === null,
    ).length;
    const activeCategories = categories.filter((c) => c.is_active).length;

    return (
        <AdminLayout title="Categories">
            <div className="mx-auto w-full max-w-6xl space-y-6">
                <PageHeader
                    title="Categories"
                    description="Shape the catalog hierarchy, storefront visibility, and parent-child structure."
                    actions={
                        <Button asChild>
                            <Link href={CategoryController.create.url()}>
                                Create category
                            </Link>
                        </Button>
                    }
                />

                {/* Stat cards */}
                <div className="grid gap-4 md:grid-cols-3">
                    <Card className="border-border/70">
                        <CardHeader className="pt-4 pb-1">
                            <CardTitle className="text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                Total categories
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="pb-4">
                            <p className="text-3xl font-semibold tabular-nums">
                                {categories.length}
                            </p>
                            <p className="mt-0.5 text-xs text-muted-foreground">
                                All categories in the current hierarchy view.
                            </p>
                        </CardContent>
                    </Card>
                    <Card className="border-border/70">
                        <CardHeader className="pt-4 pb-1">
                            <CardTitle className="text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                Root branches
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="pb-4">
                            <p className="text-3xl font-semibold tabular-nums">
                                {rootCategories}
                            </p>
                            <p className="mt-0.5 text-xs text-muted-foreground">
                                Top-level entry points for the catalog tree.
                            </p>
                        </CardContent>
                    </Card>
                    <Card className="border-border/70">
                        <CardHeader className="pt-4 pb-1">
                            <CardTitle className="text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                Active categories
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="pb-4">
                            <p className="text-3xl font-semibold tabular-nums">
                                {activeCategories}
                            </p>
                            <p className="mt-0.5 text-xs text-muted-foreground">
                                Branches currently visible to shoppers.
                            </p>
                        </CardContent>
                    </Card>
                </div>

                {/* Filter bar */}
                <div className="flex flex-wrap items-center gap-2">
                    <Input
                        placeholder="Search categories…"
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        className="h-9 w-60"
                    />
                    <Select
                        value={activeValue}
                        onValueChange={(v) =>
                            setFilter(
                                'is_active',
                                v === EMPTY_SENTINEL ? null : v,
                            )
                        }
                    >
                        <SelectTrigger className="h-9 w-36">
                            <SelectValue placeholder="All" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value={EMPTY_SENTINEL}>All</SelectItem>
                            <SelectItem value="1">Active</SelectItem>
                            <SelectItem value="0">Inactive</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                {/* Table — categories are not paginated (flat list returned whole) */}
                <CategoryTable categories={categories} />
            </div>
        </AdminLayout>
    );
}
