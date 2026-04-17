import { Form, Link } from '@inertiajs/react';
import * as CollectionController from '@/actions/App/Http/Controllers/Admin/Catalog/CollectionController';
import { Button } from '@/components/ui/button';
import type { AdminCollection } from '@/types/admin/catalog';

export function CollectionTable({ collections }: { collections: AdminCollection[] }) {
    if (collections.length === 0) {
        return (
            <div className="rounded-lg border bg-background px-6 py-12 text-center">
                <p className="text-sm text-muted-foreground">No collections yet. Create one to curate a merchandising rail.</p>
            </div>
        );
    }

    return (
        <div className="overflow-hidden rounded-lg border bg-background">
            <div className="overflow-x-auto">
                <table className="min-w-full text-sm">
                    <thead className="bg-muted/40">
                        <tr>
                            <th className="px-4 py-3 text-left font-medium text-muted-foreground">Collection</th>
                            <th className="px-4 py-3 text-left font-medium text-muted-foreground">Status</th>
                            <th className="px-4 py-3 text-left font-medium text-muted-foreground">Sort order</th>
                            <th className="px-4 py-3 text-left font-medium text-muted-foreground">Products</th>
                            <th className="px-4 py-3 text-left font-medium text-muted-foreground">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        {collections.map((collection) => (
                            <tr key={collection.id} className="border-t align-top">
                                <td className="px-4 py-4">
                                    <div className="space-y-1">
                                        <div className="font-medium">{collection.name}</div>
                                        <div className="font-mono text-xs text-muted-foreground">{collection.slug}</div>
                                        {collection.description && <p className="max-w-md text-xs text-muted-foreground">{collection.description}</p>}
                                    </div>
                                </td>
                                <td className="px-4 py-4"><span className={`rounded-full px-2.5 py-1 text-xs font-medium ${collection.is_active ? 'bg-primary/10 text-primary' : 'bg-muted text-muted-foreground'}`}>{collection.is_active ? 'Active' : 'Inactive'}</span></td>
                                <td className="px-4 py-4 text-muted-foreground">{collection.sort_order}</td>
                                <td className="px-4 py-4 text-muted-foreground">{collection.products_count}</td>
                                <td className="px-4 py-4">
                                    <div className="flex items-center gap-2">
                                        <Button variant="outline" size="sm" asChild><Link href={CollectionController.show.url(collection)}>View</Link></Button>
                                        <Button variant="outline" size="sm" asChild><Link href={CollectionController.edit.url(collection)}>Edit</Link></Button>
                                        <Form {...CollectionController.destroy.form.delete(collection)} onSubmit={(e) => {
                                            if (!window.confirm(`Delete "${collection.name}"?`)) {
                                                e.preventDefault();
                                            }
                                        }}>
                                            {() => <Button type="submit" variant="outline" size="sm" className="text-destructive hover:text-destructive">Delete</Button>}
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
