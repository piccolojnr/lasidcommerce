import { Link } from '@inertiajs/react';
import * as BrandController from '@/actions/App/Http/Controllers/Admin/Catalog/BrandController';
import { PageHeader } from '@/components/shared/page-header/page-header';
import { Button } from '@/components/ui/button';
import { AdminLayout } from '@/layouts/app/admin-layout';
import { BrandForm } from '@/pages/admin/catalog/brands/_components/brand-form';
import type { AdminBrand } from '@/types/admin/catalog';

interface Props {
    brand: AdminBrand;
}

export default function BrandEditPage({ brand }: Props) {
    return (
        <AdminLayout title="Edit Brand">
            <div className="mx-auto w-full max-w-3xl space-y-6">
                <PageHeader
                    title={`Edit ${brand.name}`}
                    description="Update brand information."
                    actions={
                        <Button variant="outline" asChild>
                            <Link href={BrandController.show.url(brand)}>Back to brand</Link>
                        </Button>
                    }
                />
                <BrandForm brand={brand} />
            </div>
        </AdminLayout>
    );
}
