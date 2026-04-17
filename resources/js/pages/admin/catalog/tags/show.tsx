import { Link } from '@inertiajs/react';
import * as TagController from '@/actions/App/Http/Controllers/Admin/Catalog/TagController';
import { PageHeader } from '@/components/shared/page-header/page-header';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { AdminLayout } from '@/layouts/app/admin-layout';
import type { AdminTag } from '@/types/admin/catalog';

export default function TagShowPage({ tag }: { tag: AdminTag }) {
    return (
        <AdminLayout title="Tag Details">
            <div className="mx-auto w-full max-w-5xl space-y-6">
                <PageHeader title={tag.name} description="Review tag naming, slugging, and storefront readiness." actions={<div className="flex items-center gap-2"><Button variant="outline" asChild><Link href={TagController.index.url()}>Back to list</Link></Button><Button asChild><Link href={TagController.edit.url(tag)}>Edit</Link></Button></div>} />
                <div className="grid gap-4 md:grid-cols-3">
                    <Card><CardHeader><CardTitle>Status</CardTitle></CardHeader><CardContent><span className={`rounded-full px-2.5 py-1 text-xs font-medium ${tag.is_active ? 'bg-primary/10 text-primary' : 'bg-muted text-muted-foreground'}`}>{tag.is_active ? 'Active' : 'Inactive'}</span></CardContent></Card>
                    <Card><CardHeader><CardTitle>Products</CardTitle></CardHeader><CardContent><div className="text-3xl font-semibold">{tag.products_count}</div></CardContent></Card>
                    <Card><CardHeader><CardTitle>Slug</CardTitle></CardHeader><CardContent><div className="font-mono text-sm">{tag.slug}</div></CardContent></Card>
                </div>
                <Card>
                    <CardHeader><CardTitle>Description</CardTitle></CardHeader>
                    <CardContent><p className="text-sm text-muted-foreground">{tag.description ?? 'No description yet.'}</p></CardContent>
                </Card>
            </div>
        </AdminLayout>
    );
}
