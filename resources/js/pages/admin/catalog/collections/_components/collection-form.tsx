import { Form } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import * as CollectionController from '@/actions/App/Http/Controllers/Admin/Catalog/CollectionController';
import { FieldError } from '@/components/shared/forms/field-error';
import { FormActions } from '@/components/shared/forms/form-actions';
import { FormSection } from '@/components/shared/forms/form-section';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { AdminCollection } from '@/types/admin/catalog';

const textareaClassName =
    'flex min-h-[120px] w-full rounded-xl border border-input bg-background px-4 py-3 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50';

interface ProductOption {
    id: number;
    name: string;
    sku: string;
    status: string;
}

function slugify(value: string): string {
    return value
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-');
}

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
    const [selectedProducts, setSelectedProducts] = useState<
        Record<string, string>
    >(
        Object.fromEntries(
            collection?.products.map((product) => [
                String(product.id),
                String(product.sort_order),
            ]) ?? [],
        ),
    );
    const formProps = isEdit
        ? CollectionController.update.form.patch(collection)
        : CollectionController.store.form.post();

    const selectedEntries = useMemo(
        () =>
            Object.entries(selectedProducts)
                .map(([id, currentSortOrder]) => ({
                    id,
                    sortOrder: currentSortOrder,
                }))
                .sort((a, b) => Number(a.sortOrder) - Number(b.sortOrder)),
        [selectedProducts],
    );

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
                                    onChange={(event) => {
                                        if (!slugManual) {
                                            setSlugValue(
                                                slugify(event.target.value),
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
                                        onClick={() =>
                                            setSlugManual((value) => !value)
                                        }
                                    >
                                        {slugManual
                                            ? 'Manual mode'
                                            : 'Auto-generate'}
                                    </button>
                                </div>
                                <Input
                                    id="slug-display"
                                    value={slugValue}
                                    className="h-12 rounded-xl font-mono text-sm"
                                    onChange={(event) => {
                                        if (slugManual) {
                                            setSlugValue(event.target.value);
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
                                    onChange={(event) =>
                                        setSortOrder(event.target.value)
                                    }
                                    className="h-12 rounded-xl"
                                />
                                <p className="text-xs text-muted-foreground">
                                    Lower numbers surface earlier in storefront
                                    collection lists.
                                </p>
                                <FieldError message={errors.sort_order} />
                            </div>
                        </div>
                        <label className="flex cursor-pointer items-start gap-4 rounded-2xl border p-4 transition">
                            <Checkbox
                                checked={isActive}
                                onCheckedChange={(value) =>
                                    setIsActive(Boolean(value))
                                }
                            />
                            <div className="space-y-1">
                                <div className="font-medium">
                                    Active on storefront
                                </div>
                                <p className="text-sm text-muted-foreground">
                                    Inactive collections stay in admin but
                                    disappear from public collection surfaces.
                                </p>
                            </div>
                        </label>
                    </FormSection>

                    <FormSection
                        title="Curated products"
                        description="Choose products and give them an explicit ordering inside this collection."
                        badge="Merchandising"
                        contentClassName="space-y-4"
                    >
                        <div className="grid gap-3 rounded-2xl border border-border/70 p-4">
                            {products.map((product, index) => {
                                const selected =
                                    Object.prototype.hasOwnProperty.call(
                                        selectedProducts,
                                        String(product.id),
                                    );
                                const currentSortOrder =
                                    selectedProducts[String(product.id)] ??
                                    String((index + 1) * 10);

                                return (
                                    <div
                                        key={product.id}
                                        className="grid gap-3 rounded-xl border border-border/60 p-3 md:grid-cols-[minmax(0,1fr)_120px] md:items-center"
                                    >
                                        <label className="flex items-start gap-3 text-sm">
                                            <Checkbox
                                                checked={selected}
                                                onCheckedChange={(value) => {
                                                    const nextChecked =
                                                        Boolean(value);
                                                    setSelectedProducts(
                                                        (current) => {
                                                            const next = {
                                                                ...current,
                                                            };

                                                            if (nextChecked) {
                                                                next[
                                                                    String(
                                                                        product.id,
                                                                    )
                                                                ] =
                                                                    current[
                                                                        String(
                                                                            product.id,
                                                                        )
                                                                    ] ??
                                                                    String(
                                                                        (Object.keys(
                                                                            current,
                                                                        )
                                                                            .length +
                                                                            1) *
                                                                            10,
                                                                    );
                                                            } else {
                                                                delete next[
                                                                    String(
                                                                        product.id,
                                                                    )
                                                                ];
                                                            }

                                                            return next;
                                                        },
                                                    );
                                                }}
                                            />
                                            <div className="space-y-1">
                                                <div className="font-medium">
                                                    {product.name}
                                                </div>
                                                <div className="text-xs text-muted-foreground">
                                                    {product.sku} •{' '}
                                                    {product.status}
                                                </div>
                                            </div>
                                        </label>
                                        <div className="space-y-1">
                                            <Label
                                                htmlFor={`sort-order-${product.id}`}
                                            >
                                                Order
                                            </Label>
                                            <Input
                                                id={`sort-order-${product.id}`}
                                                type="number"
                                                min={0}
                                                disabled={!selected}
                                                value={currentSortOrder}
                                                onChange={(event) =>
                                                    setSelectedProducts(
                                                        (current) => ({
                                                            ...current,
                                                            [String(
                                                                product.id,
                                                            )]:
                                                                event.target
                                                                    .value,
                                                        }),
                                                    )
                                                }
                                            />
                                        </div>
                                    </div>
                                );
                            })}
                        </div>
                        <FieldError message={errors.product_memberships} />
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
