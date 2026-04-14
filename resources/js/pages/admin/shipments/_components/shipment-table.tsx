import { Link } from '@inertiajs/react';
import * as ShipmentController from '@/actions/App/Http/Controllers/Admin/Shipments/ShipmentController';
import { DataTable } from '@/components/shared/data-table/data-table';
import type { DataTableColumn } from '@/components/shared/data-table/data-table';
import { EmptyState } from '@/components/shared/empty-state/empty-state';
import { StatusBadge } from '@/components/shared/status-badge/status-badge';
import { formatDate } from '@/lib/formatters/date';
import type { AdminShipment } from '@/types/admin/shipment';

const columns: DataTableColumn<AdminShipment>[] = [
    {
        key: 'id',
        title: 'Shipment',
        render: (row) => (
            <Link
                href={ShipmentController.show.url(row)}
                className="font-medium hover:underline"
            >
                #{row.id}
            </Link>
        ),
    },
    {
        key: 'order',
        title: 'Order',
        render: (row) => row.order?.order_number ?? 'N/A',
    },
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
    shipments: AdminShipment[];
}

export function ShipmentTable({ shipments }: ShipmentTableProps) {
    return (
        <DataTable
            columns={columns}
            data={shipments}
            emptyState={
                <EmptyState
                    title="No shipments found"
                    description="Try a different search or status filter. Right now this list is empty."
                />
            }
        />
    );
}
