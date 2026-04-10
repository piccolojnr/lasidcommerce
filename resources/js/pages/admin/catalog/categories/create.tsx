import { PageHeader } from '@/components/shared/page-header/page-header';
import { AdminLayout } from '@/layouts/app/admin-layout';
import { CategoryForm } from '@/pages/admin/catalog/categories/_components/category-form';

export default function CategoryCreatePage() {
    return (
        <AdminLayout title="Create Category" description="Add a new catalog category.">
            <div className="space-y-6">
                <PageHeader title="Create category" description="Set up a new category for product organization." />
                <CategoryForm />
            </div>
        </AdminLayout>
    );
}
