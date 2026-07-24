import { router } from '@inertiajs/react';
import { Link } from '@inertiajs/react';
import { Calendar, Hash, Layers, Pencil, Trash2 } from 'lucide-react';
import * as CollectionController from '@/actions/App/Http/Controllers/Admin/Catalog/CollectionController';
import { useConfirmDialog } from '@/components/shared/confirm-dialog/confirm-dialog';
import { PageHeader } from '@/components/shared/page-header/page-header';
import { StatusBadge } from '@/components/shared/status-badge/status-badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { AdminLayout } from '@/layouts/app/admin-layout';
import { formatDate } from '@/lib/formatters/date';
import type { AdminCollection } from '@/types/admin/catalog';

export default function CollectionShowPage({
    collection,
}: {
    collection: AdminCollection;
}) {
    const { confirmDialog, requestConfirm } = useConfirmDialog();

    function handleDelete() {
        requestConfirm({
            title: `Delete "${collection.name}"?`,
            description:
                collection.products_count > 0
                    ? `This collection contains ${collection.products_count} product${collection.products_count === 1 ? '' : 's'}. Deleting it will remove the rail from the storefront.`
                    : 'This will permanently remove the collection.',
            confirmLabel: 'Delete collection',
            destructive: true,
            onConfirm: () =>
                router.delete(CollectionController.destroy.url(collection), {
                    onSuccess: () =>
                        router.visit(CollectionController.index.url()),
                }),
        });
    }

    return (
        <AdminLayout title={collection.name}>
            {confirmDialog}
            <div className="mx-auto w-full max-w-5xl space-y-6">
                <PageHeader
                    title={collection.name}
                    description="Review the curated product mix and storefront state of this collection."
                    actions={
                        <div className="flex items-center gap-2">
                            <Button variant="outline" size="sm" asChild>
                                <Link href={CollectionController.index.url()}>
                                    All collections
                                </Link>
                            </Button>
                            <Button variant="outline" size="sm" asChild>
                                <Link
                                    href={CollectionController.edit.url(
                                        collection,
                                    )}
                                >
                                    <Pencil className="mr-1.5 size-3.5" />
                                    Edit
                                </Link>
                            </Button>
                            <Button
                                variant="outline"
                                size="sm"
                                className="text-destructive hover:bg-destructive/10 hover:text-destructive"
                                onClick={handleDelete}
                            >
                                <Trash2 className="mr-1.5 size-3.5" />
                                Delete
                            </Button>
                        </div>
                    }
                />

                {/* Stat strip */}
                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <Card className="border-border/70">
                        <CardContent className="flex items-center gap-3 pt-4 pb-4">
                            <div className="flex size-9 items-center justify-center rounded-lg bg-muted">
                                <Layers className="size-4 text-muted-foreground" />
                            </div>
                            <div>
                                <p className="text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                    Products
                                </p>
                                <p className="text-2xl font-semibold tabular-nums">
                                    {collection.products_count}
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                    <Card className="border-border/70">
                        <CardContent className="flex items-center gap-3 pt-4 pb-4">
                            <div className="flex size-9 items-center justify-center rounded-lg bg-muted">
                                <Hash className="size-4 text-muted-foreground" />
                            </div>
                            <div>
                                <p className="text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                    Sort order
                                </p>
                                <p className="text-2xl font-semibold tabular-nums">
                                    #{collection.sort_order}
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                    <Card className="border-border/70">
                        <CardContent className="flex items-center gap-3 pt-4 pb-4">
                            <div className="flex size-9 items-center justify-center rounded-lg bg-muted">
                                <Calendar className="size-4 text-muted-foreground" />
                            </div>
                            <div>
                                <p className="text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                    Created
                                </p>
                                <p className="text-sm font-medium">
                                    {formatDate(collection.created_at)}
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                    <Card className="border-border/70">
                        <CardContent className="flex items-center gap-3 pt-4 pb-4">
                            <div>
                                <p className="text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                    Status
                                </p>
                                <div className="mt-1.5">
                                    <StatusBadge
                                        status={
                                            collection.is_active
                                                ? 'active'
                                                : 'inactive'
                                        }
                                    />
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Details card */}
                <Card className="border-border/70">
                    <CardHeader className="border-b border-border/60 bg-muted/20 py-4">
                        <CardTitle className="text-sm font-semibold">
                            Details
                        </CardTitle>
                    </CardHeader>
                    <CardContent className="divide-y py-0">
                        <div className="flex items-start gap-4 py-3">
                            <span className="w-32 shrink-0 text-xs font-medium text-muted-foreground">
                                Slug
                            </span>
                            <span className="font-mono text-sm">
                                {collection.slug}
                            </span>
                        </div>
                        <div className="flex items-start gap-4 py-3">
                            <span className="w-32 shrink-0 text-xs font-medium text-muted-foreground">
                                Description
                            </span>
                            <span className="text-sm text-muted-foreground">
                                {collection.description ?? (
                                    <span className="italic opacity-60">
                                        No description.
                                    </span>
                                )}
                            </span>
                        </div>
                    </CardContent>
                </Card>

                {/* Curated products */}
                <Card className="border-border/70">
                    <CardHeader className="border-b border-border/60 bg-muted/20 py-4">
                        <div className="flex items-center justify-between">
                            <CardTitle className="text-sm font-semibold">
                                Curated products
                            </CardTitle>
                            <Button variant="outline" size="sm" asChild>
                                <Link
                                    href={CollectionController.edit.url(
                                        collection,
                                    )}
                                >
                                    <Pencil className="mr-1.5 size-3.5" />
                                    Edit products
                                </Link>
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent className="p-0">
                        {collection.products.length === 0 ? (
                            <div className="px-6 py-14 text-center">
                                <p className="text-sm text-muted-foreground">
                                    No products assigned yet.{' '}
                                    <Link
                                        href={CollectionController.edit.url(
                                            collection,
                                        )}
                                        className="font-medium text-primary hover:underline"
                                    >
                                        Add products
                                    </Link>
                                </p>
                            </div>
                        ) : (
                            <div className="overflow-hidden">
                                <table className="min-w-full text-sm">
                                    <thead>
                                        <tr className="border-b bg-muted/20">
                                            <th className="px-4 py-2.5 text-left text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                                Product
                                            </th>
                                            <th className="px-4 py-2.5 text-left text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                                SKU
                                            </th>
                                            <th className="px-4 py-2.5 text-left text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                                Status
                                            </th>
                                            <th className="px-4 py-2.5 text-center text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                                Sort order
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y">
                                        {collection.products
                                            .slice()
                                            .sort(
                                                (a, b) =>
                                                    a.sort_order - b.sort_order,
                                            )
                                            .map((product) => (
                                                <tr
                                                    key={product.id}
                                                    className="transition-colors hover:bg-muted/20"
                                                >
                                                    <td className="px-4 py-3">
                                                        <div className="flex items-center gap-3">
                                                            <div className="h-9 w-9 shrink-0 overflow-hidden rounded-md border bg-muted/30">
                                                                {product.primary_image_url ? (
                                                                    <img
                                                                        src={
                                                                            product.primary_image_url
                                                                        }
                                                                        alt=""
                                                                        className="h-full w-full object-cover"
                                                                    />
                                                                ) : null}
                                                            </div>
                                                            <div>
                                                                <p className="leading-snug font-medium">
                                                                    {
                                                                        product.name
                                                                    }
                                                                </p>
                                                                <p className="text-xs text-muted-foreground">
                                                                    {product.brand_name ??
                                                                        'No brand'}
                                                                    {product.category_name
                                                                        ? ` · ${product.category_name}`
                                                                        : ''}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td className="px-4 py-3 font-mono text-xs text-muted-foreground">
                                                        {product.sku}
                                                    </td>
                                                    <td className="px-4 py-3">
                                                        <StatusBadge
                                                            status={
                                                                product.status
                                                            }
                                                        />
                                                    </td>
                                                    <td className="px-4 py-3 text-center">
                                                        <span className="rounded-full bg-muted px-2.5 py-0.5 text-xs font-medium text-muted-foreground tabular-nums">
                                                            #
                                                            {product.sort_order}
                                                        </span>
                                                    </td>
                                                </tr>
                                            ))}
                                    </tbody>
                                </table>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AdminLayout>
    );
}
