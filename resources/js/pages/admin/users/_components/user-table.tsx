import { DataTable } from '@/components/shared/data-table/data-table';
import type { DataTableColumn } from '@/components/shared/data-table/data-table';
import { StatusBadge } from '@/components/shared/status-badge/status-badge';
import type { AdminUser } from '@/types/admin/user';

const columns: DataTableColumn<AdminUser>[] = [
    { key: 'name', title: 'Name' },
    { key: 'email', title: 'Email' },
    {
        key: 'status',
        title: 'Status',
        render: (row) => <StatusBadge status={row.status} />,
    },
];

interface UserTableProps {
    data?: AdminUser[];
}

export function UserTable({ data = [] }: UserTableProps) {
    return <DataTable columns={columns} data={data} />;
}
