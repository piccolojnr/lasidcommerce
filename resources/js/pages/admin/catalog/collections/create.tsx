import { Link } from '@inertiajs/react';
import * as CollectionController from '@/actions/App/Http/Controllers/Admin/Catalog/CollectionController';
import { PageHeader } from '@/components/shared/page-header/page-header';
import { Button } from '@/components/ui/button';
import { AdminLayout } from '@/layouts/app/admin-layout';
import { CollectionForm } from '@/pages/admin/catalog/collections/_components/collection-form';

interface ProductOption {
    id: number;
    name: string;
    sku: string;
    status: string;
}

export default function CollectionCreatePage({
    products,
}: {
    products: ProductOption[];
}) {
    return (
        <AdminLayout title="Create Collection">
            <div className="mx-auto w-full max-w-5xl space-y-6">
                <PageHeader
                    title="Create collection"
                    description="Curate a storefront grouping with deliberate product ordering."
                    actions={
                        <Button variant="outline" asChild>
                            <Link href={CollectionController.index.url()}>
                                Back to list
                            </Link>
                        </Button>
                    }
                />
                <CollectionForm products={products} />
            </div>
        </AdminLayout>
    );
}
