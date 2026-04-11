import { DataTable  } from '@/components/shared/data-table/data-table';
import type {DataTableColumn} from '@/components/shared/data-table/data-table';
import { StatusBadge } from '@/components/shared/status-badge/status-badge';
import { formatDate } from '@/lib/formatters/date';
import type { AdminShipment } from '@/types/admin/shipment';

const columns: DataTableColumn<AdminShipment>[] = [
    { key: 'id', title: 'Shipment' },
    {
        key: 'status',
        title: 'Status',
        render: (row) => <StatusBadge status={row.status} />,
    },
    { key: 'tracking_number', title: 'Tracking' },
    { key: 'carrier_name', title: 'Carrier' },
    {
        key: 'shipped_at',
        title: 'Shipped',
        render: (row) => formatDate(row.shipped_at),
    },
];

interface ShipmentTableProps {
    data?: AdminShipment[];
}

export function ShipmentTable({ data = [] }: ShipmentTableProps) {
    return <DataTable columns={columns} data={data} />;
}
