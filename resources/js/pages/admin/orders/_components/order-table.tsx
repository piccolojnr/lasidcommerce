import { DataTable  } from '@/components/shared/data-table/data-table';
import type {DataTableColumn} from '@/components/shared/data-table/data-table';
import { StatusBadge } from '@/components/shared/status-badge/status-badge';
import { formatMoney } from '@/lib/formatters/money';
import type { AdminOrder } from '@/types/admin/order';

const columns: DataTableColumn<AdminOrder>[] = [
    { key: 'order_number', title: 'Order' },
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
        render: (row) => formatMoney(row.total_amount),
    },
];

interface OrderTableProps {
    data?: AdminOrder[];
}

export function OrderTable({ data = [] }: OrderTableProps) {
    return <DataTable columns={columns} data={data} />;
}
