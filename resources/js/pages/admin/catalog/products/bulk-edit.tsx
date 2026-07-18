import { Link, useForm } from '@inertiajs/react';
import {
    AlertTriangle,
    ArrowLeft,
    ImageOff,
    Save,
    Sparkles,
} from 'lucide-react';
import type { FormEvent } from 'react';
import * as ProductBulkCatalogController from '@/actions/App/Http/Controllers/Admin/Catalog/ProductBulkCatalogController';
import * as ProductController from '@/actions/App/Http/Controllers/Admin/Catalog/ProductController';
import { PageHeader } from '@/components/shared/page-header/page-header';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import {
    EMPTY_SENTINEL,
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { AdminLayout } from '@/layouts/app/admin-layout';
import type { AdminBulkEditableProduct } from '@/types/admin/catalog';

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

interface BulkEditResult {
    processed: number;
    updated: number;
    failed: number;
    errors: Array<{ row: number; message: string }>;
}

interface EditableProductRow {
    id: number;
    name: string;
    sku: string;
    status: string;
    product_type: string;
    category_id: string;
    brand_id: string;
    base_price: number;
    compare_at_price: number | '';
    cost_price: number | '';
    track_inventory: boolean;
    allow_backorders: boolean;
    is_featured: boolean;
    quantity_on_hand: number | '';
    reorder_level: number | '';
}

interface Props {
    products: AdminBulkEditableProduct[];
    filters: ProductFilters;
    bulkEditResult: BulkEditResult | null;
    categories: SelectOption[];
    brands: SelectOption[];
    tags: SelectOption[];
    collections: SelectOption[];
}

function toEditableRow(product: AdminBulkEditableProduct): EditableProductRow {
    return {
        id: product.id,
        name: product.name,
        sku: product.sku,
        status: product.status,
        product_type: product.product_type,
        category_id: product.category_id ? String(product.category_id) : '',
        brand_id: product.brand_id ? String(product.brand_id) : '',
        base_price: product.base_price,
        compare_at_price: product.compare_at_price ?? '',
        cost_price: product.cost_price ?? '',
        track_inventory: product.track_inventory,
        allow_backorders: product.allow_backorders,
        is_featured: product.is_featured,
        quantity_on_hand: product.quantity_on_hand ?? '',
        reorder_level: product.reorder_level ?? '',
    };
}

export default function ProductBulkEditPage({
    products,
    filters,
    bulkEditResult,
    categories,
    brands,
}: Props) {
    const { data, setData, patch, processing, errors } = useForm<{
        products: EditableProductRow[];
    }>({
        products: products.map(toEditableRow),
    });

    const missingImages = products.filter(
        (product) => product.image_count === 0,
    ).length;
    const variantInventoryRows = products.filter(
        (product) => product.variants_count > 0,
    ).length;
    const queryString = new URLSearchParams(
        Object.entries(filters).filter(([, value]) => value !== null) as [
            string,
            string,
        ][],
    ).toString();
    const backHref = `${ProductController.index.url()}${queryString ? `?${queryString}` : ''}`;

    const updateRow = <Key extends keyof EditableProductRow>(
        index: number,
        key: Key,
        value: EditableProductRow[Key],
    ) => {
        setData(
            'products',
            data.products.map((row, rowIndex) =>
                rowIndex === index ? { ...row, [key]: value } : row,
            ),
        );
    };

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        patch(ProductBulkCatalogController.update.url(), {
            preserveScroll: true,
        });
    };

    return (
        <AdminLayout title="Bulk edit products">
            <form
                onSubmit={submit}
                className="mx-auto flex w-full max-w-7xl flex-col gap-6"
            >
                <PageHeader
                    title="Bulk edit products"
                    description="Update merchandising, pricing, publishing, and base stock from a spreadsheet-style workspace."
                    actions={
                        <div className="flex flex-wrap gap-2">
                            <Button variant="outline" asChild>
                                <Link href={backHref}>
                                    <ArrowLeft data-icon="inline-start" />
                                    Products
                                </Link>
                            </Button>
                            <Button type="submit" disabled={processing}>
                                <Save data-icon="inline-start" />
                                {processing ? 'Saving' : 'Save changes'}
                            </Button>
                        </div>
                    }
                />

                <div className="grid gap-3 md:grid-cols-3">
                    <Alert>
                        <Sparkles data-icon="inline-start" />
                        <AlertTitle>{products.length} editable rows</AlertTitle>
                        <AlertDescription>
                            The editor loads up to 100 products matching the
                            current filters.
                        </AlertDescription>
                    </Alert>
                    <Alert>
                        <ImageOff data-icon="inline-start" />
                        <AlertTitle>{missingImages} missing media</AlertTitle>
                        <AlertDescription>
                            Rows without thumbnails should be opened and given
                            storefront images.
                        </AlertDescription>
                    </Alert>
                    <Alert>
                        <AlertTriangle data-icon="inline-start" />
                        <AlertTitle>
                            {variantInventoryRows} variant stock rows
                        </AlertTitle>
                        <AlertDescription>
                            Variant inventory is shown here, but edited from the
                            product variant screen.
                        </AlertDescription>
                    </Alert>
                </div>

                {bulkEditResult ? (
                    <Alert>
                        <AlertTitle>Last save result</AlertTitle>
                        <AlertDescription>
                            <div className="flex flex-col gap-3">
                                <div className="flex flex-wrap gap-2">
                                    <Badge variant="secondary">
                                        {bulkEditResult.processed} processed
                                    </Badge>
                                    <Badge variant="secondary">
                                        {bulkEditResult.updated} updated
                                    </Badge>
                                    <Badge
                                        variant={
                                            bulkEditResult.failed > 0
                                                ? 'destructive'
                                                : 'secondary'
                                        }
                                    >
                                        {bulkEditResult.failed} failed
                                    </Badge>
                                </div>
                                {bulkEditResult.errors.length > 0 ? (
                                    <div className="flex flex-col gap-2">
                                        {bulkEditResult.errors.map((error) => (
                                            <p
                                                key={`${error.row}-${error.message}`}
                                            >
                                                Row {error.row}: {error.message}
                                            </p>
                                        ))}
                                    </div>
                                ) : null}
                            </div>
                        </AlertDescription>
                    </Alert>
                ) : null}

                {errors.products ? (
                    <Alert variant="destructive">
                        <AlertTitle>Bulk save failed</AlertTitle>
                        <AlertDescription>{errors.products}</AlertDescription>
                    </Alert>
                ) : null}

                <div className="overflow-hidden rounded-lg border bg-background">
                    <div className="overflow-x-auto">
                        <table className="min-w-[1500px] text-sm">
                            <thead className="bg-muted/40">
                                <tr>
                                    <th className="sticky left-0 z-10 w-72 bg-muted px-3 py-3 text-left font-medium text-muted-foreground">
                                        Product
                                    </th>
                                    <th className="w-40 px-3 py-3 text-left font-medium text-muted-foreground">
                                        SKU
                                    </th>
                                    <th className="w-36 px-3 py-3 text-left font-medium text-muted-foreground">
                                        Status
                                    </th>
                                    <th className="w-36 px-3 py-3 text-left font-medium text-muted-foreground">
                                        Type
                                    </th>
                                    <th className="w-44 px-3 py-3 text-left font-medium text-muted-foreground">
                                        Category
                                    </th>
                                    <th className="w-44 px-3 py-3 text-left font-medium text-muted-foreground">
                                        Brand
                                    </th>
                                    <th className="w-32 px-3 py-3 text-left font-medium text-muted-foreground">
                                        Price
                                    </th>
                                    <th className="w-32 px-3 py-3 text-left font-medium text-muted-foreground">
                                        Compare
                                    </th>
                                    <th className="w-32 px-3 py-3 text-left font-medium text-muted-foreground">
                                        Cost
                                    </th>
                                    <th className="w-32 px-3 py-3 text-left font-medium text-muted-foreground">
                                        On hand
                                    </th>
                                    <th className="w-32 px-3 py-3 text-left font-medium text-muted-foreground">
                                        Reorder
                                    </th>
                                    <th className="w-48 px-3 py-3 text-left font-medium text-muted-foreground">
                                        Flags
                                    </th>
                                    <th className="w-28 px-3 py-3 text-left font-medium text-muted-foreground">
                                        Media
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                {data.products.map((row, index) => {
                                    const product = products[index];
                                    const usesVariantInventory =
                                        product.variants_count > 0;

                                    return (
                                        <tr
                                            key={row.id}
                                            className="border-t align-top"
                                        >
                                            <td className="sticky left-0 z-10 bg-background px-3 py-3">
                                                <div className="flex items-center gap-3">
                                                    <div className="size-12 shrink-0 overflow-hidden rounded-md border bg-muted/30">
                                                        {product.primary_image_thumb_url ? (
                                                            <img
                                                                src={
                                                                    product.primary_image_thumb_url
                                                                }
                                                                alt=""
                                                                className="size-full object-cover"
                                                            />
                                                        ) : null}
                                                    </div>
                                                    <div className="flex min-w-0 flex-1 flex-col gap-2">
                                                        <Input
                                                            value={row.name}
                                                            onChange={(event) =>
                                                                updateRow(
                                                                    index,
                                                                    'name',
                                                                    event.target
                                                                        .value,
                                                                )
                                                            }
                                                        />
                                                        <Button
                                                            variant="outline"
                                                            size="sm"
                                                            asChild
                                                        >
                                                            <Link
                                                                href={ProductController.edit.url(
                                                                    row.id,
                                                                )}
                                                            >
                                                                Edit media
                                                            </Link>
                                                        </Button>
                                                    </div>
                                                </div>
                                            </td>
                                            <td className="px-3 py-3">
                                                <Input
                                                    value={row.sku}
                                                    onChange={(event) =>
                                                        updateRow(
                                                            index,
                                                            'sku',
                                                            event.target.value,
                                                        )
                                                    }
                                                />
                                            </td>
                                            <td className="px-3 py-3">
                                                <Select
                                                    value={row.status}
                                                    onValueChange={(value) =>
                                                        updateRow(
                                                            index,
                                                            'status',
                                                            value,
                                                        )
                                                    }
                                                >
                                                    <SelectTrigger>
                                                        <SelectValue />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectItem value="draft">
                                                            Draft
                                                        </SelectItem>
                                                        <SelectItem value="active">
                                                            Active
                                                        </SelectItem>
                                                    </SelectContent>
                                                </Select>
                                            </td>
                                            <td className="px-3 py-3">
                                                <Select
                                                    value={row.product_type}
                                                    onValueChange={(value) =>
                                                        updateRow(
                                                            index,
                                                            'product_type',
                                                            value,
                                                        )
                                                    }
                                                >
                                                    <SelectTrigger>
                                                        <SelectValue />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectItem value="physical">
                                                            Physical
                                                        </SelectItem>
                                                        <SelectItem value="digital">
                                                            Digital
                                                        </SelectItem>
                                                    </SelectContent>
                                                </Select>
                                            </td>
                                            <td className="px-3 py-3">
                                                <Select
                                                    value={
                                                        row.category_id ||
                                                        EMPTY_SENTINEL
                                                    }
                                                    onValueChange={(value) =>
                                                        updateRow(
                                                            index,
                                                            'category_id',
                                                            value ===
                                                                EMPTY_SENTINEL
                                                                ? ''
                                                                : value,
                                                        )
                                                    }
                                                >
                                                    <SelectTrigger>
                                                        <SelectValue />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectItem
                                                            value={
                                                                EMPTY_SENTINEL
                                                            }
                                                        >
                                                            No category
                                                        </SelectItem>
                                                        {categories.map(
                                                            (category) => (
                                                                <SelectItem
                                                                    key={
                                                                        category.id
                                                                    }
                                                                    value={String(
                                                                        category.id,
                                                                    )}
                                                                >
                                                                    {
                                                                        category.name
                                                                    }
                                                                </SelectItem>
                                                            ),
                                                        )}
                                                    </SelectContent>
                                                </Select>
                                            </td>
                                            <td className="px-3 py-3">
                                                <Select
                                                    value={
                                                        row.brand_id ||
                                                        EMPTY_SENTINEL
                                                    }
                                                    onValueChange={(value) =>
                                                        updateRow(
                                                            index,
                                                            'brand_id',
                                                            value ===
                                                                EMPTY_SENTINEL
                                                                ? ''
                                                                : value,
                                                        )
                                                    }
                                                >
                                                    <SelectTrigger>
                                                        <SelectValue />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectItem
                                                            value={
                                                                EMPTY_SENTINEL
                                                            }
                                                        >
                                                            No brand
                                                        </SelectItem>
                                                        {brands.map((brand) => (
                                                            <SelectItem
                                                                key={brand.id}
                                                                value={String(
                                                                    brand.id,
                                                                )}
                                                            >
                                                                {brand.name}
                                                            </SelectItem>
                                                        ))}
                                                    </SelectContent>
                                                </Select>
                                            </td>
                                            <td className="px-3 py-3">
                                                <Input
                                                    type="number"
                                                    min="0"
                                                    value={row.base_price}
                                                    onChange={(event) =>
                                                        updateRow(
                                                            index,
                                                            'base_price',
                                                            Number(
                                                                event.target
                                                                    .value,
                                                            ),
                                                        )
                                                    }
                                                />
                                            </td>
                                            <td className="px-3 py-3">
                                                <Input
                                                    type="number"
                                                    min="0"
                                                    value={row.compare_at_price}
                                                    onChange={(event) =>
                                                        updateRow(
                                                            index,
                                                            'compare_at_price',
                                                            event.target.value
                                                                ? Number(
                                                                      event
                                                                          .target
                                                                          .value,
                                                                  )
                                                                : '',
                                                        )
                                                    }
                                                />
                                            </td>
                                            <td className="px-3 py-3">
                                                <Input
                                                    type="number"
                                                    min="0"
                                                    value={row.cost_price}
                                                    onChange={(event) =>
                                                        updateRow(
                                                            index,
                                                            'cost_price',
                                                            event.target.value
                                                                ? Number(
                                                                      event
                                                                          .target
                                                                          .value,
                                                                  )
                                                                : '',
                                                        )
                                                    }
                                                />
                                            </td>
                                            <td className="px-3 py-3">
                                                <Input
                                                    type="number"
                                                    min="0"
                                                    value={row.quantity_on_hand}
                                                    disabled={
                                                        usesVariantInventory
                                                    }
                                                    onChange={(event) =>
                                                        updateRow(
                                                            index,
                                                            'quantity_on_hand',
                                                            event.target.value
                                                                ? Number(
                                                                      event
                                                                          .target
                                                                          .value,
                                                                  )
                                                                : '',
                                                        )
                                                    }
                                                />
                                                {usesVariantInventory ? (
                                                    <p className="mt-1 text-xs text-muted-foreground">
                                                        Variant-managed
                                                    </p>
                                                ) : null}
                                            </td>
                                            <td className="px-3 py-3">
                                                <Input
                                                    type="number"
                                                    min="0"
                                                    value={row.reorder_level}
                                                    disabled={
                                                        usesVariantInventory
                                                    }
                                                    onChange={(event) =>
                                                        updateRow(
                                                            index,
                                                            'reorder_level',
                                                            event.target.value
                                                                ? Number(
                                                                      event
                                                                          .target
                                                                          .value,
                                                                  )
                                                                : '',
                                                        )
                                                    }
                                                />
                                            </td>
                                            <td className="px-3 py-3">
                                                <div className="flex flex-col gap-3">
                                                    <label className="flex items-center gap-2">
                                                        <Checkbox
                                                            checked={
                                                                row.track_inventory
                                                            }
                                                            onCheckedChange={(
                                                                checked,
                                                            ) =>
                                                                updateRow(
                                                                    index,
                                                                    'track_inventory',
                                                                    checked ===
                                                                        true,
                                                                )
                                                            }
                                                        />
                                                        <span>Track</span>
                                                    </label>
                                                    <label className="flex items-center gap-2">
                                                        <Checkbox
                                                            checked={
                                                                row.allow_backorders
                                                            }
                                                            onCheckedChange={(
                                                                checked,
                                                            ) =>
                                                                updateRow(
                                                                    index,
                                                                    'allow_backorders',
                                                                    checked ===
                                                                        true,
                                                                )
                                                            }
                                                        />
                                                        <span>Backorder</span>
                                                    </label>
                                                    <label className="flex items-center gap-2">
                                                        <Checkbox
                                                            checked={
                                                                row.is_featured
                                                            }
                                                            onCheckedChange={(
                                                                checked,
                                                            ) =>
                                                                updateRow(
                                                                    index,
                                                                    'is_featured',
                                                                    checked ===
                                                                        true,
                                                                )
                                                            }
                                                        />
                                                        <span>Featured</span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td className="px-3 py-3">
                                                <div className="flex flex-col gap-2">
                                                    <Badge
                                                        variant={
                                                            product.image_count >
                                                            0
                                                                ? 'secondary'
                                                                : 'destructive'
                                                        }
                                                    >
                                                        {product.image_count}{' '}
                                                        image
                                                        {product.image_count ===
                                                        1
                                                            ? ''
                                                            : 's'}
                                                    </Badge>
                                                    {product.reserved_quantity >
                                                    0 ? (
                                                        <Badge variant="outline">
                                                            {
                                                                product.reserved_quantity
                                                            }{' '}
                                                            reserved
                                                        </Badge>
                                                    ) : null}
                                                </div>
                                            </td>
                                        </tr>
                                    );
                                })}
                            </tbody>
                        </table>
                    </div>
                </div>
            </form>
        </AdminLayout>
    );
}
