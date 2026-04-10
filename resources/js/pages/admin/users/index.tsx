import { DataTableToolbar } from '@/components/shared/data-table/data-table-toolbar';
import { PageHeader } from '@/components/shared/page-header/page-header';
import { AdminLayout } from '@/layouts/app/admin-layout';
import { UserTable } from '@/pages/admin/users/_components/user-table';

export default function UserIndexPage() {
    return (
        <AdminLayout title="Users" description="Review internal and customer-facing user accounts.">
            <div className="space-y-6">
                <PageHeader title="Users" description="Monitor account status and manage role assignment." />
                <DataTableToolbar searchPlaceholder="Search users..." />
                <UserTable />
            </div>
        </AdminLayout>
    );
}
