import { Link } from '@inertiajs/react';
import * as StockItemController from '@/actions/App/Http/Controllers/Admin/Inventory/StockItemController';
import * as StockMovementController from '@/actions/App/Http/Controllers/Admin/Inventory/StockMovementController';
import { PageHeader } from '@/components/shared/page-header/page-header';
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
import { formatStatus } from '@/lib/formatters/status';
import type { AdminInventoryListPage, AdminStockMovementSummary } from '@/types/admin/inventory';
import type { PaginationLink } from '@/types/shared/pagination';

interface StockMovementFilters {
    [key: string]: string | null;
    search: string | null;
    type: string | null;
}

interface Props {
    stockMovements: AdminInventoryListPage<AdminStockMovementSummary>;
    filters: StockMovementFilters;
    movementTypes: string[];
}

export default function StockMovementIndexPage({ stockMovements, filters, movementTypes }: Props) {
    const { search, setSearch, setFilter } = useFilters(StockMovementController.index.url(), filters);
    const positiveMoves = stockMovements.data.filter((movement) => movement.stock_delta > 0).length;
    const negativeMoves = stockMovements.data.filter((movement) => movement.stock_delta < 0).length;

    return (
        <AdminLayout title="Stock Movements" description="Review the append-only inventory ledger and trace manual adjustments.">
            <div className="mx-auto w-full max-w-7xl space-y-8">
                <PageHeader
                    title="Stock Movements"
                    description="Trace every recorded inventory adjustment with actor and reference context."
                    actions={
                        <div className="flex items-center gap-3">
                            <Link href={StockItemController.index.url()} className="text-sm text-muted-foreground transition hover:text-foreground">
                                View stock items
                            </Link>
                            <Link href={StockMovementController.index.url()} className="text-sm text-muted-foreground transition hover:text-foreground">
                                Reset filters
                            </Link>
                        </div>
                    }
                />

                <div className="grid gap-4 md:grid-cols-3">
                    <Card className="border-border/70">
                        <CardHeader className="pb-2"><CardTitle className="text-sm font-medium text-muted-foreground">Visible in this result</CardTitle></CardHeader>
                        <CardContent><div className="text-3xl font-semibold">{stockMovements.data.length}</div><p className="text-sm text-muted-foreground">Ledger entries on the current page after filters.</p></CardContent>
                    </Card>
                    <Card className="border-border/70">
                        <CardHeader className="pb-2"><CardTitle className="text-sm font-medium text-muted-foreground">Positive adjustments</CardTitle></CardHeader>
                        <CardContent><div className="text-3xl font-semibold">{positiveMoves}</div><p className="text-sm text-muted-foreground">Entries that increased quantity on hand.</p></CardContent>
                    </Card>
                    <Card className="border-border/70">
                        <CardHeader className="pb-2"><CardTitle className="text-sm font-medium text-muted-foreground">Negative adjustments</CardTitle></CardHeader>
                        <CardContent><div className="text-3xl font-semibold">{negativeMoves}</div><p className="text-sm text-muted-foreground">Entries that reduced quantity on hand.</p></CardContent>
                    </Card>
                </div>

                <div className="rounded-[2rem] border border-border/70 bg-muted/25 p-5">
                    <p className="text-xs font-semibold uppercase tracking-[0.24em] text-muted-foreground">Ledger filter</p>
                    <p className="mt-1 text-sm text-muted-foreground">Search by product, SKU, reference, or note and narrow by movement type.</p>
                    <div className="mt-4 flex flex-wrap items-center gap-3">
                        <Input
                            placeholder="Search product, SKU, reference, or note..."
                            value={search}
                            onChange={(event) => setSearch(event.target.value)}
                            className="h-11 w-80 bg-background"
                        />
                        <Select value={filters.type ?? EMPTY_SENTINEL} onValueChange={(value) => setFilter('type', value === EMPTY_SENTINEL ? null : value)}>
                            <SelectTrigger className="h-11 w-56 bg-background"><SelectValue placeholder="All movement types" /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value={EMPTY_SENTINEL}>All movement types</SelectItem>
                                {movementTypes.map((type) => <SelectItem key={type} value={type}>{formatStatus(type)}</SelectItem>)}
                            </SelectContent>
                        </Select>
                    </div>
                </div>

                <div className="overflow-hidden rounded-lg border bg-background">
                    <div className="overflow-x-auto">
                        <table className="min-w-full text-sm">
                            <thead className="bg-muted/40">
                                <tr>
                                    <th className="px-4 py-3 text-left font-medium text-muted-foreground">Movement</th>
                                    <th className="px-4 py-3 text-left font-medium text-muted-foreground">Product</th>
                                    <th className="px-4 py-3 text-left font-medium text-muted-foreground">Delta</th>
                                    <th className="px-4 py-3 text-left font-medium text-muted-foreground">Reference</th>
                                    <th className="px-4 py-3 text-left font-medium text-muted-foreground">Actor</th>
                                    <th className="px-4 py-3 text-left font-medium text-muted-foreground">Created</th>
                                    <th className="px-4 py-3 text-left font-medium text-muted-foreground">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {stockMovements.data.map((movement) => (
                                    <tr key={movement.id} className="border-t align-top">
                                        <td className="px-4 py-4">
                                            <div className="space-y-1">
                                                <div className="font-medium">{formatStatus(movement.type)}</div>
                                                <div className="text-xs text-muted-foreground">{movement.note ?? 'No note recorded'}</div>
                                            </div>
                                        </td>
                                        <td className="px-4 py-4">
                                            <div className="space-y-1">
                                                <div className="font-medium">{movement.product_name ?? 'Unknown product'}</div>
                                                <div className="font-mono text-xs text-muted-foreground">{movement.product_sku ?? 'No SKU'}</div>
                                                {movement.variant_name || movement.variant_sku ? (
                                                    <div className="text-xs text-muted-foreground">{movement.variant_name ?? 'Variant'}{movement.variant_sku ? ` • ${movement.variant_sku}` : ''}</div>
                                                ) : null}
                                            </div>
                                        </td>
                                        <td className={`px-4 py-3 align-middle font-semibold ${movement.stock_delta >= 0 ? 'text-emerald-600' : 'text-destructive'}`}>
                                            {movement.stock_delta >= 0 ? '+' : ''}{movement.stock_delta}
                                        </td>
                                        <td className="px-4 py-3 align-middle text-muted-foreground">
                                            {movement.reference_type && movement.reference_id ? `${movement.reference_type} #${movement.reference_id}` : '—'}
                                        </td>
                                        <td className="px-4 py-3 align-middle text-muted-foreground">{movement.creator_name ?? 'System'}</td>
                                        <td className="px-4 py-3 align-middle text-muted-foreground">{formatDate(movement.created_at)}</td>
                                        <td className="px-4 py-3 align-middle">
                                            <Link href={StockMovementController.show.url(movement.id)} className="text-sm font-medium text-primary transition hover:text-primary/80">
                                                View entry
                                            </Link>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>

                {stockMovements.last_page > 1 && (
                    <div className="flex items-center justify-center gap-1">
                        {stockMovements.links.map((link: PaginationLink, index: number) =>
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
