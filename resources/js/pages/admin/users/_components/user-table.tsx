import { DataTable, type DataTableColumn } from '@/components/shared/data-table/data-table';
import { StatusBadge } from '@/components/shared/status-badge/status-badge';
import type { AdminUser } from '@/types/admin/user';

const columns: DataTableColumn<AdminUser>[] = [
    { key: 'first_name', title: 'First name' },
    { key: 'last_name', title: 'Last name' },
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
