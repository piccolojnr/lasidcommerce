import { DataTableToolbar } from '@/components/shared/data-table/data-table-toolbar';
import { PageHeader } from '@/components/shared/page-header/page-header';
import { AdminLayout } from '@/layouts/app/admin-layout';
import { ShipmentTable } from '@/pages/admin/shipments/_components/shipment-table';

export default function ShipmentIndexPage() {
    return (
        <AdminLayout title="Shipments" description="Track outbound delivery activity and status changes.">
            <div className="space-y-6">
                <PageHeader title="Shipments" description="Manage parcel progress, riders, and delivery state." />
                <DataTableToolbar searchPlaceholder="Search shipments..." />
                <ShipmentTable />
            </div>
        </AdminLayout>
    );
}
