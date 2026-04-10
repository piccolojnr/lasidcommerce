import { PageHeader } from '@/components/shared/page-header/page-header';
import { AdminLayout } from '@/layouts/app/admin-layout';
import { CategoryForm } from '@/pages/admin/catalog/categories/_components/category-form';

export default function CategoryEditPage() {
    return (
        <AdminLayout title="Edit Category" description="Update category configuration.">
            <div className="space-y-6">
                <PageHeader title="Edit category" description="Adjust metadata, hierarchy, and visibility." />
                <CategoryForm />
            </div>
        </AdminLayout>
    );
}
