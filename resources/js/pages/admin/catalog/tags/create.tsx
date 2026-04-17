import { Link } from '@inertiajs/react';
import * as TagController from '@/actions/App/Http/Controllers/Admin/Catalog/TagController';
import { PageHeader } from '@/components/shared/page-header/page-header';
import { Button } from '@/components/ui/button';
import { AdminLayout } from '@/layouts/app/admin-layout';
import { TagForm } from '@/pages/admin/catalog/tags/_components/tag-form';

export default function TagCreatePage() {
    return (
        <AdminLayout title="Create Tag">
            <div className="mx-auto w-full max-w-4xl space-y-6">
                <PageHeader title="Create tag" description="Create a reusable merchandising label for storefront discovery." actions={<Button variant="outline" asChild><Link href={TagController.index.url()}>Back to list</Link></Button>} />
                <TagForm />
            </div>
        </AdminLayout>
    );
}
