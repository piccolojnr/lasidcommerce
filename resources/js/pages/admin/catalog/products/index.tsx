import { Link } from '@inertiajs/react';
import * as ProductController from '@/actions/App/Http/Controllers/Admin/Catalog/ProductController';
import { PageHeader } from '@/components/shared/page-header/page-header';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { EMPTY_SENTINEL, Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { useFilters } from '@/hooks/use-filters';
import { AdminLayout } from '@/layouts/app/admin-layout';
import { ProductTable } from '@/pages/admin/catalog/products/_components/product-table';
import type { AdminCatalogListPage, AdminProduct } from '@/types/admin/catalog';
import type { PaginationLink } from '@/types/shared/pagination';

interface SelectOption { id: number; name: string; }

interface ProductFilters {
    [key: string]: string | null;
    search: string | null;
    status: string | null;
    category_id: string | null;
    brand_id: string | null;
}

interface Props {
    products: AdminCatalogListPage<AdminProduct>;
    filters: ProductFilters;
    categories: SelectOption[];
    brands: SelectOption[];
}

export default function ProductIndexPage({ products, filters, categories, brands }: Props) {
    const { search, setSearch, setFilter } = useFilters(ProductController.index.url(), filters);

    return (
        <AdminLayout title="Products">
            <div className="mx-auto w-full max-w-6xl space-y-6">
                <PageHeader
                    title="Products"
                    description="Manage product catalog entries and publishing state."
                    actions={
                        <Button asChild>
                            <Link href={ProductController.create.url()}>Create product</Link>
                        </Button>
                    }
                />
                <div className="flex flex-wrap items-center gap-3">
                    <Input
                        placeholder="Search name or SKU…"
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        className="w-64"
                    />
                    <Select
                        value={filters.status ?? EMPTY_SENTINEL}
                        onValueChange={(v) => setFilter('status', v === EMPTY_SENTINEL ? null : v)}
                    >
                        <SelectTrigger className="w-40"><SelectValue placeholder="All statuses" /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value={EMPTY_SENTINEL}>All statuses</SelectItem>
                            <SelectItem value="draft">Draft</SelectItem>
                            <SelectItem value="active">Active</SelectItem>
                        </SelectContent>
                    </Select>
                    <Select
                        value={filters.category_id ?? EMPTY_SENTINEL}
                        onValueChange={(v) => setFilter('category_id', v === EMPTY_SENTINEL ? null : v)}
                    >
                        <SelectTrigger className="w-48"><SelectValue placeholder="All categories" /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value={EMPTY_SENTINEL}>All categories</SelectItem>
                            {categories.map((c) => (
                                <SelectItem key={c.id} value={String(c.id)}>{c.name}</SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                    <Select
                        value={filters.brand_id ?? EMPTY_SENTINEL}
                        onValueChange={(v) => setFilter('brand_id', v === EMPTY_SENTINEL ? null : v)}
                    >
                        <SelectTrigger className="w-40"><SelectValue placeholder="All brands" /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value={EMPTY_SENTINEL}>All brands</SelectItem>
                            {brands.map((b) => (
                                <SelectItem key={b.id} value={String(b.id)}>{b.name}</SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                </div>
                <ProductTable products={products.data} />
                {products.last_page > 1 && (
                    <div className="flex items-center justify-center gap-1">
                        {products.links.map((link: PaginationLink, i: number) =>
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
