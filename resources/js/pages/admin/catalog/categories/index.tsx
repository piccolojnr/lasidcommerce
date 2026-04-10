import { Button } from '@/components/ui/button';
import { DataTable, type DataTableColumn } from '@/components/shared/data-table/data-table';
import { DataTableToolbar } from '@/components/shared/data-table/data-table-toolbar';
import { EmptyState } from '@/components/shared/empty-state/empty-state';
import { PageHeader } from '@/components/shared/page-header/page-header';
import { StatusBadge } from '@/components/shared/status-badge/status-badge';
import { AdminLayout } from '@/layouts/app/admin-layout';
import type { AdminCategory } from '@/types/admin/catalog';

const categories: AdminCategory[] = [
    { id: 1, name: 'Footwear', slug: 'footwear', is_active: true, sort_order: 1, parent_name: null },
];

const columns: DataTableColumn<AdminCategory>[] = [
    { key: 'name', title: 'Name' },
    { key: 'slug', title: 'Slug' },
    { key: 'parent_name', title: 'Parent', render: (row) => row.parent_name ?? 'Root' },
    { key: 'sort_order', title: 'Order' },
    { key: 'is_active', title: 'Status', render: (row) => <StatusBadge status={row.is_active ? 'active' : 'inactive'} /> },
];

export default function CategoryIndexPage() {
    return (
        <AdminLayout title="Categories" description="Organize the storefront catalog tree.">
            <div className="space-y-6">
                <PageHeader
                    title="Categories"
                    description="Manage hierarchy, ordering, and active visibility."
                    actions={<Button>Create category</Button>}
                />
                <DataTableToolbar actions={<Button variant="outline">Filters</Button>} />
                <DataTable
                    columns={columns}
                    data={categories}
                    emptyState={<EmptyState title="No categories yet" description="Create a category to structure the catalog." />}
                />
            </div>
        </AdminLayout>
    );
}
