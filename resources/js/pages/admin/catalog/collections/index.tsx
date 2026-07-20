import { Link } from '@inertiajs/react';
import * as CollectionController from '@/actions/App/Http/Controllers/Admin/Catalog/CollectionController';
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
import { CollectionTable } from '@/pages/admin/catalog/collections/_components/collection-table';
import type { AdminCatalogListPage, AdminCollection } from '@/types/admin/catalog';

interface CollectionFilters {
    [key: string]: string | boolean | null;
    search: string | null;
    is_active: boolean | null;
}

export default function CollectionIndexPage({
    collections,
    filters,
}: {
    collections: AdminCatalogListPage<AdminCollection>;
    filters: CollectionFilters;
}) {
    const { search, setSearch, setFilter } = useFilters(
        CollectionController.index.url(),
        filters,
    );

    const activeValue =
        filters.is_active === true ? '1' : filters.is_active === false ? '0' : EMPTY_SENTINEL;

    const activeCollections = collections.data.filter((c) => c.is_active).length;
    const assignedProducts = collections.data.reduce((sum, c) => sum + c.products_count, 0);

    return (
        <AdminLayout title="Collections">
            <div className="mx-auto w-full max-w-6xl space-y-6">
                <PageHeader
                    title="Collections"
                    description="Curate reusable storefront rails with explicit product membership and ordering."
                    actions={
                        <Button asChild>
                            <Link href={CollectionController.create.url()}>
                                Create collection
                            </Link>
                        </Button>
                    }
                />

                {/* Stat cards */}
                <div className="grid gap-4 md:grid-cols-3">
                    <Card className="border-border/70">
                        <CardHeader className="pb-1 pt-4">
                            <CardTitle className="text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                Visible in result
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="pb-4">
                            <p className="text-3xl font-semibold tabular-nums">{collections.total}</p>
                            <p className="mt-0.5 text-xs text-muted-foreground">
                                Collections matching the current filters.
                            </p>
                        </CardContent>
                    </Card>
                    <Card className="border-border/70">
                        <CardHeader className="pb-1 pt-4">
                            <CardTitle className="text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                Active on this page
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="pb-4">
                            <p className="text-3xl font-semibold tabular-nums">{activeCollections}</p>
                            <p className="mt-0.5 text-xs text-muted-foreground">
                                Rails live on the storefront.
                            </p>
                        </CardContent>
                    </Card>
                    <Card className="border-border/70">
                        <CardHeader className="pb-1 pt-4">
                            <CardTitle className="text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                Assigned products
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="pb-4">
                            <p className="text-3xl font-semibold tabular-nums">{assignedProducts}</p>
                            <p className="mt-0.5 text-xs text-muted-foreground">
                                Total product slots across this page.
                            </p>
                        </CardContent>
                    </Card>
                </div>

                {/* Filter bar */}
                <div className="flex flex-wrap items-center gap-2">
                    <Input
                        placeholder="Search collections…"
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        className="h-9 w-60"
                    />
                    <Select
                        value={activeValue}
                        onValueChange={(v) =>
                            setFilter('is_active', v === EMPTY_SENTINEL ? null : v)
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

                {/* Table + pagination */}
                <div className="overflow-hidden rounded-lg border bg-background">
                    <CollectionTable collections={collections.data} />
                    <DataTablePagination meta={collections} />
                </div>
            </div>
        </AdminLayout>
    );
}
