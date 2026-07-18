import { Link, useForm } from '@inertiajs/react';
import * as ProductController from '@/actions/App/Http/Controllers/Admin/Catalog/ProductController';
import * as StockAdjustmentController from '@/actions/App/Http/Controllers/Admin/Inventory/StockAdjustmentController';
import * as StockItemController from '@/actions/App/Http/Controllers/Admin/Inventory/StockItemController';
import * as StockMovementController from '@/actions/App/Http/Controllers/Admin/Inventory/StockMovementController';
import { FieldError } from '@/components/shared/forms/field-error';
import { PageHeader } from '@/components/shared/page-header/page-header';
import { StatusBadge } from '@/components/shared/status-badge/status-badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { AdminLayout } from '@/layouts/app/admin-layout';
import { formatDate } from '@/lib/formatters/date';
import { formatStatus } from '@/lib/formatters/status';
import type { AdminStockItemDetail } from '@/types/admin/inventory';

interface Props {
    stockItem: AdminStockItemDetail;
    movementTypes: string[];
}

export default function StockItemShowPage({ stockItem, movementTypes }: Props) {
    const reorderForm = useForm({
        reorder_level: stockItem.reorder_level?.toString() ?? '',
    });
    const adjustmentForm = useForm({
        type: movementTypes[0] ?? '',
        quantity: '1',
        reference_type: '',
        reference_id: '',
        note: '',
    });

    const submitReorderLevel = () => {
        reorderForm.transform((data) => ({
            reorder_level:
                data.reorder_level === '' ? null : Number(data.reorder_level),
        }));
        reorderForm.put(StockItemController.update.url(stockItem.id), {
            preserveScroll: true,
        });
    };

    const submitAdjustment = () => {
        adjustmentForm.transform((data) => ({
            ...data,
            quantity: Number(data.quantity),
            reference_id:
                data.reference_id === '' ? null : Number(data.reference_id),
            reference_type: data.reference_type || null,
            note: data.note || null,
        }));
        adjustmentForm.post(StockAdjustmentController.store.url(stockItem.id), {
            preserveScroll: true,
        });
    };

    return (
        <AdminLayout
            title="Stock Item Details"
            description="Review stock position, reorder threshold, and movement history."
        >
            <div className="mx-auto w-full max-w-7xl space-y-8">
                <PageHeader
                    title={stockItem.product_name ?? 'Stock item'}
                    description={`Available quantity: ${stockItem.available_quantity}.`}
                    actions={
                        <div className="flex items-center gap-2">
                            <Button variant="outline" asChild>
                                <Link href={StockItemController.index.url()}>
                                    Back to inventory
                                </Link>
                            </Button>
                            <Button variant="outline" asChild>
                                <Link
                                    href={StockMovementController.index.url()}
                                >
                                    View ledger
                                </Link>
                            </Button>
                            {stockItem.product_id ? (
                                <Button asChild>
                                    <Link
                                        href={ProductController.show.url(
                                            stockItem.product_id,
                                        )}
                                    >
                                        View product
                                    </Link>
                                </Button>
                            ) : null}
                        </div>
                    }
                />

                <div className="grid gap-4 lg:grid-cols-[1.2fr_0.8fr]">
                    <Card className="border-border/70 bg-muted/30">
                        <CardHeader className="space-y-3">
                            <div className="flex items-center justify-between gap-3">
                                <p className="text-xs font-semibold tracking-[0.24em] text-muted-foreground uppercase">
                                    Stock position
                                </p>
                                <StatusBadge status={stockItem.status} />
                            </div>
                            <CardTitle className="text-2xl">
                                {stockItem.product_sku ??
                                    `Stock item #${stockItem.id}`}
                            </CardTitle>
                            <p className="text-sm leading-6 text-muted-foreground">
                                {stockItem.variant_name ??
                                    'Base product inventory'}
                                {stockItem.variant_sku
                                    ? ` • ${stockItem.variant_sku}`
                                    : ''}
                            </p>
                        </CardHeader>
                        <CardContent className="grid gap-4 border-t border-border/70 pt-6 md:grid-cols-4">
                            <div>
                                <p className="text-xs font-semibold tracking-[0.2em] text-muted-foreground uppercase">
                                    On hand
                                </p>
                                <p className="mt-2 font-semibold">
                                    {stockItem.quantity_on_hand}
                                </p>
                            </div>
                            <div>
                                <p className="text-xs font-semibold tracking-[0.2em] text-muted-foreground uppercase">
                                    Reserved
                                </p>
                                <p className="mt-2 font-semibold">
                                    {stockItem.quantity_reserved}
                                </p>
                            </div>
                            <div>
                                <p className="text-xs font-semibold tracking-[0.2em] text-muted-foreground uppercase">
                                    Available
                                </p>
                                <p className="mt-2 font-semibold">
                                    {stockItem.available_quantity}
                                </p>
                            </div>
                            <div>
                                <p className="text-xs font-semibold tracking-[0.2em] text-muted-foreground uppercase">
                                    Reorder level
                                </p>
                                <p className="mt-2 font-semibold">
                                    {stockItem.reorder_level ?? '—'}
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <Card className="border-border/70 bg-primary/5">
                        <CardHeader>
                            <CardTitle className="text-xl">
                                Inventory metadata
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-3 text-sm">
                            <div className="flex items-center justify-between">
                                <span className="text-muted-foreground">
                                    Product
                                </span>
                                <span className="font-medium">
                                    {stockItem.product_name ??
                                        'Unknown product'}
                                </span>
                            </div>
                            <div className="flex items-center justify-between">
                                <span className="text-muted-foreground">
                                    Product SKU
                                </span>
                                <span className="font-mono text-xs">
                                    {stockItem.product_sku ?? 'N/A'}
                                </span>
                            </div>
                            <div className="flex items-center justify-between">
                                <span className="text-muted-foreground">
                                    Variant
                                </span>
                                <span className="font-medium">
                                    {stockItem.variant_name ?? 'Base product'}
                                </span>
                            </div>
                            <div className="flex items-center justify-between">
                                <span className="text-muted-foreground">
                                    Last updated
                                </span>
                                <span className="font-medium">
                                    {formatDate(stockItem.updated_at)}
                                </span>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <div className="grid gap-6 xl:grid-cols-[0.8fr_1.2fr]">
                    <div className="space-y-6">
                        <Card className="overflow-hidden border-border/70 pt-0">
                            <CardHeader className="border-b border-border/70 bg-muted/30 py-6">
                                <CardTitle>Reorder controls</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4 p-6">
                                <div className="rounded-2xl border border-border/70 bg-muted/20 p-4 text-sm text-muted-foreground">
                                    Reorder level is metadata only. Use a stock
                                    adjustment below to change on-hand quantity
                                    and keep the movement ledger intact.
                                </div>
                                <form
                                    className="space-y-4"
                                    onSubmit={(event) => {
                                        event.preventDefault();
                                        submitReorderLevel();
                                    }}
                                >
                                    <div className="space-y-2">
                                        <Label htmlFor="reorder_level">
                                            Reorder level
                                        </Label>
                                        <Input
                                            id="reorder_level"
                                            type="number"
                                            min="0"
                                            value={
                                                reorderForm.data.reorder_level
                                            }
                                            onChange={(event) =>
                                                reorderForm.setData(
                                                    'reorder_level',
                                                    event.target.value,
                                                )
                                            }
                                        />
                                        <FieldError
                                            message={
                                                reorderForm.errors.reorder_level
                                            }
                                        />
                                    </div>
                                    <Button
                                        type="submit"
                                        disabled={reorderForm.processing}
                                        className="w-full"
                                    >
                                        {reorderForm.processing
                                            ? 'Saving…'
                                            : 'Save reorder level'}
                                    </Button>
                                </form>
                            </CardContent>
                        </Card>

                        <Card className="overflow-hidden border-border/70 pt-0">
                            <CardHeader className="border-b border-border/70 bg-muted/30 py-6">
                                <CardTitle>Manual adjustment</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4 p-6">
                                <form
                                    className="space-y-4"
                                    onSubmit={(event) => {
                                        event.preventDefault();
                                        submitAdjustment();
                                    }}
                                >
                                    <div className="space-y-2">
                                        <Label htmlFor="type">
                                            Movement type
                                        </Label>
                                        <Select
                                            value={adjustmentForm.data.type}
                                            onValueChange={(value) =>
                                                adjustmentForm.setData(
                                                    'type',
                                                    value,
                                                )
                                            }
                                        >
                                            <SelectTrigger id="type">
                                                <SelectValue placeholder="Select a movement type" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {movementTypes.map((type) => (
                                                    <SelectItem
                                                        key={type}
                                                        value={type}
                                                    >
                                                        {formatStatus(type)}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        <FieldError
                                            message={adjustmentForm.errors.type}
                                        />
                                    </div>
                                    <div className="space-y-2">
                                        <Label htmlFor="quantity">
                                            Quantity
                                        </Label>
                                        <Input
                                            id="quantity"
                                            type="number"
                                            min="1"
                                            value={adjustmentForm.data.quantity}
                                            onChange={(event) =>
                                                adjustmentForm.setData(
                                                    'quantity',
                                                    event.target.value,
                                                )
                                            }
                                        />
                                        <FieldError
                                            message={
                                                adjustmentForm.errors.quantity
                                            }
                                        />
                                    </div>
                                    <div className="grid gap-4 md:grid-cols-2">
                                        <div className="space-y-2">
                                            <Label htmlFor="reference_type">
                                                Reference type
                                            </Label>
                                            <Input
                                                id="reference_type"
                                                value={
                                                    adjustmentForm.data
                                                        .reference_type
                                                }
                                                onChange={(event) =>
                                                    adjustmentForm.setData(
                                                        'reference_type',
                                                        event.target.value,
                                                    )
                                                }
                                                placeholder="App\\Models\\Product"
                                            />
                                            <FieldError
                                                message={
                                                    adjustmentForm.errors
                                                        .reference_type
                                                }
                                            />
                                        </div>
                                        <div className="space-y-2">
                                            <Label htmlFor="reference_id">
                                                Reference ID
                                            </Label>
                                            <Input
                                                id="reference_id"
                                                type="number"
                                                min="1"
                                                value={
                                                    adjustmentForm.data
                                                        .reference_id
                                                }
                                                onChange={(event) =>
                                                    adjustmentForm.setData(
                                                        'reference_id',
                                                        event.target.value,
                                                    )
                                                }
                                                placeholder="123"
                                            />
                                            <FieldError
                                                message={
                                                    adjustmentForm.errors
                                                        .reference_id
                                                }
                                            />
                                        </div>
                                    </div>
                                    <div className="space-y-2">
                                        <Label htmlFor="note">Note</Label>
                                        <Input
                                            id="note"
                                            value={adjustmentForm.data.note}
                                            onChange={(event) =>
                                                adjustmentForm.setData(
                                                    'note',
                                                    event.target.value,
                                                )
                                            }
                                            placeholder="Operational context for this movement"
                                        />
                                        <FieldError
                                            message={adjustmentForm.errors.note}
                                        />
                                    </div>
                                    <Button
                                        type="submit"
                                        disabled={adjustmentForm.processing}
                                        className="w-full"
                                    >
                                        {adjustmentForm.processing
                                            ? 'Recording…'
                                            : 'Record stock adjustment'}
                                    </Button>
                                </form>
                            </CardContent>
                        </Card>
                    </div>

                    <div className="space-y-6">
                        <Card className="overflow-hidden border-border/70 pt-0">
                            <CardHeader className="border-b border-border/70 bg-muted/30 py-6">
                                <CardTitle>Movement history</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-3 p-6">
                                {stockItem.movements.length > 0 ? (
                                    stockItem.movements.map((movement) => (
                                        <div
                                            key={movement.id}
                                            className="rounded-2xl border border-border/70 bg-background/80 p-4"
                                        >
                                            <div className="flex items-start justify-between gap-3">
                                                <div>
                                                    <div className="flex items-center gap-2">
                                                        <StatusBadge
                                                            status={
                                                                movement.stock_delta >=
                                                                0
                                                                    ? 'in_stock'
                                                                    : 'attention_required'
                                                            }
                                                        />
                                                        <Link
                                                            href={StockMovementController.show.url(
                                                                movement.id,
                                                            )}
                                                            className="font-medium text-primary transition hover:text-primary/80"
                                                        >
                                                            {formatStatus(
                                                                movement.type,
                                                            )}
                                                        </Link>
                                                    </div>
                                                    <p className="mt-2 text-sm text-muted-foreground">
                                                        {movement.note ??
                                                            'No note recorded.'}
                                                    </p>
                                                    <p className="mt-2 text-xs text-muted-foreground">
                                                        {movement.creator_name ??
                                                            'System'}{' '}
                                                        •{' '}
                                                        {formatDate(
                                                            movement.created_at,
                                                        )}
                                                    </p>
                                                </div>
                                                <div
                                                    className={`text-right text-sm font-semibold ${movement.stock_delta >= 0 ? 'text-emerald-600' : 'text-destructive'}`}
                                                >
                                                    {movement.stock_delta >= 0
                                                        ? '+'
                                                        : ''}
                                                    {movement.stock_delta}
                                                </div>
                                            </div>
                                        </div>
                                    ))
                                ) : (
                                    <p className="text-sm text-muted-foreground">
                                        No stock movements have been recorded
                                        for this item yet.
                                    </p>
                                )}
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
