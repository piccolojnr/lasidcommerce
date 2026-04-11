import { DataTableToolbar } from '@/components/shared/data-table/data-table-toolbar';
import { PageHeader } from '@/components/shared/page-header/page-header';
import { Button } from '@/components/ui/button';
import { AdminLayout } from '@/layouts/app/admin-layout';
import { OrderTable } from '@/pages/admin/orders/_components/order-table';

export default function OrderIndexPage() {
    return (
        <AdminLayout title="Orders" description="Track order intake and fulfillment progress.">
            <div className="space-y-6">
                <PageHeader title="Orders" description="Monitor incoming orders and their operational state." actions={<Button variant="outline">Export</Button>} />
                <DataTableToolbar searchPlaceholder="Search orders..." />
                <OrderTable />
            </div>
        </AdminLayout>
    );
}
