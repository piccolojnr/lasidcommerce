import { Form, Link } from '@inertiajs/react';
import * as CategoryController from '@/actions/App/Http/Controllers/Admin/Catalog/CategoryController';
import { StatusBadge } from '@/components/shared/status-badge/status-badge';
import { Button } from '@/components/ui/button';
import type { AdminCategory } from '@/types/admin/catalog';

interface CategoryTableProps {
    categories: AdminCategory[];
}

export function CategoryTable({ categories }: CategoryTableProps) {
    if (categories.length === 0) {
        return (
            <div className="rounded-lg border bg-background px-6 py-12 text-center">
                <p className="text-sm text-muted-foreground">
                    No categories yet. Create one to get started.
                </p>
            </div>
        );
    }

    return (
        <div className="mx-auto w-full max-w-6xl overflow-hidden rounded-lg border bg-background">
            <div className="overflow-x-auto">
                <table className="min-w-full text-center text-sm">
                    <thead className="bg-muted/40">
                        <tr>
                            <th className="px-4 py-3 font-medium text-muted-foreground">
                                Name
                            </th>
                            <th className="px-4 py-3 font-medium text-muted-foreground">
                                Slug
                            </th>
                            <th className="px-4 py-3 font-medium text-muted-foreground">
                                Parent
                            </th>
                            <th className="px-4 py-3 font-medium text-muted-foreground">
                                Order
                            </th>
                            <th className="px-4 py-3 font-medium text-muted-foreground">
                                Status
                            </th>
                            <th className="px-4 py-3 font-medium text-muted-foreground">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        {categories.map((category) => (
                            <tr key={category.id} className="border-t">
                                <td className="px-4 py-3 align-middle">
                                    <span
                                        className="inline-flex items-center gap-1"
                                        style={{
                                            paddingLeft: `${category.depth * 20}px`,
                                        }}
                                    >
                                        {category.depth > 0 && (
                                            <span className="text-muted-foreground select-none">
                                                └
                                            </span>
                                        )}
                                        {category.name}
                                        {category.children_count > 0 && (
                                            <span className="ml-1 text-xs text-muted-foreground">
                                                ({category.children_count})
                                            </span>
                                        )}
                                    </span>
                                </td>
                                <td className="px-4 py-3 align-middle text-muted-foreground">
                                    {category.slug}
                                </td>
                                <td className="px-4 py-3 align-middle text-muted-foreground">
                                    {category.parent_name ?? (
                                        <span className="italic">Root</span>
                                    )}
                                </td>
                                <td className="px-4 py-3 align-middle">
                                    {category.sort_order}
                                </td>
                                <td className="px-4 py-3 align-middle">
                                    <StatusBadge
                                        status={
                                            category.is_active
                                                ? 'active'
                                                : 'inactive'
                                        }
                                    />
                                </td>
                                <td className="px-4 py-3 align-middle">
                                    <div className="flex items-center justify-center gap-2">
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            asChild
                                        >
                                            <Link
                                                href={CategoryController.show.url(
                                                    category,
                                                )}
                                            >
                                                View
                                            </Link>
                                        </Button>
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            asChild
                                        >
                                            <Link
                                                href={CategoryController.edit.url(
                                                    category,
                                                )}
                                            >
                                                Edit
                                            </Link>
                                        </Button>
                                        <Form
                                            {...CategoryController.destroy.form.delete(
                                                category,
                                            )}
                                            onSubmit={(e) => {
                                                if (
                                                    !window.confirm(
                                                        'Are you sure you want to delete "' +
                                                            category.name +
                                                            '"?',
                                                    )
                                                ) {
                                                    e.preventDefault();
                                                }
                                            }}
                                        >
                                            {() => (
                                                <Button
                                                    type="submit"
                                                    variant="outline"
                                                    size="sm"
                                                    className="text-destructive hover:text-destructive"
                                                >
                                                    Delete
                                                </Button>
                                            )}
                                        </Form>
                                    </div>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </div>
    );
}
