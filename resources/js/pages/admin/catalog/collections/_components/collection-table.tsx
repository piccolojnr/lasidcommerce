import { router } from '@inertiajs/react';
import { Link } from '@inertiajs/react';
import { Eye, Pencil, Trash2 } from 'lucide-react';
import * as CollectionController from '@/actions/App/Http/Controllers/Admin/Catalog/CollectionController';
import { useConfirmDialog } from '@/components/shared/confirm-dialog/confirm-dialog';
import { StatusBadge } from '@/components/shared/status-badge/status-badge';
import { Button } from '@/components/ui/button';
import type { AdminCollection } from '@/types/admin/catalog';

export function CollectionTable({ collections }: { collections: AdminCollection[] }) {
    const { confirmDialog, requestConfirm } = useConfirmDialog();

    if (collections.length === 0) {
        return (
            <div className="rounded-lg border bg-background px-6 py-14 text-center">
                <p className="text-sm text-muted-foreground">
                    No collections yet. Create one to curate a merchandising rail.
                </p>
            </div>
        );
    }

    function handleDelete(collection: AdminCollection) {
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
                                    Collection
                                </th>
                                <th className="px-4 py-3 text-left text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                    Status
                                </th>
                                <th className="px-4 py-3 text-center text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                    Sort order
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
                            {collections.map((collection) => (
                                <tr
                                    key={collection.id}
                                    className="group transition-colors hover:bg-muted/30"
                                >
                                    {/* Collection name + slug */}
                                    <td className="px-4 py-3 align-middle">
                                        <Link
                                            href={CollectionController.show.url(collection)}
                                            className="font-medium hover:underline"
                                        >
                                            {collection.name}
                                        </Link>
                                        <p className="mt-0.5 font-mono text-xs text-muted-foreground">
                                            {collection.slug}
                                        </p>
                                        {collection.description && (
                                            <p className="mt-0.5 max-w-sm text-xs text-muted-foreground">
                                                {collection.description}
                                            </p>
                                        )}
                                    </td>

                                    {/* Status */}
                                    <td className="px-4 py-3 align-middle">
                                        <StatusBadge
                                            status={collection.is_active ? 'active' : 'inactive'}
                                        />
                                    </td>

                                    {/* Sort order pill */}
                                    <td className="px-4 py-3 text-center align-middle">
                                        <span className="rounded-full bg-muted px-2.5 py-0.5 text-xs font-medium tabular-nums text-muted-foreground">
                                            #{collection.sort_order}
                                        </span>
                                    </td>

                                    {/* Products count */}
                                    <td className="px-4 py-3 text-center align-middle">
                                        <span className="rounded-full bg-muted px-2.5 py-0.5 text-xs font-medium tabular-nums text-muted-foreground">
                                            {collection.products_count}
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
                                                <Link href={CollectionController.show.url(collection)}>
                                                    <Eye className="size-4" />
                                                    <span className="sr-only">View</span>
                                                </Link>
                                            </Button>
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                className="size-8"
                                                asChild
                                                title="Edit"
                                            >
                                                <Link href={CollectionController.edit.url(collection)}>
                                                    <Pencil className="size-4" />
                                                    <span className="sr-only">Edit</span>
                                                </Link>
                                            </Button>
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                className="size-8 text-destructive hover:bg-destructive/10 hover:text-destructive"
                                                title="Delete"
                                                onClick={() => handleDelete(collection)}
                                            >
                                                <Trash2 className="size-4" />
                                                <span className="sr-only">Delete</span>
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
