import { Link } from '@inertiajs/react';
import * as CollectionController from '@/actions/App/Http/Controllers/Admin/Catalog/CollectionController';
import { PageHeader } from '@/components/shared/page-header/page-header';
import { Button } from '@/components/ui/button';
import { AdminLayout } from '@/layouts/app/admin-layout';
import { CollectionForm } from '@/pages/admin/catalog/collections/_components/collection-form';
import type { AdminCollection } from '@/types/admin/catalog';

interface ProductOption {
    id: number;
    name: string;
    sku: string;
    status: string;
}

export default function CollectionEditPage({
    collection,
    products,
}: {
    collection: AdminCollection;
    products: ProductOption[];
}) {
    return (
        <AdminLayout title="Edit Collection">
            <div className="mx-auto w-full max-w-5xl space-y-6">
                <PageHeader
                    title={`Edit ${collection.name}`}
                    description="Adjust collection copy, visibility, and explicit product ordering."
                    actions={
                        <Button variant="outline" asChild>
                            <Link
                                href={CollectionController.show.url(collection)}
                            >
                                Back to collection
                            </Link>
                        </Button>
                    }
                />
                <CollectionForm collection={collection} products={products} />
            </div>
        </AdminLayout>
    );
}
