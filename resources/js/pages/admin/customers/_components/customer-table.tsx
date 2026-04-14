import { Link } from '@inertiajs/react';
import { DataTable } from '@/components/shared/data-table/data-table';
import type { DataTableColumn } from '@/components/shared/data-table/data-table';
import { EmptyState } from '@/components/shared/empty-state/empty-state';
import { StatusBadge } from '@/components/shared/status-badge/status-badge';
import { formatDate } from '@/lib/formatters/date';
import type { AdminCustomer } from '@/types/admin/user';

const customerPath = (id: number) => `/admin/customers/${id}`;

const columns: DataTableColumn<AdminCustomer>[] = [
    {
        key: 'name',
        title: 'Name',
        render: (row) => (
            <Link href={customerPath(row.id)} className="font-medium hover:underline">
                {row.name}
            </Link>
        ),
    },
    { key: 'email', title: 'Email' },
    {
        key: 'status',
        title: 'Status',
        render: (row) => <StatusBadge status={row.status} />,
    },
    { key: 'orders_count', title: 'Orders' },
    { key: 'payments_count', title: 'Payments' },
    {
        key: 'created_at',
        title: 'Created',
        render: (row) => formatDate(row.created_at),
    },
];

export function CustomerTable({ customers }: { customers: AdminCustomer[] }) {
    return (
        <DataTable
            columns={columns}
            data={customers}
            emptyState={
                <EmptyState
                    title="No customers found"
                    description="Try a different search or status filter."
                />
            }
        />
    );
}
