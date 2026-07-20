import { Link } from '@inertiajs/react';
import * as StockItemController from '@/actions/App/Http/Controllers/Admin/Inventory/StockItemController';
import * as StockMovementController from '@/actions/App/Http/Controllers/Admin/Inventory/StockMovementController';
import { DataTablePagination } from '@/components/shared/data-table/data-table-pagination';
import { PageHeader } from '@/components/shared/page-header/page-header';
import { StatusBadge } from '@/components/shared/status-badge/status-badge';
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
import type {
    AdminInventoryListPage,
    AdminStockItemSummary,
} from '@/types/admin/inventory';

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
    const { search, setSearch, setFilter } = useFilters(
        StockItemController.index.url(),
        filters,
    );

    const lowStockCount = stockItems.data.filter((i) => i.status === 'low_stock').length;
    const outOfStockCount = stockItems.data.filter((i) => i.status === 'out_of_stock').length;

    return (
        <AdminLayout
            title="Inventory"
            description="Track stock position, low-stock risk, and manual adjustments."
        >
            <div className="mx-auto w-full max-w-7xl space-y-6">
                <PageHeader
                    title="Stock Items"
                    description="Inspect on-hand, reserved, and available inventory across the catalog."
                    actions={
                        <div className="flex items-center gap-2">
                            <Button variant="outline" size="sm" asChild>
                                <Link href={StockMovementController.index.url()}>
                                    View movement ledger
                                </Link>
                            </Button>
                            <Button variant="ghost" size="sm" asChild>
                                <Link href={StockItemController.index.url()}>
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
                                {stockItems.total}
                            </p>
                            <p className="mt-0.5 text-xs text-muted-foreground">
                                Stock items matching the current filters.
                            </p>
                        </CardContent>
                    </Card>
                    <Card className="border-border/70">
                        <CardHeader className="pb-1 pt-4">
                            <CardTitle className="text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                Low stock on page
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="pb-4">
                            <p className="text-3xl font-semibold tabular-nums">
                                {lowStockCount}
                            </p>
                            <p className="mt-0.5 text-xs text-muted-foreground">
                                Items at or below their reorder threshold.
                            </p>
                        </CardContent>
                    </Card>
                    <Card className="border-border/70">
                        <CardHeader className="pb-1 pt-4">
                            <CardTitle className="text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                Out of stock on page
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="pb-4">
                            <p className="text-3xl font-semibold tabular-nums">
                                {outOfStockCount}
                            </p>
                            <p className="mt-0.5 text-xs text-muted-foreground">
                                Items with no available quantity left.
                            </p>
                        </CardContent>
                    </Card>
                </div>

                {/* Filter bar */}
                <div className="flex flex-wrap items-center gap-2">
                    <Input
                        placeholder="Search product or SKU…"
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        className="h-9 w-72"
                    />
                    <Select
                        value={filters.status ?? EMPTY_SENTINEL}
                        onValueChange={(v) =>
                            setFilter('status', v === EMPTY_SENTINEL ? null : v)
                        }
                    >
                        <SelectTrigger className="h-9 w-44">
                            <SelectValue placeholder="All stock states" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value={EMPTY_SENTINEL}>All stock states</SelectItem>
                            <SelectItem value="in_stock">In stock</SelectItem>
                            <SelectItem value="low_stock">Low stock</SelectItem>
                            <SelectItem value="out_of_stock">Out of stock</SelectItem>
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
                                        Item
                                    </th>
                                    <th className="px-4 py-3 text-left text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                        Status
                                    </th>
                                    <th className="px-4 py-3 text-right text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                        On hand
                                    </th>
                                    <th className="px-4 py-3 text-right text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                        Reserved
                                    </th>
                                    <th className="px-4 py-3 text-right text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                        Available
                                    </th>
                                    <th className="px-4 py-3 text-right text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                        Reorder level
                                    </th>
                                    <th className="px-4 py-3 text-left text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                        Updated
                                    </th>
                                    <th className="px-3 py-3 text-right text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="divide-y">
                                {stockItems.data.length === 0 ? (
                                    <tr>
                                        <td
                                            colSpan={8}
                                            className="px-4 py-14 text-center text-sm text-muted-foreground"
                                        >
                                            No stock items match the current filters.
                                        </td>
                                    </tr>
                                ) : (
                                    stockItems.data.map((item) => (
                                        <tr
                                            key={item.id}
                                            className="group transition-colors hover:bg-muted/30"
                                        >
                                            {/* Item */}
                                            <td className="px-4 py-3 align-middle">
                                                <p className="font-medium">
                                                    {item.product_name ?? 'Unknown product'}
                                                </p>
                                                <p className="font-mono text-xs text-muted-foreground">
                                                    {item.product_sku ?? 'No SKU'}
                                                </p>
                                                {(item.variant_name || item.variant_sku) && (
                                                    <p className="text-xs text-muted-foreground">
                                                        {item.variant_name ?? 'Variant'}
                                                        {item.variant_sku
                                                            ? ` · ${item.variant_sku}`
                                                            : ''}
                                                    </p>
                                                )}
                                            </td>

                                            {/* Status */}
                                            <td className="px-4 py-3 align-middle">
                                                <StatusBadge status={item.status} />
                                            </td>

                                            {/* On hand */}
                                            <td className="px-4 py-3 text-right align-middle tabular-nums">
                                                {item.quantity_on_hand}
                                            </td>

                                            {/* Reserved */}
                                            <td className="px-4 py-3 text-right align-middle tabular-nums text-muted-foreground">
                                                {item.quantity_reserved}
                                            </td>

                                            {/* Available */}
                                            <td className="px-4 py-3 text-right align-middle">
                                                <span className="font-semibold tabular-nums">
                                                    {item.available_quantity}
                                                </span>
                                            </td>

                                            {/* Reorder level */}
                                            <td className="px-4 py-3 text-right align-middle tabular-nums text-muted-foreground">
                                                {item.reorder_level ?? '—'}
                                            </td>

                                            {/* Updated */}
                                            <td className="px-4 py-3 align-middle text-sm text-muted-foreground">
                                                {item.updated_at ? formatDate(item.updated_at) : '—'}
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
                                                            href={StockItemController.show.url(
                                                                item.id,
                                                            )}
                                                        >
                                                            View details
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
                    <DataTablePagination meta={stockItems} />
                </div>
            </div>
        </AdminLayout>
    );
}
