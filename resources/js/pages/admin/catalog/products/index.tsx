import { Link } from '@inertiajs/react';
import * as ProductController from '@/actions/App/Http/Controllers/Admin/Catalog/ProductController';
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
import { ProductTable } from '@/pages/admin/catalog/products/_components/product-table';
import type { AdminCatalogListPage, AdminProduct } from '@/types/admin/catalog';
import type { PaginationLink } from '@/types/shared/pagination';

interface SelectOption {
    id: number;
    name: string;
}

interface ProductFilters {
    [key: string]: string | null;
    search: string | null;
    status: string | null;
    category_id: string | null;
    brand_id: string | null;
    tag_id: string | null;
    collection_id: string | null;
}

interface Props {
    products: AdminCatalogListPage<AdminProduct>;
    filters: ProductFilters;
    categories: SelectOption[];
    brands: SelectOption[];
    tags: SelectOption[];
    collections: SelectOption[];
}

export default function ProductIndexPage({
    products,
    filters,
    categories,
    brands,
    tags,
    collections,
}: Props) {
    const { search, setSearch, setFilter } = useFilters(
        ProductController.index.url(),
        filters,
    );
    const activeProducts = products.data.filter(
        (product) => product.status === 'active',
    ).length;
    const featuredProducts = products.data.filter(
        (product) => product.is_featured,
    ).length;

    return (
        <AdminLayout title="Products">
            <div className="mx-auto w-full max-w-6xl space-y-6">
                <PageHeader
                    title="Products"
                    description="Manage the active catalog mix, pricing posture, and publishing pipeline."
                    actions={
                        <Button asChild>
                            <Link href={ProductController.create.url()}>
                                Create product
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
                                {products.data.length}
                            </div>
                            <p className="text-sm text-muted-foreground">
                                Products on the current page after filters.
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
                                {activeProducts}
                            </div>
                            <p className="text-sm text-muted-foreground">
                                Listings already live to shoppers.
                            </p>
                        </CardContent>
                    </Card>
                    <Card className="border-border/70">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                Featured in this slice
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-3xl font-semibold">
                                {featuredProducts}
                            </div>
                            <p className="text-sm text-muted-foreground">
                                Products currently flagged for spotlight
                                placement.
                            </p>
                        </CardContent>
                    </Card>
                </div>
                <div className="flex flex-wrap items-center gap-3">
                    <Input
                        placeholder="Search name or SKU…"
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        className="w-64"
                    />
                    <Select
                        value={filters.status ?? EMPTY_SENTINEL}
                        onValueChange={(v) =>
                            setFilter('status', v === EMPTY_SENTINEL ? null : v)
                        }
                    >
                        <SelectTrigger className="w-40">
                            <SelectValue placeholder="All statuses" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value={EMPTY_SENTINEL}>
                                All statuses
                            </SelectItem>
                            <SelectItem value="draft">Draft</SelectItem>
                            <SelectItem value="active">Active</SelectItem>
                        </SelectContent>
                    </Select>
                    <Select
                        value={filters.category_id ?? EMPTY_SENTINEL}
                        onValueChange={(v) =>
                            setFilter(
                                'category_id',
                                v === EMPTY_SENTINEL ? null : v,
                            )
                        }
                    >
                        <SelectTrigger className="w-48">
                            <SelectValue placeholder="All categories" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value={EMPTY_SENTINEL}>
                                All categories
                            </SelectItem>
                            {categories.map((c) => (
                                <SelectItem key={c.id} value={String(c.id)}>
                                    {c.name}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                    <Select
                        value={filters.brand_id ?? EMPTY_SENTINEL}
                        onValueChange={(v) =>
                            setFilter(
                                'brand_id',
                                v === EMPTY_SENTINEL ? null : v,
                            )
                        }
                    >
                        <SelectTrigger className="w-40">
                            <SelectValue placeholder="All brands" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value={EMPTY_SENTINEL}>
                                All brands
                            </SelectItem>
                            {brands.map((b) => (
                                <SelectItem key={b.id} value={String(b.id)}>
                                    {b.name}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                    <Select
                        value={filters.tag_id ?? EMPTY_SENTINEL}
                        onValueChange={(v) =>
                            setFilter('tag_id', v === EMPTY_SENTINEL ? null : v)
                        }
                    >
                        <SelectTrigger className="w-40">
                            <SelectValue placeholder="All tags" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value={EMPTY_SENTINEL}>
                                All tags
                            </SelectItem>
                            {tags.map((tag) => (
                                <SelectItem key={tag.id} value={String(tag.id)}>
                                    {tag.name}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                    <Select
                        value={filters.collection_id ?? EMPTY_SENTINEL}
                        onValueChange={(v) =>
                            setFilter(
                                'collection_id',
                                v === EMPTY_SENTINEL ? null : v,
                            )
                        }
                    >
                        <SelectTrigger className="w-48">
                            <SelectValue placeholder="All collections" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value={EMPTY_SENTINEL}>
                                All collections
                            </SelectItem>
                            {collections.map((collection) => (
                                <SelectItem
                                    key={collection.id}
                                    value={String(collection.id)}
                                >
                                    {collection.name}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                </div>
                <ProductTable products={products.data} />
                {products.last_page > 1 && (
                    <div className="flex items-center justify-center gap-1">
                        {products.links.map(
                            (link: PaginationLink, i: number) =>
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
