import { Link } from '@inertiajs/react';
import * as BrandController from '@/actions/App/Http/Controllers/Admin/Catalog/BrandController';
import { PageHeader } from '@/components/shared/page-header/page-header';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { EMPTY_SENTINEL, Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
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
    const { search, setSearch, setFilter } = useFilters(BrandController.index.url(), filters);

    const activeValue = filters.is_active === true ? '1' : filters.is_active === false ? '0' : EMPTY_SENTINEL;

    return (
        <AdminLayout title="Brands">
            <div className="mx-auto w-full max-w-6xl space-y-6">
                <PageHeader
                    title="Brands"
                    description="Manage brand logos, slugs, and storefront visibility."
                    actions={
                        <Button asChild>
                            <Link href={BrandController.create.url()}>Create brand</Link>
                        </Button>
                    }
                />
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
                        <SelectTrigger className="w-40"><SelectValue placeholder="All" /></SelectTrigger>
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
                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                />
                            ) : (
                                <span
                                    key={i}
                                    className="rounded border px-3 py-1 text-sm opacity-40"
                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                />
                            ),
                        )}
                    </div>
                )}
            </div>
        </AdminLayout>
    );
}
