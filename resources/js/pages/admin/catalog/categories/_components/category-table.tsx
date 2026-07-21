import { router } from '@inertiajs/react';
import { Link } from '@inertiajs/react';
import { Eye, Pencil, Trash2 } from 'lucide-react';
import * as CategoryController from '@/actions/App/Http/Controllers/Admin/Catalog/CategoryController';
import { useConfirmDialog } from '@/components/shared/confirm-dialog/confirm-dialog';
import { StatusBadge } from '@/components/shared/status-badge/status-badge';
import { Button } from '@/components/ui/button';
import type { AdminCategory } from '@/types/admin/catalog';

interface CategoryTableProps {
    categories: AdminCategory[];
}

export function CategoryTable({ categories }: CategoryTableProps) {
    const { confirmDialog, requestConfirm } = useConfirmDialog();

    if (categories.length === 0) {
        return (
            <div className="rounded-lg border bg-background px-6 py-14 text-center">
                <p className="text-sm text-muted-foreground">
                    No categories yet. Create one to get started.
                </p>
            </div>
        );
    }

    function handleDelete(category: AdminCategory) {
        requestConfirm({
            title: `Delete "${category.name}"?`,
            description:
                category.children_count > 0
                    ? `This category has ${category.children_count} child categor${category.children_count === 1 ? 'y' : 'ies'}. Deleting it may affect the catalog hierarchy.`
                    : 'This will permanently remove the category. Any products assigned to it will become uncategorised.',
            confirmLabel: 'Delete category',
            destructive: true,
            onConfirm: () =>
                router.delete(CategoryController.destroy.url(category), {
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
                                    Name
                                </th>
                                <th className="px-4 py-3 text-left text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                    Slug
                                </th>
                                <th className="px-4 py-3 text-left text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                    Parent
                                </th>
                                <th className="px-4 py-3 text-center text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                    Order
                                </th>
                                <th className="px-4 py-3 text-left text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                    Status
                                </th>
                                <th className="px-3 py-3 text-right text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody className="divide-y">
                            {categories.map((category) => (
                                <tr
                                    key={category.id}
                                    className="group transition-colors hover:bg-muted/30"
                                >
                                    {/* Name with depth indentation */}
                                    <td className="px-4 py-3 align-middle">
                                        <span
                                            className="flex items-center gap-1.5"
                                            style={{
                                                paddingLeft: `${category.depth * 20}px`,
                                            }}
                                        >
                                            {category.depth > 0 && (
                                                <span className="text-muted-foreground/60 select-none">
                                                    └
                                                </span>
                                            )}
                                            <Link
                                                href={CategoryController.show.url(
                                                    category,
                                                )}
                                                className="font-medium hover:underline"
                                            >
                                                {category.name}
                                            </Link>
                                            {category.children_count > 0 && (
                                                <span className="rounded-full bg-muted px-1.5 py-0.5 text-[11px] text-muted-foreground">
                                                    {category.children_count}
                                                </span>
                                            )}
                                        </span>
                                    </td>

                                    {/* Slug */}
                                    <td className="px-4 py-3 align-middle font-mono text-xs text-muted-foreground">
                                        {category.slug}
                                    </td>

                                    {/* Parent */}
                                    <td className="px-4 py-3 align-middle text-sm text-muted-foreground">
                                        {category.parent_name ?? (
                                            <span className="italic opacity-50">
                                                Root
                                            </span>
                                        )}
                                    </td>

                                    {/* Sort order */}
                                    <td className="px-4 py-3 text-center align-middle">
                                        <span className="rounded-full bg-muted px-2.5 py-0.5 text-xs font-medium text-muted-foreground">
                                            {category.sort_order}
                                        </span>
                                    </td>

                                    {/* Status */}
                                    <td className="px-4 py-3 align-middle">
                                        <StatusBadge
                                            status={
                                                category.is_active
                                                    ? 'active'
                                                    : 'inactive'
                                            }
                                        />
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
                                                    href={CategoryController.show.url(
                                                        category,
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
                                                    href={CategoryController.edit.url(
                                                        category,
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
                                                    handleDelete(category)
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
