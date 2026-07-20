import { Link } from '@inertiajs/react';
import { ArrowDown, ArrowUp } from 'lucide-react';
import * as StockItemController from '@/actions/App/Http/Controllers/Admin/Inventory/StockItemController';
import * as StockMovementController from '@/actions/App/Http/Controllers/Admin/Inventory/StockMovementController';
import { DataTablePagination } from '@/components/shared/data-table/data-table-pagination';
import { PageHeader } from '@/components/shared/page-header/page-header';
import { Button } from '@/components/ui/button';
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
import type {
    AdminInventoryListPage,
    AdminStockMovementSummary,
} from '@/types/admin/inventory';

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

export default function StockMovementIndexPage({
    stockMovements,
    filters,
    movementTypes,
}: Props) {
    const { search, setSearch, setFilter } = useFilters(
        StockMovementController.index.url(),
        filters,
    );

    const positiveMoves = stockMovements.data.filter((m) => m.stock_delta > 0).length;
    const negativeMoves = stockMovements.data.filter((m) => m.stock_delta < 0).length;

    return (
        <AdminLayout
            title="Stock Movements"
            description="Review the append-only inventory ledger and trace manual adjustments."
        >
            <div className="mx-auto w-full max-w-7xl space-y-6">
                <PageHeader
                    title="Stock Movements"
                    description="Trace every recorded inventory adjustment with actor and reference context."
                    actions={
                        <div className="flex items-center gap-2">
                            <Button variant="outline" size="sm" asChild>
                                <Link href={StockItemController.index.url()}>
                                    View stock items
                                </Link>
                            </Button>
                            <Button variant="ghost" size="sm" asChild>
                                <Link href={StockMovementController.index.url()}>
                                    Reset filters
                                </Link>
                            </Button>
                        </div>
                    }
                />

                {/* Stat cards */}
                <div className="grid gap-4 md:grid-cols-3">
                    <Card className="border-border/70">
                        <CardHeader className="pb-1 pt-4">
                            <CardTitle className="text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                Visible in result
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="pb-4">
                            <p className="text-3xl font-semibold tabular-nums">
                                {stockMovements.total}
                            </p>
                            <p className="mt-0.5 text-xs text-muted-foreground">
                                Ledger entries matching the current filters.
                            </p>
                        </CardContent>
                    </Card>
                    <Card className="border-border/70">
                        <CardHeader className="pb-1 pt-4">
                            <CardTitle className="text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                Positive adjustments
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="pb-4">
                            <p className="text-3xl font-semibold tabular-nums">
                                {positiveMoves}
                            </p>
                            <p className="mt-0.5 text-xs text-muted-foreground">
                                Entries that increased quantity on hand.
                            </p>
                        </CardContent>
                    </Card>
                    <Card className="border-border/70">
                        <CardHeader className="pb-1 pt-4">
                            <CardTitle className="text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                Negative adjustments
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="pb-4">
                            <p className="text-3xl font-semibold tabular-nums">
                                {negativeMoves}
                            </p>
                            <p className="mt-0.5 text-xs text-muted-foreground">
                                Entries that reduced quantity on hand.
                            </p>
                        </CardContent>
                    </Card>
                </div>

                {/* Filter bar */}
                <div className="flex flex-wrap items-center gap-2">
                    <Input
                        placeholder="Search product, SKU, reference, or note…"
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        className="h-9 w-80"
                    />
                    <Select
                        value={filters.type ?? EMPTY_SENTINEL}
                        onValueChange={(v) =>
                            setFilter('type', v === EMPTY_SENTINEL ? null : v)
                        }
                    >
                        <SelectTrigger className="h-9 w-52">
                            <SelectValue placeholder="All movement types" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value={EMPTY_SENTINEL}>All movement types</SelectItem>
                            {movementTypes.map((type) => (
                                <SelectItem key={type} value={type}>
                                    {formatStatus(type)}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                </div>

                {/* Table + pagination */}
                <div className="overflow-hidden rounded-lg border bg-background">
                    <div className="overflow-x-auto">
                        <table className="min-w-full text-sm">
                            <thead>
                                <tr className="border-b bg-muted/40">
                                    <th className="px-4 py-3 text-left text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                        Movement
                                    </th>
                                    <th className="px-4 py-3 text-left text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                        Product
                                    </th>
                                    <th className="px-4 py-3 text-center text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                        Delta
                                    </th>
                                    <th className="px-4 py-3 text-left text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                        Reference
                                    </th>
                                    <th className="px-4 py-3 text-left text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                        Actor
                                    </th>
                                    <th className="px-4 py-3 text-left text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                        Created
                                    </th>
                                    <th className="px-3 py-3 text-right text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="divide-y">
                                {stockMovements.data.length === 0 ? (
                                    <tr>
                                        <td
                                            colSpan={7}
                                            className="px-4 py-14 text-center text-sm text-muted-foreground"
                                        >
                                            No movements match the current filters.
                                        </td>
                                    </tr>
                                ) : (
                                    stockMovements.data.map((movement) => (
                                        <tr
                                            key={movement.id}
                                            className="group transition-colors hover:bg-muted/30"
                                        >
                                            {/* Movement type + note */}
                                            <td className="px-4 py-3 align-middle">
                                                <p className="font-medium">
                                                    {formatStatus(movement.type)}
                                                </p>
                                                {movement.note && (
                                                    <p className="mt-0.5 max-w-xs truncate text-xs text-muted-foreground">
                                                        {movement.note}
                                                    </p>
                                                )}
                                            </td>

                                            {/* Product */}
                                            <td className="px-4 py-3 align-middle">
                                                <p className="font-medium">
                                                    {movement.product_name ?? 'Unknown product'}
                                                </p>
                                                <p className="font-mono text-xs text-muted-foreground">
                                                    {movement.product_sku ?? 'No SKU'}
                                                </p>
                                                {(movement.variant_name || movement.variant_sku) && (
                                                    <p className="text-xs text-muted-foreground">
                                                        {movement.variant_name ?? 'Variant'}
                                                        {movement.variant_sku
                                                            ? ` · ${movement.variant_sku}`
                                                            : ''}
                                                    </p>
                                                )}
                                            </td>

                                            {/* Delta pill */}
                                            <td className="px-4 py-3 text-center align-middle">
                                                <span
                                                    className={`inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold tabular-nums ${
                                                        movement.stock_delta > 0
                                                            ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400'
                                                            : movement.stock_delta < 0
                                                              ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'
                                                              : 'bg-muted text-muted-foreground'
                                                    }`}
                                                >
                                                    {movement.stock_delta > 0 ? (
                                                        <ArrowUp className="size-3" />
                                                    ) : movement.stock_delta < 0 ? (
                                                        <ArrowDown className="size-3" />
                                                    ) : null}
                                                    {movement.stock_delta > 0 ? '+' : ''}
                                                    {movement.stock_delta}
                                                </span>
                                            </td>

                                            {/* Reference */}
                                            <td className="px-4 py-3 align-middle text-sm text-muted-foreground">
                                                {movement.reference_type && movement.reference_id
                                                    ? `${movement.reference_type} #${movement.reference_id}`
                                                    : '—'}
                                            </td>

                                            {/* Actor */}
                                            <td className="px-4 py-3 align-middle text-sm text-muted-foreground">
                                                {movement.creator_name ?? 'System'}
                                            </td>

                                            {/* Created */}
                                            <td className="px-4 py-3 align-middle text-sm text-muted-foreground">
                                                {movement.created_at
                                                    ? formatDate(movement.created_at)
                                                    : '—'}
                                            </td>

                                            {/* Actions */}
                                            <td className="px-3 py-3 align-middle">
                                                <div className="flex justify-end">
                                                    <Button
                                                        variant="ghost"
                                                        size="sm"
                                                        className="h-8 text-xs"
                                                        asChild
                                                    >
                                                        <Link
                                                            href={StockMovementController.show.url(
                                                                movement.id,
                                                            )}
                                                        >
                                                            View entry
                                                        </Link>
                                                    </Button>
                                                </div>
                                            </td>
                                        </tr>
                                    ))
                                )}
                            </tbody>
                        </table>
                    </div>
                    <DataTablePagination meta={stockMovements} />
                </div>
            </div>
        </AdminLayout>
    );
}
