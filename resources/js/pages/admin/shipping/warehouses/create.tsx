import { Link } from '@inertiajs/react';
import { PageHeader } from '@/components/shared/page-header/page-header';
import { Button } from '@/components/ui/button';
import { AdminLayout } from '@/layouts/app/admin-layout';
import { adminRoutes } from '@/lib/routes';
import { WarehouseForm } from '@/pages/admin/shipping/warehouses/_components/warehouse-form';

export default function WarehouseCreatePage() {
    return (
        <AdminLayout title="Create Warehouse">
            <div className="mx-auto w-full max-w-7xl space-y-8">
                <PageHeader
                    title="Create warehouse location"
                    description="Add a new fulfillment origin for shipment assignment and operational routing."
                    actions={
                        <Button variant="outline" asChild>
                            <Link href={adminRoutes.shipping.warehouses}>
                                Back to warehouses
                            </Link>
                        </Button>
                    }
                />
                <WarehouseForm />
            </div>
        </AdminLayout>
    );
}
