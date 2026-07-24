import { Form, Link } from '@inertiajs/react';
import { Download, FileSpreadsheet, PencilLine, Upload } from 'lucide-react';
import * as ProductBulkCatalogController from '@/actions/App/Http/Controllers/Admin/Catalog/ProductBulkCatalogController';
import * as ProductController from '@/actions/App/Http/Controllers/Admin/Catalog/ProductController';
import { DataTablePagination } from '@/components/shared/data-table/data-table-pagination';
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
        (p) => p.status === 'active',
    ).length;
    const featuredProducts = products.data.filter((p) => p.is_featured).length;

    const activeFilterQuery = Object.fromEntries(
        Object.entries(filters).filter(([, v]) => v !== null),
    ) as Record<string, string>;

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
                                {products.total}
                            </p>
                            <p className="mt-0.5 text-xs text-muted-foreground">
                                Total products matching current filters.
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
                                {activeProducts}
                            </p>
                            <p className="mt-0.5 text-xs text-muted-foreground">
                                Listings already live to shoppers.
                            </p>
                        </CardContent>
                    </Card>
                    <Card className="border-border/70">
                        <CardHeader className="pt-4 pb-1">
                            <CardTitle className="text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                Featured on this page
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="pb-4">
                            <p className="text-3xl font-semibold tabular-nums">
                                {featuredProducts}
                            </p>
                            <p className="mt-0.5 text-xs text-muted-foreground">
                                Products flagged for spotlight placement.
                            </p>
                        </CardContent>
                    </Card>
                </div>

                {/* Bulk operations card */}
                <Card className="border-border/70">
                    <CardHeader className="pb-3">
                        <div className="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <div className="flex items-center gap-2 text-xs text-muted-foreground">
                                    <FileSpreadsheet className="size-3.5" />
                                    <span className="font-semibold tracking-wide uppercase">
                                        Bulk catalog operations
                                    </span>
                                </div>
                                <CardTitle className="mt-0.5 text-base">
                                    Import and export products
                                </CardTitle>
                            </div>
                            <div className="flex flex-wrap gap-2">
                                <Button variant="outline" size="sm" asChild>
                                    <Link
                                        href={ProductBulkCatalogController.edit.url(
                                            {
                                                query: activeFilterQuery,
                                            },
                                        )}
                                    >
                                        <PencilLine className="mr-1.5 size-3.5" />
                                        Bulk editor
                                    </Link>
                                </Button>
                                <Button variant="outline" size="sm" asChild>
                                    <a
                                        href={ProductBulkCatalogController.template.url()}
                                    >
                                        <Download className="mr-1.5 size-3.5" />
                                        Template
                                    </a>
                                </Button>
                                <Button variant="outline" size="sm" asChild>
                                    <a
                                        href={ProductBulkCatalogController.exportMethod.url()}
                                    >
                                        <Download className="mr-1.5 size-3.5" />
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
                                        size="sm"
                                        disabled={processing}
                                        className="md:self-end"
                                    >
                                        <Upload className="mr-1.5 size-3.5" />
                                        {processing ? 'Importing…' : 'Import'}
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
                                        {importResult.errors.length > 0 && (
                                            <div className="flex flex-col gap-1">
                                                {importResult.errors.map(
                                                    (err) => (
                                                        <p
                                                            key={`${err.row}-${err.message}`}
                                                            className="text-xs"
                                                        >
                                                            Row {err.row}:{' '}
                                                            {err.message}
                                                        </p>
                                                    ),
                                                )}
                                            </div>
                                        )}
                                    </div>
                                </AlertDescription>
                            </Alert>
                        ) : null}
                    </CardContent>
                </Card>

                {/* Filter bar */}
                <div className="flex flex-wrap items-center gap-2">
                    <Input
                        placeholder="Search name or SKU…"
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        className="h-9 w-60"
                    />
                    <Select
                        value={filters.status ?? EMPTY_SENTINEL}
                        onValueChange={(v) =>
                            setFilter('status', v === EMPTY_SENTINEL ? null : v)
                        }
                    >
                        <SelectTrigger className="h-9 w-36">
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
                        <SelectTrigger className="h-9 w-44">
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
                        <SelectTrigger className="h-9 w-36">
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
                        <SelectTrigger className="h-9 w-36">
                            <SelectValue placeholder="All tags" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value={EMPTY_SENTINEL}>
                                All tags
                            </SelectItem>
                            {tags.map((t) => (
                                <SelectItem key={t.id} value={String(t.id)}>
                                    {t.name}
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
                        <SelectTrigger className="h-9 w-44">
                            <SelectValue placeholder="All collections" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value={EMPTY_SENTINEL}>
                                All collections
                            </SelectItem>
                            {collections.map((col) => (
                                <SelectItem key={col.id} value={String(col.id)}>
                                    {col.name}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                </div>

                {/* Table */}
                <div className="overflow-hidden rounded-lg border bg-background">
                    <ProductTable products={products.data} />
                    <DataTablePagination meta={products} />
                </div>
            </div>
        </AdminLayout>
    );
}
