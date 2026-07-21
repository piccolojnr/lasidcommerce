import { router } from '@inertiajs/react';
import { Link } from '@inertiajs/react';
import { Eye, Pencil, Trash2 } from 'lucide-react';
import * as BrandController from '@/actions/App/Http/Controllers/Admin/Catalog/BrandController';
import { useConfirmDialog } from '@/components/shared/confirm-dialog/confirm-dialog';
import { StatusBadge } from '@/components/shared/status-badge/status-badge';
import { Button } from '@/components/ui/button';
import type { AdminBrand } from '@/types/admin/catalog';

interface BrandTableProps {
    brands: AdminBrand[];
}

export function BrandTable({ brands }: BrandTableProps) {
    const { confirmDialog, requestConfirm } = useConfirmDialog();

    if (brands.length === 0) {
        return (
            <div className="rounded-lg border bg-background px-6 py-14 text-center">
                <p className="text-sm text-muted-foreground">
                    No brands yet. Create one to get started.
                </p>
            </div>
        );
    }

    function handleDelete(brand: AdminBrand) {
        requestConfirm({
            title: `Delete "${brand.name}"?`,
            description:
                brand.products_count > 0
                    ? `This brand is linked to ${brand.products_count} product${brand.products_count === 1 ? '' : 's'}. Deleting it will remove the brand association from those products.`
                    : 'This will permanently remove the brand.',
            confirmLabel: 'Delete brand',
            destructive: true,
            onConfirm: () =>
                router.delete(BrandController.destroy.url(brand), {
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
                                    Brand
                                </th>
                                <th className="px-4 py-3 text-left text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                    Slug
                                </th>
                                <th className="px-4 py-3 text-left text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                    Status
                                </th>
                                <th className="px-4 py-3 text-center text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                    Products
                                </th>
                                <th className="px-3 py-3 text-right text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody className="divide-y">
                            {brands.map((brand) => (
                                <tr
                                    key={brand.id}
                                    className="group transition-colors hover:bg-muted/30"
                                >
                                    {/* Brand with logo thumbnail */}
                                    <td className="px-4 py-3 align-middle">
                                        <div className="flex items-center gap-3">
                                            <div className="h-10 w-10 shrink-0 overflow-hidden rounded-md border bg-muted/30">
                                                {brand.image_url ? (
                                                    <img
                                                        src={brand.image_url}
                                                        alt={brand.name}
                                                        className="h-full w-full object-contain p-1"
                                                    />
                                                ) : (
                                                    <div className="flex h-full w-full items-center justify-center text-xs font-bold text-muted-foreground">
                                                        {brand.name
                                                            .charAt(0)
                                                            .toUpperCase()}
                                                    </div>
                                                )}
                                            </div>
                                            <div>
                                                <Link
                                                    href={BrandController.show.url(
                                                        brand,
                                                    )}
                                                    className="font-medium hover:underline"
                                                >
                                                    {brand.name}
                                                </Link>
                                                {brand.description && (
                                                    <p className="mt-0.5 max-w-sm truncate text-xs text-muted-foreground">
                                                        {brand.description}
                                                    </p>
                                                )}
                                            </div>
                                        </div>
                                    </td>

                                    {/* Slug */}
                                    <td className="px-4 py-3 align-middle font-mono text-xs text-muted-foreground">
                                        {brand.slug}
                                    </td>

                                    {/* Status */}
                                    <td className="px-4 py-3 align-middle">
                                        <StatusBadge
                                            status={
                                                brand.is_active
                                                    ? 'active'
                                                    : 'inactive'
                                            }
                                        />
                                    </td>

                                    {/* Products count */}
                                    <td className="px-4 py-3 text-center align-middle">
                                        <span className="rounded-full bg-muted px-2.5 py-0.5 text-xs font-medium text-muted-foreground tabular-nums">
                                            {brand.products_count}
                                        </span>
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
                                                    href={BrandController.show.url(
                                                        brand,
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
                                                    href={BrandController.edit.url(
                                                        brand,
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
                                                    handleDelete(brand)
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
