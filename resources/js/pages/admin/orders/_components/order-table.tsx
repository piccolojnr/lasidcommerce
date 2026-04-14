import { Link } from '@inertiajs/react';
import * as OrderController from '@/actions/App/Http/Controllers/Admin/Orders/OrderController';
import { DataTable } from '@/components/shared/data-table/data-table';
import type { DataTableColumn } from '@/components/shared/data-table/data-table';
import { EmptyState } from '@/components/shared/empty-state/empty-state';
import { StatusBadge } from '@/components/shared/status-badge/status-badge';
import { formatDate } from '@/lib/formatters/date';
import { formatMoney } from '@/lib/formatters/money';
import type { AdminOrder } from '@/types/admin/order';

const columns: DataTableColumn<AdminOrder>[] = [
    {
        key: 'order_number',
        title: 'Order',
        render: (row) => (
            <Link
                href={OrderController.show.url(row)}
                className="font-medium text-foreground hover:underline"
            >
                {row.order_number}
            </Link>
        ),
    },
    { key: 'email', title: 'Customer' },
    {
        key: 'status',
        title: 'Status',
        render: (row) => <StatusBadge status={row.status} />,
    },
    {
        key: 'payment_status',
        title: 'Payment',
        render: (row) => <StatusBadge status={row.payment_status} />,
    },
    {
        key: 'total_amount',
        title: 'Total',
        render: (row) => formatMoney(row.total_amount, row.currency_code),
    },
    {
        key: 'placed_at',
        title: 'Placed',
        render: (row) => formatDate(row.placed_at),
    },
];

interface OrderTableProps {
    orders: AdminOrder[];
}

export function OrderTable({ orders }: OrderTableProps) {
    return (
        <DataTable
            columns={columns}
            data={orders}
            emptyState={
                <EmptyState
                    title="No orders found"
                    description="Try a different filter. There is nothing useful in this result set."
                />
            }
        />
    );
}
