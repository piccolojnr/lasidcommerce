import { Link } from '@inertiajs/react';
import * as TagController from '@/actions/App/Http/Controllers/Admin/Catalog/TagController';
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
import { TagTable } from '@/pages/admin/catalog/tags/_components/tag-table';
import type { AdminCatalogListPage, AdminTag } from '@/types/admin/catalog';

interface TagFilters {
    [key: string]: string | boolean | null;
    search: string | null;
    is_active: boolean | null;
}

export default function TagIndexPage({
    tags,
    filters,
}: {
    tags: AdminCatalogListPage<AdminTag>;
    filters: TagFilters;
}) {
    const { search, setSearch, setFilter } = useFilters(
        TagController.index.url(),
        filters,
    );

    const activeValue =
        filters.is_active === true
            ? '1'
            : filters.is_active === false
              ? '0'
              : EMPTY_SENTINEL;

    const activeTags = tags.data.filter((t) => t.is_active).length;
    const assignedProducts = tags.data.reduce(
        (sum, t) => sum + t.products_count,
        0,
    );

    return (
        <AdminLayout title="Tags">
            <div className="mx-auto w-full max-w-6xl space-y-6">
                <PageHeader
                    title="Tags"
                    description="Manage flexible merchandising labels without distorting categories or pricing rules."
                    actions={
                        <Button asChild>
                            <Link href={TagController.create.url()}>
                                Create tag
                            </Link>
                        </Button>
                    }
                />

                {/* Stat cards */}
                <div className="grid gap-4 md:grid-cols-3">
                    <Card className="border-border/70">
                        <CardHeader className="pt-4 pb-1">
                            <CardTitle className="text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                Visible in result
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="pb-4">
                            <p className="text-3xl font-semibold tabular-nums">
                                {tags.total}
                            </p>
                            <p className="mt-0.5 text-xs text-muted-foreground">
                                Tags matching the current filters.
                            </p>
                        </CardContent>
                    </Card>
                    <Card className="border-border/70">
                        <CardHeader className="pt-4 pb-1">
                            <CardTitle className="text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                Active on this page
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="pb-4">
                            <p className="text-3xl font-semibold tabular-nums">
                                {activeTags}
                            </p>
                            <p className="mt-0.5 text-xs text-muted-foreground">
                                Tags currently applied on the storefront.
                            </p>
                        </CardContent>
                    </Card>
                    <Card className="border-border/70">
                        <CardHeader className="pt-4 pb-1">
                            <CardTitle className="text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                Assigned products
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="pb-4">
                            <p className="text-3xl font-semibold tabular-nums">
                                {assignedProducts}
                            </p>
                            <p className="mt-0.5 text-xs text-muted-foreground">
                                Total product assignments on this page.
                            </p>
                        </CardContent>
                    </Card>
                </div>

                {/* Filter bar */}
                <div className="flex flex-wrap items-center gap-2">
                    <Input
                        placeholder="Search tags…"
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

                {/* Table + pagination */}
                <div className="overflow-hidden rounded-lg border bg-background">
                    <TagTable tags={tags.data} />
                    <DataTablePagination meta={tags} />
                </div>
            </div>
        </AdminLayout>
    );
}
