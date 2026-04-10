import { Button } from '@/components/ui/button';
import { DataTable, type DataTableColumn } from '@/components/shared/data-table/data-table';
import { DataTableToolbar } from '@/components/shared/data-table/data-table-toolbar';
import { EmptyState } from '@/components/shared/empty-state/empty-state';
import { PageHeader } from '@/components/shared/page-header/page-header';
import { StatusBadge } from '@/components/shared/status-badge/status-badge';
import { formatMoney } from '@/lib/formatters/money';
import { AdminLayout } from '@/layouts/app/admin-layout';
import type { AdminProduct } from '@/types/admin/catalog';

const products: AdminProduct[] = [
    { id: 1, name: 'Classic sneaker', slug: 'classic-sneaker', sku: 'SNK-001', status: 'draft', product_type: 'physical', base_price: 25000, is_featured: false },
];

const columns: DataTableColumn<AdminProduct>[] = [
    { key: 'name', title: 'Product' },
    { key: 'sku', title: 'SKU' },
    { key: 'product_type', title: 'Type' },
    { key: 'base_price', title: 'Price', render: (row) => formatMoney(row.base_price) },
    { key: 'status', title: 'Status', render: (row) => <StatusBadge status={row.status} /> },
];

export default function ProductIndexPage() {
    return (
        <AdminLayout title="Products" description="Manage product catalog entries and publishing state.">
            <div className="space-y-6">
                <PageHeader title="Products" description="Maintain the sellable catalog inventory." actions={<Button>Create product</Button>} />
                <DataTableToolbar />
                <DataTable
                    columns={columns}
                    data={products}
                    emptyState={<EmptyState title="No products yet" description="Create your first product to populate the catalog." />}
                />
            </div>
        </AdminLayout>
    );
}
