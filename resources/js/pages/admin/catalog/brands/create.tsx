import { PageHeader } from '@/components/shared/page-header/page-header';
import { AdminLayout } from '@/layouts/app/admin-layout';
import { BrandForm } from '@/pages/admin/catalog/brands/_components/brand-form';

export default function BrandCreatePage() {
    return (
        <AdminLayout title="Create Brand" description="Add a new product brand.">
            <div className="space-y-6">
                <PageHeader title="Create brand" description="Capture the essentials for brand management." />
                <BrandForm />
            </div>
        </AdminLayout>
    );
}
