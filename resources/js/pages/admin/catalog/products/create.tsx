import { PageHeader } from '@/components/shared/page-header/page-header';
import { AdminLayout } from '@/layouts/app/admin-layout';
import { ProductForm } from '@/pages/admin/catalog/products/_components/product-form';

export default function ProductCreatePage() {
    return (
        <AdminLayout title="Create Product" description="Add a new catalog product.">
            <div className="space-y-6">
                <PageHeader title="Create product" description="Define the core product record before variants and media." />
                <ProductForm />
            </div>
        </AdminLayout>
    );
}
