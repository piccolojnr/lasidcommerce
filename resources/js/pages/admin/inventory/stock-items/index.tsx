import { Link } from '@inertiajs/react';
import * as StockItemController from '@/actions/App/Http/Controllers/Admin/Inventory/StockItemController';
import * as StockMovementController from '@/actions/App/Http/Controllers/Admin/Inventory/StockMovementController';
import { PageHeader } from '@/components/shared/page-header/page-header';
import { StatusBadge } from '@/components/shared/status-badge/status-badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import {
    EMPTY_SENTINEL,
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useFilters } from '@/hooks/use-filters';
import { AdminLayout } from '@/layouts/app/admin-layout';
import { formatDate } from '@/lib/formatters/date';
import type { AdminInventoryListPage, AdminStockItemSummary } from '@/types/admin/inventory';
import type { PaginationLink } from '@/types/shared/pagination';

interface StockItemFilters {
    [key: string]: string | null;
    search: string | null;
    status: string | null;
}

interface Props {
    stockItems: AdminInventoryListPage<AdminStockItemSummary>;
    filters: StockItemFilters;
}

export default function StockItemIndexPage({ stockItems, filters }: Props) {
    const { search, setSearch, setFilter } = useFilters(StockItemController.index.url(), filters);
    const lowStockCount = stockItems.data.filter((item) => item.status === 'low_stock').length;
    const outOfStockCount = stockItems.data.filter((item) => item.status === 'out_of_stock').length;

    return (
        <AdminLayout title="Inventory" description="Track stock position, low-stock risk, and manual adjustments.">
            <div className="mx-auto w-full max-w-7xl space-y-8">
                <PageHeader
                    title="Stock Items"
                    description="Inspect on-hand, reserved, and available inventory across the catalog."
                    actions={
                        <div className="flex items-center gap-3">
                            <Link href={StockMovementController.index.url()} className="text-sm text-muted-foreground transition hover:text-foreground">
                                View movement ledger
                            </Link>
                            <Link href={StockItemController.index.url()} className="text-sm text-muted-foreground transition hover:text-foreground">
                                Reset filters
                            </Link>
                        </div>
                    }
                />

                <div className="grid gap-4 md:grid-cols-3">
                    <Card className="border-border/70">
                        <CardHeader className="pb-2"><CardTitle className="text-sm font-medium text-muted-foreground">Visible in this result</CardTitle></CardHeader>
                        <CardContent><div className="text-3xl font-semibold">{stockItems.data.length}</div><p className="text-sm text-muted-foreground">Stock items on the current page after filters.</p></CardContent>
                    </Card>
                    <Card className="border-border/70">
                        <CardHeader className="pb-2"><CardTitle className="text-sm font-medium text-muted-foreground">Low stock in this slice</CardTitle></CardHeader>
                        <CardContent><div className="text-3xl font-semibold">{lowStockCount}</div><p className="text-sm text-muted-foreground">Items at or below their reorder threshold.</p></CardContent>
                    </Card>
                    <Card className="border-border/70">
                        <CardHeader className="pb-2"><CardTitle className="text-sm font-medium text-muted-foreground">Out of stock in this slice</CardTitle></CardHeader>
                        <CardContent><div className="text-3xl font-semibold">{outOfStockCount}</div><p className="text-sm text-muted-foreground">Items with no available quantity left.</p></CardContent>
                    </Card>
                </div>

                <div className="rounded-[2rem] border border-border/70 bg-muted/25 p-5">
                    <p className="text-xs font-semibold uppercase tracking-[0.24em] text-muted-foreground">Inventory filter</p>
                    <p className="mt-1 text-sm text-muted-foreground">Search by product name, product SKU, variant name, or variant SKU and then narrow by stock risk.</p>
                    <div className="mt-4 flex flex-wrap items-center gap-3">
                        <Input
                            placeholder="Search product or SKU..."
                            value={search}
                            onChange={(event) => setSearch(event.target.value)}
                            className="h-11 w-80 bg-background"
                        />
                        <Select value={filters.status ?? EMPTY_SENTINEL} onValueChange={(value) => setFilter('status', value === EMPTY_SENTINEL ? null : value)}>
                            <SelectTrigger className="h-11 w-48 bg-background"><SelectValue placeholder="All stock states" /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value={EMPTY_SENTINEL}>All stock states</SelectItem>
                                <SelectItem value="in_stock">In stock</SelectItem>
                                <SelectItem value="low_stock">Low stock</SelectItem>
                                <SelectItem value="out_of_stock">Out of stock</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>

                <div className="overflow-hidden rounded-lg border bg-background">
                    <div className="overflow-x-auto">
                        <table className="min-w-full text-sm">
                            <thead className="bg-muted/40">
                                <tr>
                                    <th className="px-4 py-3 text-left font-medium text-muted-foreground">Item</th>
                                    <th className="px-4 py-3 text-left font-medium text-muted-foreground">Status</th>
                                    <th className="px-4 py-3 text-left font-medium text-muted-foreground">On hand</th>
                                    <th className="px-4 py-3 text-left font-medium text-muted-foreground">Reserved</th>
                                    <th className="px-4 py-3 text-left font-medium text-muted-foreground">Available</th>
                                    <th className="px-4 py-3 text-left font-medium text-muted-foreground">Reorder level</th>
                                    <th className="px-4 py-3 text-left font-medium text-muted-foreground">Updated</th>
                                    <th className="px-4 py-3 text-left font-medium text-muted-foreground">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {stockItems.data.map((item) => (
                                    <tr key={item.id} className="border-t align-top">
                                        <td className="px-4 py-4">
                                            <div className="space-y-1">
                                                <div className="font-medium">{item.product_name ?? 'Unknown product'}</div>
                                                <div className="font-mono text-xs text-muted-foreground">{item.product_sku ?? 'No SKU'}</div>
                                                {item.variant_name || item.variant_sku ? (
                                                    <div className="text-xs text-muted-foreground">
                                                        {item.variant_name ?? 'Variant'}{item.variant_sku ? ` • ${item.variant_sku}` : ''}
                                                    </div>
                                                ) : null}
                                            </div>
                                        </td>
                                        <td className="px-4 py-3 align-middle"><StatusBadge status={item.status} /></td>
                                        <td className="px-4 py-3 align-middle">{item.quantity_on_hand}</td>
                                        <td className="px-4 py-3 align-middle">{item.quantity_reserved}</td>
                                        <td className="px-4 py-3 align-middle font-semibold">{item.available_quantity}</td>
                                        <td className="px-4 py-3 align-middle">{item.reorder_level ?? '—'}</td>
                                        <td className="px-4 py-3 align-middle text-muted-foreground">{formatDate(item.updated_at)}</td>
                                        <td className="px-4 py-3 align-middle">
                                            <Link href={StockItemController.show.url(item.id)} className="text-sm font-medium text-primary transition hover:text-primary/80">
                                                View details
                                            </Link>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>

                {stockItems.last_page > 1 && (
                    <div className="flex items-center justify-center gap-1">
                        {stockItems.links.map((link: PaginationLink, index: number) =>
                            link.url ? (
                                <Link
                                    key={index}
                                    href={link.url}
                                    className={`rounded border px-3 py-1 text-sm ${link.active ? 'bg-primary text-primary-foreground' : 'hover:bg-muted'}`}
                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                />
                            ) : (
                                <span key={index} className="rounded border px-3 py-1 text-sm opacity-40" dangerouslySetInnerHTML={{ __html: link.label }} />
                            ),
                        )}
                    </div>
                )}
            </div>
        </AdminLayout>
    );
}
