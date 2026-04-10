import { Button } from '@/components/ui/button';
import { DataTable, type DataTableColumn } from '@/components/shared/data-table/data-table';
import { DataTableToolbar } from '@/components/shared/data-table/data-table-toolbar';
import { PageHeader } from '@/components/shared/page-header/page-header';
import { StatusBadge } from '@/components/shared/status-badge/status-badge';
import { formatDate } from '@/lib/formatters/date';
import { formatMoney } from '@/lib/formatters/money';
import { AdminLayout } from '@/layouts/app/admin-layout';
import type { AdminCoupon } from '@/types/admin/coupon';

const coupons: AdminCoupon[] = [{ id: 1, code: 'WELCOME10', type: 'fixed', value: 1000, is_active: true, expires_at: null }];

const columns: DataTableColumn<AdminCoupon>[] = [
    { key: 'code', title: 'Code' },
    { key: 'type', title: 'Type' },
    { key: 'value', title: 'Value', render: (row) => formatMoney(row.value) },
    { key: 'expires_at', title: 'Expires', render: (row) => formatDate(row.expires_at) },
    { key: 'is_active', title: 'Status', render: (row) => <StatusBadge status={row.is_active ? 'active' : 'inactive'} /> },
];

export default function CouponIndexPage() {
    return (
        <AdminLayout title="Coupons" description="Manage discount campaigns and redemption windows.">
            <div className="space-y-6">
                <PageHeader title="Coupons" description="Review campaign codes and discount settings." actions={<Button>Create coupon</Button>} />
                <DataTableToolbar searchPlaceholder="Search coupons..." />
                <DataTable columns={columns} data={coupons} />
            </div>
        </AdminLayout>
    );
}
