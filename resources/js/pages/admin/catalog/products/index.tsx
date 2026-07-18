import { Form, Link } from '@inertiajs/react';
import { Download, FileSpreadsheet, Upload } from 'lucide-react';
import * as ProductBulkCatalogController from '@/actions/App/Http/Controllers/Admin/Catalog/ProductBulkCatalogController';
import * as ProductController from '@/actions/App/Http/Controllers/Admin/Catalog/ProductController';
import { FieldError } from '@/components/shared/forms/field-error';
import { PageHeader } from '@/components/shared/page-header/page-header';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
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
    importResult: {
        processed: number;
        created: number;
        updated: number;
        skipped: number;
        failed: number;
        errors: Array<{ row: number; message: string }>;
    } | null;
    categories: SelectOption[];
    brands: SelectOption[];
    tags: SelectOption[];
    collections: SelectOption[];
}

export default function ProductIndexPage({
    products,
    filters,
    importResult,
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
                <Card className="border-border/70">
                    <CardHeader>
                        <div className="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                            <div className="flex flex-col gap-1">
                                <div className="flex items-center gap-2 text-sm text-muted-foreground">
                                    <FileSpreadsheet data-icon="inline-start" />
                                    <span>Bulk catalog operations</span>
                                </div>
                                <CardTitle>
                                    Import and export products
                                </CardTitle>
                            </div>
                            <div className="flex flex-wrap gap-2">
                                <Button variant="outline" asChild>
                                    <a
                                        href={ProductBulkCatalogController.template.url()}
                                    >
                                        <Download data-icon="inline-start" />
                                        Template
                                    </a>
                                </Button>
                                <Button variant="outline" asChild>
                                    <a
                                        href={ProductBulkCatalogController.exportMethod.url()}
                                    >
                                        <Download data-icon="inline-start" />
                                        Export CSV
                                    </a>
                                </Button>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent className="flex flex-col gap-4">
                        <Form
                            {...ProductBulkCatalogController.importMethod.form.post()}
                            encType="multipart/form-data"
                            options={{ preserveScroll: true }}
                            className="flex flex-col gap-3 rounded-lg border border-dashed border-border/70 p-4 md:flex-row md:items-end"
                        >
                            {({ errors, processing }) => (
                                <>
                                    <div className="flex min-w-0 flex-1 flex-col gap-2">
                                        <label
                                            htmlFor="catalog_csv"
                                            className="text-sm font-medium"
                                        >
                                            Upload CSV
                                        </label>
                                        <Input
                                            id="catalog_csv"
                                            name="catalog_csv"
                                            type="file"
                                            accept=".csv,text/csv,text/plain"
                                        />
                                        <FieldError
                                            message={errors.catalog_csv}
                                        />
                                    </div>
                                    <Button
                                        type="submit"
                                        disabled={processing}
                                        className="md:self-end"
                                    >
                                        <Upload data-icon="inline-start" />
                                        {processing ? 'Importing' : 'Import'}
                                    </Button>
                                </>
                            )}
                        </Form>
                        {importResult ? (
                            <Alert>
                                <AlertTitle>Last import result</AlertTitle>
                                <AlertDescription>
                                    <div className="flex flex-col gap-3">
                                        <div className="flex flex-wrap gap-2">
                                            <Badge variant="secondary">
                                                {importResult.processed}{' '}
                                                processed
                                            </Badge>
                                            <Badge variant="secondary">
                                                {importResult.created} created
                                            </Badge>
                                            <Badge variant="secondary">
                                                {importResult.updated} updated
                                            </Badge>
                                            <Badge variant="secondary">
                                                {importResult.skipped} skipped
                                            </Badge>
                                            <Badge
                                                variant={
                                                    importResult.failed > 0
                                                        ? 'destructive'
                                                        : 'secondary'
                                                }
                                            >
                                                {importResult.failed} failed
                                            </Badge>
                                        </div>
                                        {importResult.errors.length > 0 ? (
                                            <div className="flex flex-col gap-2">
                                                {importResult.errors.map(
                                                    (error) => (
                                                        <p
                                                            key={`${error.row}-${error.message}`}
                                                        >
                                                            Row {error.row}:{' '}
                                                            {error.message}
                                                        </p>
                                                    ),
                                                )}
                                            </div>
                                        ) : null}
                                    </div>
                                </AlertDescription>
                            </Alert>
                        ) : null}
                    </CardContent>
                </Card>
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
