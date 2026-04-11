import { Link } from '@inertiajs/react';
import * as ProductController from '@/actions/App/Http/Controllers/Admin/Catalog/ProductController';
import { PageHeader } from '@/components/shared/page-header/page-header';
import { Button } from '@/components/ui/button';
import { AdminLayout } from '@/layouts/app/admin-layout';
import { ProductForm } from '@/pages/admin/catalog/products/_components/product-form';
import type { AdminProduct } from '@/types/admin/catalog';

interface SelectOption {
    id: number;
    name: string;
}

interface Props {
    product: AdminProduct;
    categories: SelectOption[];
    brands: SelectOption[];
}

export default function ProductEditPage({ product, categories, brands }: Props) {
    return (
        <AdminLayout title="Edit Product">
            <div className="mx-auto w-full max-w-3xl space-y-6">
                <PageHeader
                    title={`Edit ${product.name}`}
                    description="Update product information."
                    actions={
                        <Button variant="outline" asChild>
                            <Link href={ProductController.show.url(product)}>Back to product</Link>
                        </Button>
                    }
                />
                <ProductForm product={product} categories={categories} brands={brands} />
            </div>
        </AdminLayout>
    );
}
