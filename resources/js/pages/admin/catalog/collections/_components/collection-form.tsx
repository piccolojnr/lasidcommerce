import { Form } from '@inertiajs/react';
import { Check, GripVertical, Search, X } from 'lucide-react';
import { useMemo, useState } from 'react';
import * as CollectionController from '@/actions/App/Http/Controllers/Admin/Catalog/CollectionController';
import { FieldError } from '@/components/shared/forms/field-error';
import { FormActions } from '@/components/shared/forms/form-actions';
import { FormSection } from '@/components/shared/forms/form-section';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    EMPTY_SENTINEL,
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import type { AdminCollection } from '@/types/admin/catalog';

// ─── Types ────────────────────────────────────────────────────────────────────

interface ProductOption {
    id: number;
    name: string;
    sku: string;
    status: string;
}

// ─── Helpers ──────────────────────────────────────────────────────────────────

const textareaClassName =
    'flex min-h-[120px] w-full rounded-xl border border-input bg-background px-4 py-3 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50';

function slugify(value: string): string {
    return value
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-');
}

// ─── Product Picker ───────────────────────────────────────────────────────────

/**
 * selectedProducts: map of product-id (string) → sort_order (string)
 */
interface ProductPickerProps {
    products: ProductOption[];
    selectedProducts: Record<string, string>;
    onToggle: (productId: number) => void;
    onSortOrderChange: (productId: number, value: string) => void;
    error?: string;
}

function ProductPicker({
    products,
    selectedProducts,
    onToggle,
    onSortOrderChange,
    error,
}: ProductPickerProps) {
    const [search, setSearch] = useState('');
    const [statusFilter, setStatusFilter] = useState<string>(EMPTY_SENTINEL);

    const filteredProducts = useMemo(() => {
        const q = search.toLowerCase().trim();

        return products.filter((p) => {
            const matchesSearch =
                q === '' ||
                p.name.toLowerCase().includes(q) ||
                p.sku.toLowerCase().includes(q);
            const matchesStatus =
                statusFilter === EMPTY_SENTINEL || p.status === statusFilter;

            return matchesSearch && matchesStatus;
        });
    }, [products, search, statusFilter]);

    // Selected entries sorted by sort_order for the right panel
    const selectedEntries = useMemo(() => {
        return Object.entries(selectedProducts)
            .map(([id, order]) => ({
                id: Number(id),
                sortOrder: order,
                product: products.find((p) => p.id === Number(id)),
            }))
            .filter((e) => e.product !== undefined)
            .sort((a, b) => Number(a.sortOrder) - Number(b.sortOrder));
    }, [selectedProducts, products]);

    return (
        <div className="space-y-4">
            {/* Hidden fields for form submission */}
            {selectedEntries.map((entry, index) => (
                <div key={entry.id}>
                    <input
                        type="hidden"
                        name={`product_memberships[${index}][product_id]`}
                        value={entry.id}
                    />
                    <input
                        type="hidden"
                        name={`product_memberships[${index}][sort_order]`}
                        value={entry.sortOrder}
                    />
                </div>
            ))}

            <div className="grid gap-4 lg:grid-cols-2">
                {/* ── Left panel: browse & select ── */}
                <div className="flex flex-col overflow-hidden rounded-xl border border-border/70">
                    {/* Panel header */}
                    <div className="border-b border-border/60 bg-muted/20 px-3 py-2.5">
                        <p className="text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                            Product browser
                        </p>
                        <p className="mt-0.5 text-xs text-muted-foreground">
                            Search and click to add products to this collection.
                        </p>
                    </div>

                    {/* Search + filter */}
                    <div className="flex gap-2 border-b border-border/60 p-3">
                        <div className="relative flex-1">
                            <Search className="absolute top-1/2 left-2.5 size-3.5 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                placeholder="Search name or SKU…"
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                className="h-8 pl-8 text-xs"
                            />
                        </div>
                        <Select
                            value={statusFilter}
                            onValueChange={setStatusFilter}
                        >
                            <SelectTrigger className="h-8 w-32 text-xs">
                                <SelectValue placeholder="All" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value={EMPTY_SENTINEL}>
                                    All
                                </SelectItem>
                                <SelectItem value="active">Active</SelectItem>
                                <SelectItem value="draft">Draft</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    {/* Product list */}
                    <div className="max-h-80 flex-1 overflow-y-auto">
                        {filteredProducts.length === 0 ? (
                            <div className="py-10 text-center text-xs text-muted-foreground">
                                No products match your search.
                            </div>
                        ) : (
                            <ul className="divide-y">
                                {filteredProducts.map((product) => {
                                    const isSelected =
                                        Object.prototype.hasOwnProperty.call(
                                            selectedProducts,
                                            String(product.id),
                                        );

                                    return (
                                        <li key={product.id}>
                                            <button
                                                type="button"
                                                onClick={() =>
                                                    onToggle(product.id)
                                                }
                                                className={`flex w-full items-center gap-3 px-3 py-2.5 text-left transition-colors hover:bg-muted/40 ${
                                                    isSelected
                                                        ? 'bg-primary/5'
                                                        : ''
                                                }`}
                                            >
                                                {/* Check indicator */}
                                                <div
                                                    className={`flex size-5 shrink-0 items-center justify-center rounded border transition-colors ${
                                                        isSelected
                                                            ? 'border-primary bg-primary text-primary-foreground'
                                                            : 'border-border bg-background'
                                                    }`}
                                                >
                                                    {isSelected && (
                                                        <Check className="size-3" />
                                                    )}
                                                </div>
                                                <div className="min-w-0 flex-1">
                                                    <p className="truncate text-sm leading-snug font-medium">
                                                        {product.name}
                                                    </p>
                                                    <p className="font-mono text-xs text-muted-foreground">
                                                        {product.sku}
                                                        <span
                                                            className={`ml-2 rounded-full px-1.5 py-0.5 text-[10px] font-medium ${
                                                                product.status ===
                                                                'active'
                                                                    ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400'
                                                                    : 'bg-muted text-muted-foreground'
                                                            }`}
                                                        >
                                                            {product.status}
                                                        </span>
                                                    </p>
                                                </div>
                                            </button>
                                        </li>
                                    );
                                })}
                            </ul>
                        )}
                    </div>

                    {/* Count footer */}
                    <div className="border-t border-border/60 bg-muted/10 px-3 py-2 text-xs text-muted-foreground">
                        {filteredProducts.length} of {products.length} products
                        shown
                    </div>
                </div>

                {/* ── Right panel: selected items with sort order ── */}
                <div className="flex flex-col overflow-hidden rounded-xl border border-border/70">
                    {/* Panel header */}
                    <div className="border-b border-border/60 bg-muted/20 px-3 py-2.5">
                        <p className="text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                            Selected products
                        </p>
                        <p className="mt-0.5 text-xs text-muted-foreground">
                            Set the display order for each product in this
                            collection.
                        </p>
                    </div>

                    {/* Selected list */}
                    <div className="max-h-80 flex-1 overflow-y-auto">
                        {selectedEntries.length === 0 ? (
                            <div className="py-10 text-center text-xs text-muted-foreground">
                                No products selected yet.
                                <br />
                                Click a product on the left to add it.
                            </div>
                        ) : (
                            <ul className="divide-y">
                                {selectedEntries.map((entry) => (
                                    <li
                                        key={entry.id}
                                        className="flex items-center gap-2 px-3 py-2.5"
                                    >
                                        <GripVertical className="size-3.5 shrink-0 cursor-grab text-muted-foreground/40" />
                                        <div className="min-w-0 flex-1">
                                            <p className="truncate text-sm leading-snug font-medium">
                                                {entry.product!.name}
                                            </p>
                                            <p className="font-mono text-xs text-muted-foreground">
                                                {entry.product!.sku}
                                            </p>
                                        </div>
                                        {/* Sort order input */}
                                        <div className="flex shrink-0 items-center gap-1.5">
                                            <Label
                                                htmlFor={`so-${entry.id}`}
                                                className="text-xs text-muted-foreground"
                                            >
                                                Order
                                            </Label>
                                            <Input
                                                id={`so-${entry.id}`}
                                                type="number"
                                                min={1}
                                                value={entry.sortOrder}
                                                onChange={(e) =>
                                                    onSortOrderChange(
                                                        entry.id,
                                                        e.target.value,
                                                    )
                                                }
                                                className="h-7 w-16 text-center text-xs tabular-nums"
                                            />
                                        </div>
                                        {/* Remove button */}
                                        <button
                                            type="button"
                                            onClick={() => onToggle(entry.id)}
                                            className="shrink-0 rounded p-0.5 text-muted-foreground/60 transition-colors hover:bg-destructive/10 hover:text-destructive"
                                            title="Remove"
                                        >
                                            <X className="size-3.5" />
                                        </button>
                                    </li>
                                ))}
                            </ul>
                        )}
                    </div>

                    {/* Count footer */}
                    <div className="border-t border-border/60 bg-muted/10 px-3 py-2 text-xs text-muted-foreground">
                        {selectedEntries.length} product
                        {selectedEntries.length === 1 ? '' : 's'} selected
                    </div>
                </div>
            </div>

            <FieldError message={error} />
        </div>
    );
}

// ─── Main form ────────────────────────────────────────────────────────────────

export function CollectionForm({
    collection,
    products,
}: {
    collection?: AdminCollection;
    products: ProductOption[];
}) {
    const isEdit = collection !== undefined;
    const [slugManual, setSlugManual] = useState(isEdit);
    const [slugValue, setSlugValue] = useState(collection?.slug ?? '');
    const [isActive, setIsActive] = useState(collection?.is_active ?? true);
    const [sortOrder, setSortOrder] = useState(
        String(collection?.sort_order ?? 0),
    );

    // Map of product id (string) → sort_order (string)
    const [selectedProducts, setSelectedProducts] = useState<
        Record<string, string>
    >(
        Object.fromEntries(
            collection?.products.map((p) => [
                String(p.id),
                String(p.sort_order),
            ]) ?? [],
        ),
    );

    const formProps = isEdit
        ? CollectionController.update.form.patch(collection)
        : CollectionController.store.form.post();

    function handleToggle(productId: number) {
        setSelectedProducts((current) => {
            const next = { ...current };

            if (Object.prototype.hasOwnProperty.call(next, String(productId))) {
                delete next[String(productId)];
            } else {
                // Assign the next sort order: max existing + 10, or (count + 1) * 10
                const existingOrders = Object.values(next).map(Number);
                const maxOrder =
                    existingOrders.length > 0 ? Math.max(...existingOrders) : 0;

                next[String(productId)] = String(maxOrder + 10);
            }

            return next;
        });
    }

    function handleSortOrderChange(productId: number, value: string) {
        setSelectedProducts((current) => ({
            ...current,
            [String(productId)]: value,
        }));
    }

    return (
        <Form
            {...formProps}
            options={{ preserveScroll: true }}
            className="space-y-8"
        >
            {({ errors }) => (
                <>
                    <input type="hidden" name="slug" value={slugValue} />
                    <input
                        type="hidden"
                        name="is_active"
                        value={isActive ? '1' : '0'}
                    />
                    <input type="hidden" name="sort_order" value={sortOrder} />

                    {/* ── Identity section ── */}
                    <FormSection
                        title="Collection identity"
                        description="Curate a named storefront rail with explicit product membership."
                        badge="Foundation"
                        contentClassName="space-y-6"
                    >
                        <div className="grid gap-5 md:grid-cols-2">
                            <div className="space-y-2">
                                <Label htmlFor="name">Collection name</Label>
                                <Input
                                    id="name"
                                    name="name"
                                    defaultValue={collection?.name ?? ''}
                                    placeholder="Weekend Edit"
                                    className="h-12 rounded-xl"
                                    onChange={(e) => {
                                        if (!slugManual) {
                                            setSlugValue(
                                                slugify(e.target.value),
                                            );
                                        }
                                    }}
                                />
                                <FieldError message={errors.name} />
                            </div>
                            <div className="space-y-2">
                                <div className="flex items-center justify-between">
                                    <Label htmlFor="slug-display">Slug</Label>
                                    <button
                                        type="button"
                                        className="text-xs font-medium text-muted-foreground hover:text-foreground"
                                        onClick={() => setSlugManual((v) => !v)}
                                    >
                                        {slugManual
                                            ? 'Manual mode'
                                            : 'Auto-generate'}
                                    </button>
                                </div>
                                <Input
                                    id="slug-display"
                                    value={slugValue}
                                    readOnly={!slugManual}
                                    className="h-12 rounded-xl font-mono text-sm"
                                    onChange={(e) => {
                                        if (slugManual) {
                                            setSlugValue(e.target.value);
                                        }
                                    }}
                                />
                                <FieldError message={errors.slug} />
                            </div>
                        </div>

                        <div className="grid gap-5 md:grid-cols-[minmax(0,1fr)_180px]">
                            <div className="space-y-2">
                                <Label htmlFor="description">Description</Label>
                                <textarea
                                    id="description"
                                    name="description"
                                    defaultValue={collection?.description ?? ''}
                                    rows={5}
                                    className={textareaClassName}
                                    placeholder="Describe the mood, use case, or campaign this collection supports."
                                />
                                <FieldError message={errors.description} />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="sort-order">
                                    Collection sort order
                                </Label>
                                <Input
                                    id="sort-order"
                                    type="number"
                                    min={0}
                                    value={sortOrder}
                                    onChange={(e) =>
                                        setSortOrder(e.target.value)
                                    }
                                    className="h-12 rounded-xl"
                                />
                                <p className="text-xs text-muted-foreground">
                                    Lower numbers surface earlier in collection
                                    lists.
                                </p>
                                <FieldError message={errors.sort_order} />
                            </div>
                        </div>

                        <label className="flex cursor-pointer items-start gap-4 rounded-2xl border p-4 transition">
                            <Checkbox
                                checked={isActive}
                                onCheckedChange={(v) => setIsActive(Boolean(v))}
                            />
                            <div className="space-y-1">
                                <p className="font-medium">
                                    Active on storefront
                                </p>
                                <p className="text-sm text-muted-foreground">
                                    Inactive collections stay in admin but
                                    disappear from public collection surfaces.
                                </p>
                            </div>
                        </label>
                    </FormSection>

                    {/* ── Products section ── */}
                    <FormSection
                        title="Curated products"
                        description="Search and select products, then set their display order within this collection."
                        badge="Merchandising"
                        contentClassName="space-y-4"
                    >
                        <ProductPicker
                            products={products}
                            selectedProducts={selectedProducts}
                            onToggle={handleToggle}
                            onSortOrderChange={handleSortOrderChange}
                            error={errors.product_memberships}
                        />
                    </FormSection>

                    <FormActions
                        submitLabel={
                            isEdit ? 'Update collection' : 'Create collection'
                        }
                        onCancel={() => window.history.back()}
                    />
                </>
            )}
        </Form>
    );
}
