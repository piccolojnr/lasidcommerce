import { router } from '@inertiajs/react';
import { Link } from '@inertiajs/react';
import { Eye, Pencil, Trash2 } from 'lucide-react';
import * as TagController from '@/actions/App/Http/Controllers/Admin/Catalog/TagController';
import { useConfirmDialog } from '@/components/shared/confirm-dialog/confirm-dialog';
import { StatusBadge } from '@/components/shared/status-badge/status-badge';
import { Button } from '@/components/ui/button';
import type { AdminTag } from '@/types/admin/catalog';

export function TagTable({ tags }: { tags: AdminTag[] }) {
    const { confirmDialog, requestConfirm } = useConfirmDialog();

    if (tags.length === 0) {
        return (
            <div className="rounded-lg border bg-background px-6 py-14 text-center">
                <p className="text-sm text-muted-foreground">
                    No tags yet. Create one to get started.
                </p>
            </div>
        );
    }

    function handleDelete(tag: AdminTag) {
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
                                    Tag
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
                            {tags.map((tag) => (
                                <tr
                                    key={tag.id}
                                    className="group transition-colors hover:bg-muted/30"
                                >
                                    {/* Tag chip + slug */}
                                    <td className="px-4 py-3 align-middle">
                                        <div className="flex items-center gap-2">
                                            <span className="rounded-full bg-primary/10 px-3 py-1 text-xs font-medium text-primary">
                                                {tag.name}
                                            </span>
                                        </div>
                                        <p className="mt-0.5 font-mono text-xs text-muted-foreground">
                                            {tag.slug}
                                        </p>
                                        {tag.description && (
                                            <p className="mt-0.5 max-w-sm text-xs text-muted-foreground">
                                                {tag.description}
                                            </p>
                                        )}
                                    </td>

                                    {/* Status */}
                                    <td className="px-4 py-3 align-middle">
                                        <StatusBadge
                                            status={tag.is_active ? 'active' : 'inactive'}
                                        />
                                    </td>

                                    {/* Product count */}
                                    <td className="px-4 py-3 text-center align-middle">
                                        <span className="rounded-full bg-muted px-2.5 py-0.5 text-xs font-medium text-muted-foreground tabular-nums">
                                            {tag.products_count}
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
                                                <Link href={TagController.show.url(tag)}>
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
                                                <Link href={TagController.edit.url(tag)}>
                                                    <Pencil className="size-4" />
                                                    <span className="sr-only">Edit</span>
                                                </Link>
                                            </Button>
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                className="size-8 text-destructive hover:bg-destructive/10 hover:text-destructive"
                                                title="Delete"
                                                onClick={() => handleDelete(tag)}
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
