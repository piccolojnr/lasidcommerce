import { router } from '@inertiajs/react';
import { Link } from '@inertiajs/react';
import { Eye, Pencil, Trash2 } from 'lucide-react';
import * as ProductController from '@/actions/App/Http/Controllers/Admin/Catalog/ProductController';
import * as StockItemController from '@/actions/App/Http/Controllers/Admin/Inventory/StockItemController';
import { useConfirmDialog } from '@/components/shared/confirm-dialog/confirm-dialog';
import { StatusBadge } from '@/components/shared/status-badge/status-badge';
import { Button } from '@/components/ui/button';
import { formatMoney } from '@/lib/formatters/money';
import type { AdminProduct } from '@/types/admin/catalog';

interface ProductTableProps {
    products: AdminProduct[];
}

export function ProductTable({ products }: ProductTableProps) {
    const { confirmDialog, requestConfirm } = useConfirmDialog();

    if (products.length === 0) {
        return (
            <div className="rounded-lg border bg-background px-6 py-14 text-center">
                <p className="text-sm text-muted-foreground">
                    No products match the current filters.
                </p>
            </div>
        );
    }

    function handleDelete(product: AdminProduct) {
        requestConfirm({
            title: `Delete "${product.name}"?`,
            description:
                'This will permanently remove the product and all its variants, images, and inventory records. This action cannot be undone.',
            confirmLabel: 'Delete product',
            destructive: true,
            onConfirm: () =>
                router.delete(ProductController.destroy.url(product), {
                    preserveScroll: true,
                }),
        });
    }

    return (
        <>
            {confirmDialog}
            <div className="overflow-hidden rounded-lg border bg-background">
                <div className="overflow-x-auto">
                    <table className="min-w-full text-sm">
                        <thead>
                            <tr className="border-b bg-muted/40">
                                <th className="px-4 py-3 text-left text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                    Product
                                </th>
                                <th className="px-4 py-3 text-left text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                    SKU
                                </th>
                                <th className="px-4 py-3 text-left text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                    Status
                                </th>
                                <th className="px-4 py-3 text-left text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                    Price
                                </th>
                                <th className="px-4 py-3 text-left text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                    Inventory
                                </th>
                                <th className="px-4 py-3 text-left text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                    Category
                                </th>
                                <th className="px-3 py-3 text-right text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody className="divide-y">
                            {products.map((product) => (
                                <tr
                                    key={product.id}
                                    className="group transition-colors hover:bg-muted/30"
                                >
                                    {/* Product */}
                                    <td className="px-4 py-3">
                                        <div className="flex items-center gap-3">
                                            <div className="h-12 w-12 shrink-0 overflow-hidden rounded-md border bg-muted/30">
                                                {product.images[0] ? (
                                                    <img
                                                        src={
                                                            product.images[0]
                                                                .thumb_url
                                                        }
                                                        alt=""
                                                        className="h-full w-full object-cover"
                                                    />
                                                ) : (
                                                    <div className="h-full w-full" />
                                                )}
                                            </div>
                                            <div className="min-w-0">
                                                <Link
                                                    href={ProductController.show.url(
                                                        product,
                                                    )}
                                                    className="block max-w-md truncate leading-snug font-medium hover:underline"
                                                >
                                                    {product.name}
                                                </Link>
                                                <p className="mt-0.5 text-xs text-muted-foreground">
                                                    {product.brand_name ??
                                                        'No brand'}
                                                    {product.variants_count > 0
                                                        ? ` · ${product.variants_count} variant${product.variants_count === 1 ? '' : 's'}`
                                                        : ''}
                                                </p>
                                                {product.badges.length > 0 && (
                                                    <div className="mt-1 flex flex-wrap gap-1">
                                                        {product.badges.map(
                                                            (b) => (
                                                                <span
                                                                    key={b.key}
                                                                    className="rounded-full bg-primary/10 px-2 py-0.5 text-[11px] font-medium text-primary"
                                                                >
                                                                    {b.label}
                                                                </span>
                                                            ),
                                                        )}
                                                        {product.tags
                                                            .slice(0, 2)
                                                            .map((t) => (
                                                                <span
                                                                    key={t.id}
                                                                    className="rounded-full bg-muted px-2 py-0.5 text-[11px] text-muted-foreground"
                                                                >
                                                                    {t.name}
                                                                </span>
                                                            ))}
                                                    </div>
                                                )}
                                            </div>
                                        </div>
                                    </td>

                                    {/* SKU */}
                                    <td className="px-4 py-3 align-middle font-mono text-xs text-muted-foreground">
                                        {product.sku}
                                    </td>

                                    {/* Status */}
                                    <td className="px-4 py-3 align-middle">
                                        <StatusBadge status={product.status} />
                                    </td>

                                    {/* Price */}
                                    <td className="px-4 py-3 align-middle">
                                        <span className="font-medium tabular-nums">
                                            {formatMoney(product.base_price)}
                                        </span>
                                        {product.compare_at_price != null && (
                                            <p className="text-xs text-muted-foreground tabular-nums line-through">
                                                {formatMoney(
                                                    product.compare_at_price,
                                                )}
                                            </p>
                                        )}
                                    </td>

                                    {/* Inventory */}
                                    <td className="px-4 py-3 align-middle">
                                        <div className="flex items-center gap-1.5">
                                            <StatusBadge
                                                status={
                                                    product.inventory.status
                                                }
                                            />
                                            <span className="text-xs text-muted-foreground">
                                                {product.track_inventory
                                                    ? `${product.inventory.available_quantity} avail.`
                                                    : 'Untracked'}
                                            </span>
                                        </div>
                                        {product.track_inventory && (
                                            <Link
                                                href={
                                                    product.inventory
                                                        .primary_stock_item_id
                                                        ? StockItemController.show.url(
                                                              product.inventory
                                                                  .primary_stock_item_id,
                                                          )
                                                        : `${StockItemController.index.url()}?search=${encodeURIComponent(product.sku)}`
                                                }
                                                className="mt-0.5 text-xs font-medium text-primary transition hover:text-primary/80"
                                            >
                                                Open inventory
                                            </Link>
                                        )}
                                    </td>

                                    {/* Category */}
                                    <td className="px-4 py-3 align-middle text-sm text-muted-foreground">
                                        {product.category_name ?? '—'}
                                    </td>

                                    {/* Actions */}
                                    <td className="px-3 py-3 align-middle">
                                        <div className="flex items-center justify-end gap-1">
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                className="size-8"
                                                asChild
                                                title="View"
                                            >
                                                <Link
                                                    href={ProductController.show.url(
                                                        product,
                                                    )}
                                                >
                                                    <Eye className="size-4" />
                                                    <span className="sr-only">
                                                        View
                                                    </span>
                                                </Link>
                                            </Button>
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                className="size-8"
                                                asChild
                                                title="Edit"
                                            >
                                                <Link
                                                    href={ProductController.edit.url(
                                                        product,
                                                    )}
                                                >
                                                    <Pencil className="size-4" />
                                                    <span className="sr-only">
                                                        Edit
                                                    </span>
                                                </Link>
                                            </Button>
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                className="size-8 text-destructive hover:bg-destructive/10 hover:text-destructive"
                                                title="Delete"
                                                onClick={() =>
                                                    handleDelete(product)
                                                }
                                            >
                                                <Trash2 className="size-4" />
                                                <span className="sr-only">
                                                    Delete
                                                </span>
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
        </>
    );
}
