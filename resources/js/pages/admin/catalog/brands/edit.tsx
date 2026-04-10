import { PageHeader } from '@/components/shared/page-header/page-header';
import { AdminLayout } from '@/layouts/app/admin-layout';
import { BrandForm } from '@/pages/admin/catalog/brands/_components/brand-form';

export default function BrandEditPage() {
    return (
        <AdminLayout title="Edit Brand" description="Update brand information.">
            <div className="space-y-6">
                <PageHeader title="Edit brand" description="Adjust brand naming, slug, and visibility." />
                <BrandForm />
            </div>
        </AdminLayout>
    );
}
