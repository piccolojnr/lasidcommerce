import { Link } from '@inertiajs/react';
import { PageHeader } from '@/components/shared/page-header/page-header';
import { Button } from '@/components/ui/button';
import { AdminLayout } from '@/layouts/app/admin-layout';
import { WarehouseForm } from '@/pages/admin/shipping/warehouses/_components/warehouse-form';
import type { AdminWarehouseDetail } from '@/types/admin/shipping';

export default function WarehouseEditPage({ warehouse }: { warehouse: AdminWarehouseDetail }) {
    return (
        <AdminLayout title="Edit Warehouse">
            <div className="mx-auto w-full max-w-7xl space-y-8">
                <PageHeader
                    title={`Edit ${warehouse.name}`}
                    description="Adjust the warehouse profile, address, and operational state."
                    actions={<Button variant="outline" asChild><Link href={`/admin/shipping/warehouse-locations/${warehouse.id}`}>Back to warehouse</Link></Button>}
                />
                <WarehouseForm warehouse={warehouse} />
            </div>
        </AdminLayout>
    );
}
