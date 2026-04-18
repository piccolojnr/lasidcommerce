import { Link } from '@inertiajs/react';
import * as CollectionController from '@/actions/App/Http/Controllers/Admin/Catalog/CollectionController';
import { PageHeader } from '@/components/shared/page-header/page-header';
import { StatusBadge } from '@/components/shared/status-badge/status-badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { AdminLayout } from '@/layouts/app/admin-layout';
import type { AdminCollection } from '@/types/admin/catalog';

export default function CollectionShowPage({ collection }: { collection: AdminCollection }) {
    return (
        <AdminLayout title="Collection Details">
            <div className="mx-auto w-full max-w-6xl space-y-6">
                <PageHeader title={collection.name} description="Review the curated product mix and storefront state of this collection." actions={<div className="flex items-center gap-2"><Button variant="outline" asChild><Link href={CollectionController.index.url()}>Back to list</Link></Button><Button asChild><Link href={CollectionController.edit.url(collection)}>Edit</Link></Button></div>} />
                <div className="grid gap-4 md:grid-cols-3">
                    <Card><CardHeader><CardTitle>Status</CardTitle></CardHeader><CardContent><span className={`rounded-full px-2.5 py-1 text-xs font-medium ${collection.is_active ? 'bg-primary/10 text-primary' : 'bg-muted text-muted-foreground'}`}>{collection.is_active ? 'Active' : 'Inactive'}</span></CardContent></Card>
                    <Card><CardHeader><CardTitle>Sort order</CardTitle></CardHeader><CardContent><div className="text-3xl font-semibold">{collection.sort_order}</div></CardContent></Card>
                    <Card><CardHeader><CardTitle>Products</CardTitle></CardHeader><CardContent><div className="text-3xl font-semibold">{collection.products_count}</div></CardContent></Card>
                </div>
                <Card>
                    <CardHeader><CardTitle>Description</CardTitle></CardHeader>
                    <CardContent><p className="text-sm text-muted-foreground">{collection.description ?? 'No description yet.'}</p></CardContent>
                </Card>
                <Card>
                    <CardHeader><CardTitle>Curated products</CardTitle></CardHeader>
                    <CardContent className="space-y-3">
                        {collection.products.length > 0 ? collection.products.map((product) => (
                            <div key={product.id} className="flex items-center justify-between rounded-2xl border border-border/70 p-4">
                                <div className="space-y-1">
                                    <div className="font-medium">{product.name}</div>
                                    <div className="text-xs text-muted-foreground">{product.sku} • {product.brand_name ?? 'No brand'} • {product.category_name ?? 'No category'}</div>
                                </div>
                                <div className="flex items-center gap-4">
                                    <StatusBadge status={product.status} />
                                    <div className="text-sm text-muted-foreground">Order {product.sort_order}</div>
                                </div>
                            </div>
                        )) : <p className="text-sm text-muted-foreground">No products assigned yet.</p>}
                    </CardContent>
                </Card>
            </div>
        </AdminLayout>
    );
}
