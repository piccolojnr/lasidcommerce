import { Link } from '@inertiajs/react';
import * as BrandController from '@/actions/App/Http/Controllers/Admin/Catalog/BrandController';
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
import { BrandTable } from '@/pages/admin/catalog/brands/_components/brand-table';
import type { AdminBrand, AdminCatalogListPage } from '@/types/admin/catalog';

interface BrandFilters {
    [key: string]: string | boolean | null;
    search: string | null;
    is_active: boolean | null;
}

interface Props {
    brands: AdminCatalogListPage<AdminBrand>;
    filters: BrandFilters;
}

export default function BrandIndexPage({ brands, filters }: Props) {
    const { search, setSearch, setFilter } = useFilters(
        BrandController.index.url(),
        filters,
    );

    const activeValue =
        filters.is_active === true
            ? '1'
            : filters.is_active === false
              ? '0'
              : EMPTY_SENTINEL;

    const activeBrands = brands.data.filter((b) => b.is_active).length;
    const brandsWithImages = brands.data.filter((b) => b.image_url).length;

    return (
        <AdminLayout title="Brands">
            <div className="mx-auto w-full max-w-6xl space-y-6">
                <PageHeader
                    title="Brands"
                    description="Keep the brand layer of the catalog consistent, visible, and visually credible."
                    actions={
                        <Button asChild>
                            <Link href={BrandController.create.url()}>
                                Create brand
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
                                {brands.total}
                            </p>
                            <p className="mt-0.5 text-xs text-muted-foreground">
                                Brands matching the current filters.
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
                                {activeBrands}
                            </p>
                            <p className="mt-0.5 text-xs text-muted-foreground">
                                Brands currently visible on the storefront.
                            </p>
                        </CardContent>
                    </Card>
                    <Card className="border-border/70">
                        <CardHeader className="pt-4 pb-1">
                            <CardTitle className="text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                With imagery
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="pb-4">
                            <p className="text-3xl font-semibold tabular-nums">
                                {brandsWithImages}
                            </p>
                            <p className="mt-0.5 text-xs text-muted-foreground">
                                Brands carrying a logo or visual identifier.
                            </p>
                        </CardContent>
                    </Card>
                </div>

                {/* Filter bar */}
                <div className="flex flex-wrap items-center gap-2">
                    <Input
                        placeholder="Search brands…"
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
                    <BrandTable brands={brands.data} />
                    <DataTablePagination meta={brands} />
                </div>
            </div>
        </AdminLayout>
    );
}
