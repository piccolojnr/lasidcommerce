import { router } from '@inertiajs/react';
import { Link } from '@inertiajs/react';
import { Calendar, Layers, Pencil, Tag as TagIcon, Trash2 } from 'lucide-react';
import * as TagController from '@/actions/App/Http/Controllers/Admin/Catalog/TagController';
import { useConfirmDialog } from '@/components/shared/confirm-dialog/confirm-dialog';
import { PageHeader } from '@/components/shared/page-header/page-header';
import { StatusBadge } from '@/components/shared/status-badge/status-badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { AdminLayout } from '@/layouts/app/admin-layout';
import { formatDate } from '@/lib/formatters/date';
import type { AdminTag } from '@/types/admin/catalog';

export default function TagShowPage({ tag }: { tag: AdminTag }) {
    const { confirmDialog, requestConfirm } = useConfirmDialog();

    function handleDelete() {
        requestConfirm({
            title: `Delete tag "${tag.name}"?`,
            description:
                tag.products_count > 0
                    ? `This tag is assigned to ${tag.products_count} product${tag.products_count === 1 ? '' : 's'}. Deleting it will remove the tag from those products.`
                    : 'This will permanently remove the tag.',
            confirmLabel: 'Delete tag',
            destructive: true,
            onConfirm: () =>
                router.delete(TagController.destroy.url(tag), {
                    onSuccess: () => router.visit(TagController.index.url()),
                }),
        });
    }

    return (
        <AdminLayout title={tag.name}>
            {confirmDialog}
            <div className="mx-auto w-full max-w-5xl space-y-6">
                <PageHeader
                    title={tag.name}
                    description="Review tag naming, slugging, and storefront readiness."
                    actions={
                        <div className="flex items-center gap-2">
                            <Button variant="outline" size="sm" asChild>
                                <Link href={TagController.index.url()}>
                                    All tags
                                </Link>
                            </Button>
                            <Button variant="outline" size="sm" asChild>
                                <Link href={TagController.edit.url(tag)}>
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
                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <Card className="border-border/70">
                        <CardContent className="flex items-center gap-3 pt-4 pb-4">
                            <div className="flex size-9 items-center justify-center rounded-lg bg-muted">
                                <Layers className="size-4 text-muted-foreground" />
                            </div>
                            <div>
                                <p className="text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                    Tagged products
                                </p>
                                <p className="text-2xl font-semibold tabular-nums">
                                    {tag.products_count}
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
                                    {formatDate(tag.created_at)}
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
                                            tag.is_active
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
                                {tag.slug}
                            </span>
                        </div>
                        <div className="flex items-start gap-4 py-3">
                            <span className="w-32 shrink-0 text-xs font-medium text-muted-foreground">
                                Description
                            </span>
                            <span className="text-sm text-muted-foreground">
                                {tag.description ?? (
                                    <span className="italic opacity-60">
                                        No description.
                                    </span>
                                )}
                            </span>
                        </div>
                    </CardContent>
                </Card>

                {/* Tagged products */}
                <Card className="border-border/70">
                    <CardHeader className="border-b border-border/60 bg-muted/20 py-4">
                        <div className="flex items-center justify-between">
                            <CardTitle className="text-sm font-semibold">
                                Tagged products
                                {tag.products_count > tag.products.length ? (
                                    <span className="ml-2 text-xs font-normal text-muted-foreground">
                                        (showing first {tag.products.length} of{' '}
                                        {tag.products_count})
                                    </span>
                                ) : null}
                            </CardTitle>
                            <div className="flex items-center gap-2">
                                <span className="rounded-full bg-muted px-2.5 py-0.5 text-xs font-medium text-muted-foreground tabular-nums">
                                    {tag.products_count}
                                </span>
                                <span className="rounded-full bg-primary/10 px-3 py-1 text-xs font-medium text-primary">
                                    <TagIcon className="mr-1 inline size-3" />
                                    {tag.name}
                                </span>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent className="p-0">
                        {tag.products.length === 0 ? (
                            <div className="px-6 py-14 text-center">
                                <p className="text-sm text-muted-foreground">
                                    No products carry this tag yet.
                                </p>
                            </div>
                        ) : (
                            <div className="overflow-x-auto">
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
                                                Category
                                            </th>
                                            <th className="px-4 py-2.5 text-left text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                                Status
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y">
                                        {tag.products.map((product) => (
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
                                                                {product.name}
                                                            </p>
                                                            <p className="text-xs text-muted-foreground">
                                                                {product.brand_name ??
                                                                    'No brand'}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td className="px-4 py-3 font-mono text-xs text-muted-foreground">
                                                    {product.sku}
                                                </td>
                                                <td className="px-4 py-3 text-sm text-muted-foreground">
                                                    {product.category_name ??
                                                        '—'}
                                                </td>
                                                <td className="px-4 py-3">
                                                    <StatusBadge
                                                        status={product.status}
                                                    />
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
