import { Form } from '@inertiajs/react';
import { ArrowDown, ArrowUp, Minus, Plus } from 'lucide-react';
import { useState } from 'react';
import * as StockAdjustmentController from '@/actions/App/Http/Controllers/Admin/Inventory/StockAdjustmentController';
import * as StockItemController from '@/actions/App/Http/Controllers/Admin/Inventory/StockItemController';
import { FieldError } from '@/components/shared/forms/field-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { cn } from '@/lib/utils';
import type { AdminStockItemDetail } from '@/types/admin/catalog';

// Re-export so variant-matrix can import StockItemDetail from here without
// knowing the catalog type path.
export type { AdminStockItemDetail as StockItemDetail };

interface StockAdjustPanelProps {
    stockItem: AdminStockItemDetail;
    movementTypes: string[];
    /** Label shown above the panel — e.g. "Base product" or "Black / Medium" */
    label?: string;
    /** Compact mode: hides the movement history, shows only the form. Used in tables. */
    compact?: boolean;
}

// ─── Movement type labels ─────────────────────────────────────────────────────

const TYPE_META: Record<
    string,
    { label: string; delta: 1 | -1; color: string }
> = {
    restock: { label: 'Restock', delta: 1, color: 'text-emerald-600' },
    return: { label: 'Return', delta: 1, color: 'text-emerald-600' },
    correction_add: {
        label: 'Correction (add)',
        delta: 1,
        color: 'text-emerald-600',
    },
    damage: { label: 'Damage', delta: -1, color: 'text-red-500' },
    shrinkage: { label: 'Shrinkage', delta: -1, color: 'text-red-500' },
    correction_remove: {
        label: 'Correction (remove)',
        delta: -1,
        color: 'text-red-500',
    },
};

function typeLabel(type: string): string {
    return TYPE_META[type]?.label ?? type;
}

function deltaColor(delta: number): string {
    return delta > 0 ? 'text-emerald-600' : 'text-red-500';
}

function statusBadge(available: number, reorderLevel: number | null) {
    if (available <= 0) {
        return <Badge variant="destructive">Out of stock</Badge>;
    }
    if (reorderLevel != null && available <= reorderLevel) {
        return (
            <Badge className="border-amber-300 bg-amber-100 text-amber-800">
                Low stock
            </Badge>
        );
    }
    return (
        <Badge className="border-emerald-300 bg-emerald-100 text-emerald-800">
            In stock
        </Badge>
    );
}

// ─── Component ───────────────────────────────────────────────────────────────

export function StockAdjustPanel({
    stockItem,
    movementTypes,
    label,
    compact = false,
}: StockAdjustPanelProps) {
    const [type, setType] = useState(movementTypes[0] ?? 'restock');
    const [showForm, setShowForm] = useState(compact ? false : true);

    return (
        <div className="flex flex-col gap-4">
            {/* Stock level summary */}
            <div className="flex flex-wrap items-center justify-between gap-3">
                <div className="flex flex-wrap items-center gap-4 text-sm">
                    {label && (
                        <span className="font-medium text-foreground">
                            {label}
                        </span>
                    )}
                    <span className="text-muted-foreground">
                        <span className="font-semibold text-foreground">
                            {stockItem.available_quantity}
                        </span>{' '}
                        available
                    </span>
                    <span className="text-muted-foreground">
                        {stockItem.quantity_on_hand} on hand
                    </span>
                    {stockItem.quantity_reserved > 0 && (
                        <span className="text-muted-foreground">
                            {stockItem.quantity_reserved} reserved
                        </span>
                    )}
                    {stockItem.reorder_level != null && (
                        <span className="text-muted-foreground">
                            reorder at {stockItem.reorder_level}
                        </span>
                    )}
                    {statusBadge(
                        stockItem.available_quantity,
                        stockItem.reorder_level,
                    )}
                </div>

                <div className="flex items-center gap-2">
                    {compact && (
                        <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            onClick={() => setShowForm((v) => !v)}
                        >
                            {showForm ? 'Cancel' : 'Adjust'}
                        </Button>
                    )}
                    <Button variant="ghost" size="sm" asChild>
                        <a
                            href={StockItemController.show.url(stockItem)}
                            className="text-xs text-muted-foreground"
                        >
                            Full history ↗
                        </a>
                    </Button>
                </div>
            </div>

            {/* Adjustment form */}
            {showForm && (
                <Form
                    {...StockAdjustmentController.store.form.post(stockItem)}
                    options={{ preserveScroll: true }}
                    onSuccess={() => {
                        if (compact) setShowForm(false);
                    }}
                    className="rounded-xl border border-border/70 bg-muted/20 p-4"
                >
                    {({ errors, processing }) => (
                        <div className="flex flex-col gap-4">
                            <p className="text-xs font-semibold tracking-[0.18em] text-muted-foreground uppercase">
                                Record adjustment
                            </p>

                            <div className="grid gap-4 sm:grid-cols-[1fr_140px_minmax(0,1.5fr)]">
                                {/* Type */}
                                <div className="flex flex-col gap-1.5">
                                    <Label>Type</Label>
                                    <Select
                                        value={type}
                                        onValueChange={setType}
                                    >
                                        <SelectTrigger className="h-9 rounded-lg">
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {movementTypes.map((t) => (
                                                <SelectItem key={t} value={t}>
                                                    {typeLabel(t)}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    <input
                                        type="hidden"
                                        name="type"
                                        value={type}
                                    />
                                    <FieldError message={errors.type} />
                                </div>

                                {/* Quantity */}
                                <div className="flex flex-col gap-1.5">
                                    <Label htmlFor={`qty-${stockItem.id}`}>
                                        Quantity
                                    </Label>
                                    <Input
                                        id={`qty-${stockItem.id}`}
                                        name="quantity"
                                        type="number"
                                        min="1"
                                        defaultValue={1}
                                        className="h-9 rounded-lg"
                                    />
                                    <FieldError message={errors.quantity} />
                                </div>

                                {/* Note */}
                                <div className="flex flex-col gap-1.5">
                                    <Label htmlFor={`note-${stockItem.id}`}>
                                        Note (optional)
                                    </Label>
                                    <Input
                                        id={`note-${stockItem.id}`}
                                        name="note"
                                        placeholder="e.g. Delivery from supplier #42"
                                        className="h-9 rounded-lg"
                                    />
                                    <FieldError message={errors.note} />
                                </div>
                            </div>

                            {/* Direction indicator */}
                            <div className="flex items-center justify-between gap-3">
                                <div
                                    className={cn(
                                        'flex items-center gap-1.5 text-sm font-medium',
                                        TYPE_META[type]?.delta === 1
                                            ? 'text-emerald-600'
                                            : 'text-red-500',
                                    )}
                                >
                                    {TYPE_META[type]?.delta === 1 ? (
                                        <Plus className="size-3.5" />
                                    ) : (
                                        <Minus className="size-3.5" />
                                    )}
                                    {TYPE_META[type]?.delta === 1
                                        ? 'Adds to'
                                        : 'Removes from'}{' '}
                                    stock
                                </div>
                                <Button
                                    type="submit"
                                    size="sm"
                                    disabled={processing}
                                >
                                    {processing ? 'Saving…' : 'Record'}
                                </Button>
                            </div>
                        </div>
                    )}
                </Form>
            )}

            {/* Movement history — hidden in compact mode */}
            {!compact && stockItem.movements.length > 0 && (
                <div className="flex flex-col gap-1">
                    <p className="text-xs font-semibold tracking-[0.18em] text-muted-foreground uppercase">
                        Recent movements
                    </p>
                    <div className="overflow-hidden rounded-xl border border-border/70">
                        <table className="w-full text-sm">
                            <thead>
                                <tr className="border-b border-border/60 bg-muted/40">
                                    <th className="px-4 py-2.5 text-left font-medium">
                                        Type
                                    </th>
                                    <th className="px-4 py-2.5 text-right font-medium">
                                        Change
                                    </th>
                                    <th className="px-4 py-2.5 text-left font-medium">
                                        Note
                                    </th>
                                    <th className="px-4 py-2.5 text-left font-medium">
                                        By
                                    </th>
                                    <th className="px-4 py-2.5 text-right font-medium">
                                        When
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                {stockItem.movements.map((m) => (
                                    <tr
                                        key={m.id}
                                        className="border-b border-border/40 last:border-0"
                                    >
                                        <td className="px-4 py-2.5">
                                            <span className="text-xs font-medium text-foreground">
                                                {typeLabel(m.type)}
                                            </span>
                                        </td>
                                        <td
                                            className={cn(
                                                'px-4 py-2.5 text-right font-semibold',
                                                deltaColor(m.stock_delta),
                                            )}
                                        >
                                            <span className="flex items-center justify-end gap-0.5">
                                                {m.stock_delta > 0 ? (
                                                    <ArrowUp className="size-3" />
                                                ) : (
                                                    <ArrowDown className="size-3" />
                                                )}
                                                {Math.abs(m.stock_delta)}
                                            </span>
                                        </td>
                                        <td className="px-4 py-2.5 text-muted-foreground">
                                            {m.note ?? '—'}
                                        </td>
                                        <td className="px-4 py-2.5 text-muted-foreground">
                                            {m.creator_name ?? '—'}
                                        </td>
                                        <td className="px-4 py-2.5 text-right text-muted-foreground">
                                            {m.created_at
                                                ? new Date(
                                                      m.created_at,
                                                  ).toLocaleDateString()
                                                : '—'}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>
            )}

            {!compact && stockItem.movements.length === 0 && (
                <p className="text-sm text-muted-foreground">
                    No movements recorded yet.
                </p>
            )}
        </div>
    );
}
