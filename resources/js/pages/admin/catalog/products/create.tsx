import { Link } from '@inertiajs/react';
import * as ProductController from '@/actions/App/Http/Controllers/Admin/Catalog/ProductController';
import { PageHeader } from '@/components/shared/page-header/page-header';
import { Button } from '@/components/ui/button';
import { AdminLayout } from '@/layouts/app/admin-layout';
import { ProductForm } from '@/pages/admin/catalog/products/_components/product-form';

interface SelectOption {
    id: number;
    name: string;
}

interface Props {
    categories: SelectOption[];
    brands: SelectOption[];
    tags: SelectOption[];
    collections: SelectOption[];
}

export default function ProductCreatePage({ categories, brands, tags, collections }: Props) {
    return (
        <AdminLayout title="Create Product">
            <div className="mx-auto w-full max-w-7xl space-y-6">
                <PageHeader
                    title="Create product"
                    description="Build a stronger catalog entry with better copy, imagery, and merchandising controls."
                    actions={
                        <Button variant="outline" asChild>
                            <Link href={ProductController.index.url()}>Back to list</Link>
                        </Button>
                    }
                />
                <ProductForm categories={categories} brands={brands} tags={tags} collections={collections} />
            </div>
        </AdminLayout>
    );
}
