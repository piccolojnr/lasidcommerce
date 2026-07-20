import { Form, Link } from '@inertiajs/react';
import { Boxes, ChevronDown, ChevronUp, Plus, Trash2 } from 'lucide-react';
import { useState } from 'react';
import * as ProductOptionTypeController from '@/actions/App/Http/Controllers/Admin/Catalog/ProductOptionTypeController';
import * as ProductOptionValueController from '@/actions/App/Http/Controllers/Admin/Catalog/ProductOptionValueController';
import * as ProductVariantController from '@/actions/App/Http/Controllers/Admin/Catalog/ProductVariantController';
import * as StockItemController from '@/actions/App/Http/Controllers/Admin/Inventory/StockItemController';
import { FieldError } from '@/components/shared/forms/field-error';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';
import { formatMoney } from '@/lib/formatters/money';
import type {
    AdminProduct,
    AdminProductOptionType,
    AdminProductVariant,
} from '@/types/admin/catalog';
import { centsToDisplay, displayToCents } from './product-form-utils';

function OptionTypeCard({ optionType }: { optionType: AdminProductOptionType }) {
    return (
        <Card className="border-border/70">
            <CardHeader className="flex flex-row items-start justify-between gap-4">
                <div className="flex flex-col gap-2">
                    <CardTitle className="text-base">{optionType.name}</CardTitle>
                    <div className="flex flex-wrap gap-2">
                        {optionType.values.length > 0 ? (
                            optionType.values.map((value) => (
                                <Badge key={value.id} variant="secondary">
                                    {value.value}
                                </Badge>
                            ))
                        ) : (
                            <span className="text-sm text-muted-foreground">No values yet</span>
                        )}
                    </div>
                </div>
                <Form
                    {...ProductOptionTypeController.destroy.form.delete(optionType)}
                    options={{ preserveScroll: true }}
                >
                    <Button type="submit" variant="ghost" size="icon">
                        <Trash2 data-icon="inline-start" />
                        <span className="sr-only">Delete option</span>
                    </Button>
                </Form>
            </CardHeader>
            <CardContent className="flex flex-col gap-3">
                {/* Rename option type */}
                <Form
                    {...ProductOptionTypeController.update.form.patch(optionType)}
                    options={{ preserveScroll: true }}
                    className="flex flex-col gap-2 sm:flex-row"
                >
                    {({ errors }) => (
                        <>
                            <div className="min-w-0 flex-1">
                                <Label htmlFor={`option-${optionType.id}`}>Option name</Label>
                                <Input
                                    id={`option-${optionType.id}`}
                                    name="name"
                                    defaultValue={optionType.name}
                                />
                                <FieldError message={errors.name} />
                            </div>
                            <Button type="submit" className="self-end">
                                Save
                            </Button>
                        </>
                    )}
                </Form>

                {/* Edit / delete existing values */}
                <div className="flex flex-col gap-2">
                    {optionType.values.map((value) => (
                        <div key={value.id} className="flex flex-col gap-2 sm:flex-row">
                            <Form
                                {...ProductOptionValueController.update.form.patch(value)}
                                options={{ preserveScroll: true }}
                                className="flex min-w-0 flex-1 flex-col gap-2 sm:flex-row"
                            >
                                {({ errors }) => (
                                    <>
                                        <div className="min-w-0 flex-1">
                                            <Input
                                                name="value"
                                                defaultValue={value.value}
                                                aria-label={`${optionType.name} value`}
                                            />
                                            <FieldError message={errors.value} />
                                        </div>
                                        <Button type="submit" variant="outline">
                                            Update
                                        </Button>
                                    </>
                                )}
                            </Form>
                            <Form
                                {...ProductOptionValueController.destroy.form.delete(value)}
                                options={{ preserveScroll: true }}
                            >
                                <Button type="submit" variant="ghost" size="icon">
                                    <Trash2 data-icon="inline-start" />
                                    <span className="sr-only">Delete value</span>
                                </Button>
                            </Form>
                        </div>
                    ))}
                </div>

                {/* Add new value */}
                <Form
                    {...ProductOptionValueController.store.form.post(optionType)}
                    options={{ preserveScroll: true }}
                    className="flex flex-col gap-2 rounded-lg border border-dashed border-border/70 p-3 sm:flex-row"
                >
                    {({ errors }) => (
                        <>
                            <div className="min-w-0 flex-1">
                                <Label htmlFor={`option-value-${optionType.id}`}>Add value</Label>
                                <Input
                                    id={`option-value-${optionType.id}`}
                                    name="value"
                                    placeholder="Small, Black, Cotton"
                                />
                                <FieldError message={errors.value} />
                            </div>
                            <Button type="submit" className="self-end">
                                <Plus data-icon="inline-start" />
                                Add
                            </Button>
                        </>
                    )}
                </Form>
            </CardContent>
        </Card>
    );
}

function VariantForm({
    product,
    variant,
}: {
    product: AdminProduct;
    variant?: AdminProductVariant;
}) {
    const [price, setPrice] = useState(centsToDisplay(variant?.price));
    const [compareAtPrice, setCompareAtPrice] = useState(
        centsToDisplay(variant?.compare_at_price),
    );
    const [costPrice, setCostPrice] = useState(centsToDisplay(variant?.cost_price));
    const [isActive, setIsActive] = useState(variant?.is_active ?? true);

    const formProps = variant
        ? ProductVariantController.update.form.patch(variant)
        : ProductVariantController.store.form.post(product);

    return (
        <Form
            {...formProps}
            options={{ preserveScroll: true }}
            className="flex flex-col gap-4 rounded-lg border border-border/70 p-4"
        >
            {({ errors }) => (
                <>
                    <input type="hidden" name="price" value={displayToCents(price)} />
                    <input
                        type="hidden"
                        name="compare_at_price"
                        value={displayToCents(compareAtPrice)}
                    />
                    <input type="hidden" name="cost_price" value={displayToCents(costPrice)} />
                    <input type="hidden" name="is_active" value={isActive ? '1' : '0'} />
                    {product.option_types.length > 0 && (
                        <input type="hidden" name="option_value_ids[]" value="" />
                    )}

                    <div className="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                        <div className="flex flex-col gap-2">
                            <Label htmlFor={`${variant?.id ?? 'new'}-name`}>Variant name</Label>
                            <Input
                                id={`${variant?.id ?? 'new'}-name`}
                                name="name"
                                defaultValue={variant?.name ?? ''}
                                placeholder="Black / Medium"
                            />
                            <FieldError message={errors.name} />
                        </div>
                        <div className="flex flex-col gap-2">
                            <Label htmlFor={`${variant?.id ?? 'new'}-sku`}>SKU</Label>
                            <Input
                                id={`${variant?.id ?? 'new'}-sku`}
                                name="sku"
                                defaultValue={variant?.sku ?? ''}
                                placeholder={`${product.sku}-BLK-M`}
                            />
                            <FieldError message={errors.sku} />
                        </div>
                        <div className="flex flex-col gap-2">
                            <Label>Price</Label>
                            <Input
                                type="number"
                                min="0"
                                step="0.01"
                                value={price}
                                placeholder={(product.base_price / 100).toFixed(2)}
                                onChange={(e) => setPrice(e.target.value)}
                            />
                            <FieldError message={errors.price} />
                        </div>
                        <div className="flex flex-col gap-2">
                            <Label>Compare-at</Label>
                            <Input
                                type="number"
                                min="0"
                                step="0.01"
                                value={compareAtPrice}
                                onChange={(e) => setCompareAtPrice(e.target.value)}
                            />
                            <FieldError message={errors.compare_at_price} />
                        </div>
                    </div>

                    <div className="grid gap-3 md:grid-cols-3">
                        <div className="flex flex-col gap-2">
                            <Label>Cost</Label>
                            <Input
                                type="number"
                                min="0"
                                step="0.01"
                                value={costPrice}
                                onChange={(e) => setCostPrice(e.target.value)}
                            />
                            <FieldError message={errors.cost_price} />
                        </div>
                        <div className="flex flex-col gap-2">
                            <Label htmlFor={`${variant?.id ?? 'new'}-barcode`}>Barcode</Label>
                            <Input
                                id={`${variant?.id ?? 'new'}-barcode`}
                                name="barcode"
                                defaultValue={variant?.barcode ?? ''}
                            />
                            <FieldError message={errors.barcode} />
                        </div>
                        <div className="flex flex-col gap-2">
                            <Label htmlFor={`${variant?.id ?? 'new'}-weight`}>Weight</Label>
                            <Input
                                id={`${variant?.id ?? 'new'}-weight`}
                                name="weight"
                                type="number"
                                min="0"
                                step="0.01"
                                defaultValue={variant?.weight ?? ''}
                            />
                            <FieldError message={errors.weight} />
                        </div>
                    </div>

                    {product.option_types.length > 0 && (
                        <div className="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                            {product.option_types.map((optionType) => (
                                <div
                                    key={optionType.id}
                                    className="flex flex-col gap-2 rounded-lg bg-muted/40 p-3"
                                >
                                    <p className="text-sm font-medium">{optionType.name}</p>
                                    <div className="flex flex-wrap gap-3">
                                        {optionType.values.map((value) => (
                                            <label
                                                key={value.id}
                                                className="flex items-center gap-2 text-sm"
                                            >
                                                <Checkbox
                                                    name="option_value_ids[]"
                                                    value={String(value.id)}
                                                    defaultChecked={variant?.option_value_ids.includes(
                                                        value.id,
                                                    )}
                                                />
                                                <span>{value.value}</span>
                                            </label>
                                        ))}
                                    </div>
                                </div>
                            ))}
                        </div>
                    )}
                    <FieldError message={errors.option_value_ids} />

                    <div className="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
                        <label className="flex items-center gap-2 text-sm">
                            <Checkbox
                                checked={isActive}
                                onCheckedChange={(v) => setIsActive(Boolean(v))}
                            />
                            <span>Active on storefront</span>
                        </label>
                        <div className="flex gap-2">
                            {variant?.inventory.primary_stock_item_id && (
                                <Button variant="outline" asChild>
                                    <Link
                                        href={StockItemController.show.url(
                                            variant.inventory.primary_stock_item_id,
                                        )}
                                    >
                                        Inventory
                                    </Link>
                                </Button>
                            )}
                            <Button type="submit">
                                {variant ? 'Save variant' : 'Create variant'}
                            </Button>
                        </div>
                    </div>
                </>
            )}
        </Form>
    );
}


/** A single collapsible existing-variant row. */
function VariantRow({
    product,
    variant,
}: {
    product: AdminProduct;
    variant: AdminProductVariant;
}) {
    const [open, setOpen] = useState(false);

    return (
        <div className="overflow-hidden rounded-lg border border-border/70">
            {/* Summary header — always visible */}
            <button
                type="button"
                onClick={() => setOpen((v) => !v)}
                className={cn(
                    'flex w-full items-center justify-between gap-3 px-4 py-3 text-left transition hover:bg-muted/40',
                    open && 'border-b border-border/70 bg-muted/30',
                )}
            >
                <div className="flex min-w-0 flex-wrap items-center gap-2">
                    <Badge variant={variant.is_active ? 'default' : 'secondary'}>
                        {variant.is_active ? 'Active' : 'Inactive'}
                    </Badge>
                    <span className="font-medium">{variant.name}</span>
                    <span className="font-mono text-xs text-muted-foreground">{variant.sku}</span>
                    {variant.option_values.map((v) => (
                        <Badge key={v.id} variant="outline" className="text-xs">
                            {v.option_type_name ? `${v.option_type_name}: ` : ''}
                            {v.value}
                        </Badge>
                    ))}
                </div>
                <div className="flex shrink-0 items-center gap-3">
                    <span className="text-sm font-semibold">
                        {formatMoney(variant.price ?? product.base_price)}
                    </span>
                    <span className="text-xs text-muted-foreground">
                        {variant.inventory.available_quantity} avail.
                    </span>
                    {open ? (
                        <ChevronUp className="size-4 text-muted-foreground" />
                    ) : (
                        <ChevronDown className="size-4 text-muted-foreground" />
                    )}
                </div>
            </button>

            {/* Expanded edit form + delete */}
            {open && (
                <div className="flex flex-col gap-3 p-4">
                    <VariantForm product={product} variant={variant} />
                    <Form
                        {...ProductVariantController.destroy.form.delete(variant)}
                        options={{ preserveScroll: true }}
                        className="self-start"
                    >
                        <Button type="submit" variant="outline">
                            <Trash2 data-icon="inline-start" />
                            Delete variant
                        </Button>
                    </Form>
                </div>
            )}
        </div>
    );
}

export function ProductVariantManager({ product }: { product: AdminProduct }) {
    return (
        <section className="flex flex-col gap-6">
            <div className="flex flex-col gap-2">
                <div className="flex items-center gap-2 text-sm text-muted-foreground">
                    <Boxes data-icon="inline-start" />
                    <span>Variant and option matrix</span>
                </div>
                <h2 className="text-2xl font-semibold tracking-tight">
                    Product options and variants
                </h2>
                <p className="max-w-3xl text-sm leading-6 text-muted-foreground">
                    Build option axes first, add their values, then assign values to each sellable
                    variant. Variant prices override the base product price when present.
                </p>
            </div>

            <div className="grid gap-6 xl:grid-cols-[0.9fr_1.4fr]">
                {/* Left: option types */}
                <div className="flex flex-col gap-4">
                    <Card className="border-border/70">
                        <CardHeader>
                            <CardTitle>Options</CardTitle>
                        </CardHeader>
                        <CardContent className="flex flex-col gap-4">
                            <Form
                                {...ProductOptionTypeController.store.form.post(product)}
                                options={{ preserveScroll: true }}
                                className="flex flex-col gap-2 sm:flex-row"
                            >
                                {({ errors }) => (
                                    <>
                                        <div className="min-w-0 flex-1">
                                            <Label htmlFor="new-option-name">New option</Label>
                                            <Input
                                                id="new-option-name"
                                                name="name"
                                                placeholder="Size, Color, Material"
                                            />
                                            <FieldError message={errors.name} />
                                        </div>
                                        <Button type="submit" className="self-end">
                                            <Plus data-icon="inline-start" />
                                            Add option
                                        </Button>
                                    </>
                                )}
                            </Form>

                            {product.option_types.length > 0 ? (
                                <div className="flex flex-col gap-4">
                                    {product.option_types.map((optionType) => (
                                        <OptionTypeCard key={optionType.id} optionType={optionType} />
                                    ))}
                                </div>
                            ) : (
                                <div className="rounded-lg border border-dashed border-border/70 p-4 text-sm text-muted-foreground">
                                    Add options such as size or color before creating a full variant
                                    matrix.
                                </div>
                            )}
                        </CardContent>
                    </Card>
                </div>

                {/* Right: variants */}
                <Card className="border-border/70">
                    <CardHeader>
                        <CardTitle>Variants</CardTitle>
                    </CardHeader>
                    <CardContent className="flex flex-col gap-4">
                        {/* New-variant form is always visible */}
                        <VariantForm product={product} />

                        {product.variants.length > 0 ? (
                            <div className="flex flex-col gap-2">
                                {product.variants.map((variant) => (
                                    <VariantRow
                                        key={variant.id}
                                        product={product}
                                        variant={variant}
                                    />
                                ))}
                            </div>
                        ) : (
                            <div className="rounded-lg border border-dashed border-border/70 p-4 text-sm text-muted-foreground">
                                No variants yet. Create one variant for each sellable SKU
                                combination.
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </section>
    );
}
