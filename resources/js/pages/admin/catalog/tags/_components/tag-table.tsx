import { Form, Link } from '@inertiajs/react';
import * as TagController from '@/actions/App/Http/Controllers/Admin/Catalog/TagController';
import { Button } from '@/components/ui/button';
import type { AdminTag } from '@/types/admin/catalog';

export function TagTable({ tags }: { tags: AdminTag[] }) {
    if (tags.length === 0) {
        return (
            <div className="rounded-lg border bg-background px-6 py-12 text-center">
                <p className="text-sm text-muted-foreground">No tags yet. Create one to get started.</p>
            </div>
        );
    }

    return (
        <div className="overflow-hidden rounded-lg border bg-background">
            <div className="overflow-x-auto">
                <table className="min-w-full text-sm">
                    <thead className="bg-muted/40">
                        <tr>
                            <th className="px-4 py-3 text-left font-medium text-muted-foreground">Tag</th>
                            <th className="px-4 py-3 text-left font-medium text-muted-foreground">Status</th>
                            <th className="px-4 py-3 text-left font-medium text-muted-foreground">Products</th>
                            <th className="px-4 py-3 text-left font-medium text-muted-foreground">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        {tags.map((tag) => (
                            <tr key={tag.id} className="border-t align-top">
                                <td className="px-4 py-4">
                                    <div className="space-y-1">
                                        <div className="font-medium">{tag.name}</div>
                                        <div className="font-mono text-xs text-muted-foreground">{tag.slug}</div>
                                        {tag.description && (
                                            <p className="max-w-md text-xs text-muted-foreground">{tag.description}</p>
                                        )}
                                    </div>
                                </td>
                                <td className="px-4 py-4">
                                    <span className={`rounded-full px-2.5 py-1 text-xs font-medium ${tag.is_active ? 'bg-primary/10 text-primary' : 'bg-muted text-muted-foreground'}`}>
                                        {tag.is_active ? 'Active' : 'Inactive'}
                                    </span>
                                </td>
                                <td className="px-4 py-4 text-muted-foreground">{tag.products_count}</td>
                                <td className="px-4 py-4">
                                    <div className="flex items-center gap-2">
                                        <Button variant="outline" size="sm" asChild>
                                            <Link href={TagController.show.url(tag)}>View</Link>
                                        </Button>
                                        <Button variant="outline" size="sm" asChild>
                                            <Link href={TagController.edit.url(tag)}>Edit</Link>
                                        </Button>
                                        <Form {...TagController.destroy.form.delete(tag)} onSubmit={(e) => {
                                            if (!window.confirm(`Delete "${tag.name}"?`)) {
                                                e.preventDefault();
                                            }
                                        }}>
                                            {() => (
                                                <Button type="submit" variant="outline" size="sm" className="text-destructive hover:text-destructive">
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
