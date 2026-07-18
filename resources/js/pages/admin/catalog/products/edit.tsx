import { Link } from '@inertiajs/react';
import * as ProductController from '@/actions/App/Http/Controllers/Admin/Catalog/ProductController';
import { PageHeader } from '@/components/shared/page-header/page-header';
import { Button } from '@/components/ui/button';
import { AdminLayout } from '@/layouts/app/admin-layout';
import { ProductForm } from '@/pages/admin/catalog/products/_components/product-form';
import { ProductVariantManager } from '@/pages/admin/catalog/products/_components/product-variant-manager';
import type { AdminProduct } from '@/types/admin/catalog';

interface SelectOption {
    id: number;
    name: string;
}

interface Props {
    product: AdminProduct;
    categories: SelectOption[];
    brands: SelectOption[];
    tags: SelectOption[];
    collections: SelectOption[];
}

export default function ProductEditPage({
    product,
    categories,
    brands,
    tags,
    collections,
}: Props) {
    return (
        <AdminLayout title="Edit Product">
            <div className="mx-auto w-full max-w-7xl space-y-6">
                <PageHeader
                    title={`Edit ${product.name}`}
                    description="Refine the merchandising, media, and operational setup without digging through a dull form."
                    actions={
                        <Button variant="outline" asChild>
                            <Link href={ProductController.show.url(product)}>
                                Back to product
                            </Link>
                        </Button>
                    }
                />
                <ProductForm
                    product={product}
                    categories={categories}
                    brands={brands}
                    tags={tags}
                    collections={collections}
                />
                <ProductVariantManager product={product} />
            </div>
        </AdminLayout>
    );
}
