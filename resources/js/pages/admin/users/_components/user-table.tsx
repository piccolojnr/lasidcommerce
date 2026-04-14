import { Link } from '@inertiajs/react';
import * as UserController from '@/actions/App/Http/Controllers/Admin/Users/UserController';
import { DataTable } from '@/components/shared/data-table/data-table';
import type { DataTableColumn } from '@/components/shared/data-table/data-table';
import { EmptyState } from '@/components/shared/empty-state/empty-state';
import { StatusBadge } from '@/components/shared/status-badge/status-badge';
import { formatDate } from '@/lib/formatters/date';
import type { AdminUser } from '@/types/admin/user';

const columns: DataTableColumn<AdminUser>[] = [
    {
        key: 'name',
        title: 'Name',
        render: (row) => (
            <Link href={UserController.show.url(row)} className="font-medium hover:underline">
                {row.name}
            </Link>
        ),
    },
    { key: 'email', title: 'Email' },
    {
        key: 'roles',
        title: 'Roles',
        render: (row) => (row.roles.length > 0 ? row.roles.join(', ') : 'No roles'),
    },
    {
        key: 'status',
        title: 'Status',
        render: (row) => <StatusBadge status={row.status} />,
    },
    { key: 'orders_count', title: 'Orders' },
    {
        key: 'created_at',
        title: 'Created',
        render: (row) => formatDate(row.created_at),
    },
];

interface UserTableProps {
    users: AdminUser[];
}

export function UserTable({ users }: UserTableProps) {
    return (
        <DataTable
            columns={columns}
            data={users}
            emptyState={
                <EmptyState
                    title="No users found"
                    description="Try a different search, status, or role filter."
                />
            }
        />
    );
}
