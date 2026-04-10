import { Button } from '@/components/ui/button';
import { DataTable, type DataTableColumn } from '@/components/shared/data-table/data-table';
import { DataTableToolbar } from '@/components/shared/data-table/data-table-toolbar';
import { EmptyState } from '@/components/shared/empty-state/empty-state';
import { PageHeader } from '@/components/shared/page-header/page-header';
import { StatusBadge } from '@/components/shared/status-badge/status-badge';
import { AdminLayout } from '@/layouts/app/admin-layout';
import type { AdminBrand } from '@/types/admin/catalog';

const brands: AdminBrand[] = [{ id: 1, name: 'Acme', slug: 'acme', is_active: true }];

const columns: DataTableColumn<AdminBrand>[] = [
    { key: 'name', title: 'Name' },
    { key: 'slug', title: 'Slug' },
    { key: 'is_active', title: 'Status', render: (row) => <StatusBadge status={row.is_active ? 'active' : 'inactive'} /> },
];

export default function BrandIndexPage() {
    return (
        <AdminLayout title="Brands" description="Manage brand metadata and storefront visibility.">
            <div className="space-y-6">
                <PageHeader title="Brands" description="Maintain the brand directory used across the catalog." actions={<Button>Create brand</Button>} />
                <DataTableToolbar />
                <DataTable
                    columns={columns}
                    data={brands}
                    emptyState={<EmptyState title="No brands yet" description="Create a brand to group products under a supplier or label." />}
                />
            </div>
        </AdminLayout>
    );
}
