import { Link } from '@inertiajs/react';
import * as BrandController from '@/actions/App/Http/Controllers/Admin/Catalog/BrandController';
import { PageHeader } from '@/components/shared/page-header/page-header';
import { Button } from '@/components/ui/button';
import { AdminLayout } from '@/layouts/app/admin-layout';
import { BrandForm } from '@/pages/admin/catalog/brands/_components/brand-form';

export default function BrandCreatePage() {
    return (
        <AdminLayout title="Create Brand">
            <div className="mx-auto w-full max-w-3xl space-y-6">
                <PageHeader
                    title="Create brand"
                    description="Add a new brand to the catalog."
                    actions={
                        <Button variant="outline" asChild>
                            <Link href={BrandController.index.url()}>Back to list</Link>
                        </Button>
                    }
                />
                <BrandForm />
            </div>
        </AdminLayout>
    );
}
