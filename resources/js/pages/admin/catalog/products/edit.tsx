import { PageHeader } from '@/components/shared/page-header/page-header';
import { AdminLayout } from '@/layouts/app/admin-layout';
import { ProductForm } from '@/pages/admin/catalog/products/_components/product-form';

export default function ProductEditPage() {
    return (
        <AdminLayout title="Edit Product" description="Update product catalog information.">
            <div className="space-y-6">
                <PageHeader title="Edit product" description="Refine pricing, metadata, and merchandising fields." />
                <ProductForm />
            </div>
        </AdminLayout>
    );
}
