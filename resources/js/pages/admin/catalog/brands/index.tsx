import { Link } from '@inertiajs/react';
import * as BrandController from '@/actions/App/Http/Controllers/Admin/Catalog/BrandController';
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
import type { PaginationLink } from '@/types/shared/pagination';

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
    const activeBrands = brands.data.filter((brand) => brand.is_active).length;
    const brandsWithImages = brands.data.filter(
        (brand) => brand.image_url,
    ).length;

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
                <div className="grid gap-4 md:grid-cols-3">
                    <Card className="border-border/70">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                Visible in this result
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-3xl font-semibold">
                                {brands.data.length}
                            </div>
                            <p className="text-sm text-muted-foreground">
                                Brands on the current page after filters.
                            </p>
                        </CardContent>
                    </Card>
                    <Card className="border-border/70">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                Active on this page
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-3xl font-semibold">
                                {activeBrands}
                            </div>
                            <p className="text-sm text-muted-foreground">
                                Brands currently visible on the storefront.
                            </p>
                        </CardContent>
                    </Card>
                    <Card className="border-border/70">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                With imagery
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-3xl font-semibold">
                                {brandsWithImages}
                            </div>
                            <p className="text-sm text-muted-foreground">
                                Brands carrying a logo or visual identifier.
                            </p>
                        </CardContent>
                    </Card>
                </div>
                <div className="flex flex-wrap items-center gap-3">
                    <Input
                        placeholder="Search brands…"
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
                <BrandTable brands={brands.data} />
                {brands.last_page > 1 && (
                    <div className="flex items-center justify-center gap-1">
                        {brands.links.map((link: PaginationLink, i: number) =>
                            link.url ? (
                                <Link
                                    key={i}
                                    href={link.url}
                                    className={`rounded border px-3 py-1 text-sm ${
                                        link.active
                                            ? 'bg-primary text-primary-foreground'
                                            : 'hover:bg-muted'
                                    }`}
                                    dangerouslySetInnerHTML={{
                                        __html: link.label,
                                    }}
                                />
                            ) : (
                                <span
                                    key={i}
                                    className="rounded border px-3 py-1 text-sm opacity-40"
                                    dangerouslySetInnerHTML={{
                                        __html: link.label,
                                    }}
                                />
                            ),
                        )}
                    </div>
                )}
            </div>
        </AdminLayout>
    );
}
