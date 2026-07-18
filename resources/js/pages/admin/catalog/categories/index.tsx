import { Link } from '@inertiajs/react';
import * as CategoryController from '@/actions/App/Http/Controllers/Admin/Catalog/CategoryController';
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
        (category) => category.parent_id === null,
    ).length;
    const activeCategories = categories.filter(
        (category) => category.is_active,
    ).length;

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
                <div className="grid gap-4 md:grid-cols-3">
                    <Card className="border-border/70">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                Total categories
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-3xl font-semibold">
                                {categories.length}
                            </div>
                            <p className="text-sm text-muted-foreground">
                                All categories in the current hierarchy view.
                            </p>
                        </CardContent>
                    </Card>
                    <Card className="border-border/70">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                Root branches
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-3xl font-semibold">
                                {rootCategories}
                            </div>
                            <p className="text-sm text-muted-foreground">
                                Top-level entry points for the catalog tree.
                            </p>
                        </CardContent>
                    </Card>
                    <Card className="border-border/70">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                Active categories
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-3xl font-semibold">
                                {activeCategories}
                            </div>
                            <p className="text-sm text-muted-foreground">
                                Branches currently visible to shoppers.
                            </p>
                        </CardContent>
                    </Card>
                </div>
                <div className="flex flex-wrap items-center gap-3">
                    <Input
                        placeholder="Search categories…"
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        className="w-64"
                    />
                    <Select
                        value={activeValue}
                        onValueChange={(v) => {
                            if (v === EMPTY_SENTINEL) {
                                setFilter('is_active', null);
                            } else {
                                setFilter('is_active', v);
                            }
                        }}
                    >
                        <SelectTrigger className="w-40">
                            <SelectValue placeholder="All" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value={EMPTY_SENTINEL}>All</SelectItem>
                            <SelectItem value="1">Active</SelectItem>
                            <SelectItem value="0">Inactive</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <CategoryTable categories={categories} />
            </div>
        </AdminLayout>
    );
}
