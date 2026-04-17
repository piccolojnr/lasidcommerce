import { Link } from '@inertiajs/react';
import * as TagController from '@/actions/App/Http/Controllers/Admin/Catalog/TagController';
import { PageHeader } from '@/components/shared/page-header/page-header';
import { Button } from '@/components/ui/button';
import { AdminLayout } from '@/layouts/app/admin-layout';
import { TagForm } from '@/pages/admin/catalog/tags/_components/tag-form';
import type { AdminTag } from '@/types/admin/catalog';

export default function TagEditPage({ tag }: { tag: AdminTag }) {
    return (
        <AdminLayout title="Edit Tag">
            <div className="mx-auto w-full max-w-4xl space-y-6">
                <PageHeader title={`Edit ${tag.name}`} description="Refine the tag copy and storefront visibility." actions={<Button variant="outline" asChild><Link href={TagController.show.url(tag)}>Back to tag</Link></Button>} />
                <TagForm tag={tag} />
            </div>
        </AdminLayout>
    );
}
