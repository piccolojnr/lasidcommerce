import { router } from '@inertiajs/react';
import { Form } from '@inertiajs/react';
import { Check, RefreshCw, Trash2 } from 'lucide-react';
import { useState } from 'react';
import * as ProductVariantController from '@/actions/App/Http/Controllers/Admin/Catalog/ProductVariantController';
import * as ProductVariantMatrixController from '@/actions/App/Http/Controllers/Admin/Catalog/ProductVariantMatrixController';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { cn } from '@/lib/utils';
import { centsToDisplay, displayToCents } from './product-form-utils';
import type { AdminProductVariant } from '@/types/admin/catalog';

interface VariantMatrixProps {
    product: {
        id: number;
        sku: string;
        base_price: number;
        variants: AdminProductVariant[];
        option_types: { id: number; name: string; values: { id: number; value: string }[] }[];
    };
    canGenerate: boolean;
}

// ─── Single editable row ──────────────────────────────────────────────────────
// NOTE: No <form> element here — forms cannot be valid children of <tbody>.
// We use router.patch() imperatively to save changes.

function VariantRow({
    variant,
    basePrice,
}: {
    variant: AdminProductVariant;
    basePrice: number;
}) {
    const [sku, setSku] = useState(variant.sku);
    const [price, setPrice] = useState(centsToDisplay(variant.price));
    const [isActive, setIsActive] = useState(variant.is_active);
    const [dirty, setDirty] = useState(false);
    const [saving, setSaving] = useState(false);

    const markDirty = () => setDirty(true);

    const save = () => {
        setSaving(true);
        router.patch(
            ProductVariantController.update.url(variant),
            {
                name: variant.name,
                sku,
                price: displayToCents(price),
                is_active: isActive ? '1' : '0',
                option_value_ids: variant.option_value_ids,
            },
            {
                preserveScroll: true,
                onSuccess: () => {
                    setDirty(false);
                    setSaving(false);
                },
                onError: () => setSaving(false),
            },
        );
    };

    const destroy = () => {
        router.delete(ProductVariantController.destroy.url(variant), {
            preserveScroll: true,
        });
    };

    return (
        <tr className={cn('group border-b border-border/60 last:border-0', dirty && 'bg-primary/5')}>
            {/* Active toggle */}
            <td className="px-4 py-3 text-center">
                <Checkbox
                    checked={isActive}
                    onCheckedChange={(v) => {
                        setIsActive(Boolean(v));
                        markDirty();
                    }}
                />
            </td>

            {/* Variant name + option badges */}
            <td className="px-4 py-3">
                <div className="flex flex-col gap-1">
                    <span className={cn('text-sm font-medium', !isActive && 'text-muted-foreground line-through')}>
                        {variant.name}
                    </span>
                    <div className="flex flex-wrap gap-1">
                        {variant.option_values.map((ov) => (
                            <Badge key={ov.id} variant="outline" className="text-xs">
                                {ov.option_type_name ? `${ov.option_type_name}: ` : ''}
                                {ov.value}
                            </Badge>
                        ))}
                    </div>
                </div>
            </td>

            {/* SKU — inline edit */}
            <td className="px-4 py-3">
                <Input
                    value={sku}
                    onChange={(e) => { setSku(e.target.value); markDirty(); }}
                    className="h-8 w-36 rounded-lg font-mono text-xs"
                />
            </td>

            {/* Price — inline edit */}
            <td className="px-4 py-3">
                <Input
                    type="number"
                    min="0"
                    step="0.01"
                    value={price}
                    onChange={(e) => { setPrice(e.target.value); markDirty(); }}
                    placeholder={(basePrice / 100).toFixed(2)}
                    className="h-8 w-28 rounded-lg text-right text-xs"
                />
            </td>

            {/* Available qty */}
            <td className="px-4 py-3 text-right text-sm text-muted-foreground">
                {variant.inventory.available_quantity}
            </td>

            {/* Save + delete */}
            <td className="px-4 py-3">
                <div className="flex items-center justify-end gap-1">
                    {dirty && (
                        <Button
                            type="button"
                            size="sm"
                            disabled={saving}
                            onClick={save}
                            className="h-7 gap-1 px-2 text-xs"
                        >
                            <Check className="size-3" />
                            {saving ? '…' : 'Save'}
                        </Button>
                    )}
                    <Button
                        type="button"
                        size="icon"
                        variant="ghost"
                        onClick={destroy}
                        className="h-7 w-7 text-muted-foreground opacity-0 group-hover:opacity-100 hover:text-destructive"
                    >
                        <Trash2 className="size-3.5" />
                        <span className="sr-only">Delete variant</span>
                    </Button>
                </div>
            </td>
        </tr>
    );
}

// ─── Matrix table ─────────────────────────────────────────────────────────────

export function VariantMatrix({ product, canGenerate }: VariantMatrixProps) {
    const [selected, setSelected] = useState<Set<number>>(new Set());

    const allSelected =
        product.variants.length > 0 && selected.size === product.variants.length;

    const toggleAll = () =>
        setSelected(allSelected ? new Set() : new Set(product.variants.map((v) => v.id)));

    const bulkToggle = (isActive: boolean) => {
        router.patch(
            '/admin/catalog/variants/bulk-toggle',
            {
                variant_ids: Array.from(selected),
                is_active: isActive ? '1' : '0',
            },
            {
                preserveScroll: true,
                onSuccess: () => setSelected(new Set()),
            },
        );
    };

    return (
        <div className="flex flex-col gap-4">
            {/* Toolbar */}
            <div className="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p className="text-sm font-semibold">
                        {product.variants.length > 0
                            ? `${product.variants.length} variant${product.variants.length === 1 ? '' : 's'}`
                            : 'No variants yet'}
                    </p>
                    {canGenerate && product.variants.length === 0 && (
                        <p className="text-xs text-muted-foreground">
                            Generate all combinations from the options, or add manually.
                        </p>
                    )}
                </div>

                {canGenerate && (
                    <Form
                        {...ProductVariantMatrixController.generateMatrix.form.post(product)}
                        options={{ preserveScroll: true }}
                    >
                        {({ processing }) => (
                            <Button type="submit" disabled={processing}>
                                <RefreshCw className={cn('size-4 mr-1.5', processing && 'animate-spin')} />
                                {processing ? 'Generating…' : 'Generate matrix'}
                            </Button>
                        )}
                    </Form>
                )}
            </div>

            {/* Empty state */}
            {product.variants.length === 0 ? (
                <div className="rounded-xl border border-dashed border-border/70 py-12 text-center text-sm text-muted-foreground">
                    {canGenerate
                        ? 'Click "Generate matrix" to create all combinations automatically.'
                        : 'Define option types and values first, then generate the matrix.'}
                </div>
            ) : (
                <div className="overflow-x-auto rounded-xl border border-border/70">
                    <table className="w-full text-sm">
                        <thead>
                            <tr className="border-b border-border/70 bg-muted/40">
                                <th className="w-10 px-4 py-3 text-center">
                                    <Checkbox
                                        checked={allSelected}
                                        onCheckedChange={toggleAll}
                                        aria-label="Select all"
                                    />
                                </th>
                                <th className="px-4 py-3 text-left font-medium">Variant</th>
                                <th className="px-4 py-3 text-left font-medium">SKU</th>
                                <th className="px-4 py-3 text-left font-medium">Price</th>
                                <th className="px-4 py-3 text-right font-medium">Available</th>
                                <th className="px-4 py-3" />
                            </tr>
                        </thead>
                        <tbody>
                            {product.variants.map((variant) => (
                                <VariantRow
                                    key={variant.id}
                                    variant={variant}
                                    basePrice={product.base_price}
                                />
                            ))}
                        </tbody>
                    </table>
                </div>
            )}

            {/* Bulk action bar */}
            {selected.size > 0 && (
                <div className="flex flex-wrap items-center gap-3 rounded-xl border border-primary/30 bg-primary/5 px-4 py-3">
                    <span className="text-sm font-medium">{selected.size} selected</span>
                    <Button type="button" variant="outline" size="sm" onClick={() => bulkToggle(true)}>
                        Activate selected
                    </Button>
                    <Button type="button" variant="outline" size="sm" onClick={() => bulkToggle(false)}>
                        Deactivate selected
                    </Button>
                    <Button type="button" variant="ghost" size="sm" onClick={() => setSelected(new Set())}>
                        Clear
                    </Button>
                </div>
            )}
        </div>
    );
}
