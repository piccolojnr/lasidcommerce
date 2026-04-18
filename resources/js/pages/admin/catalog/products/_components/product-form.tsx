import { Form } from '@inertiajs/react';
import { DollarSign, Package2, Sparkles, Tag } from 'lucide-react';
import { useState } from 'react';
import * as ProductController from '@/actions/App/Http/Controllers/Admin/Catalog/ProductController';
import { FieldError } from '@/components/shared/forms/field-error';
import { FormActions } from '@/components/shared/forms/form-actions';
import { FormSection } from '@/components/shared/forms/form-section';
import { ImageUploadField } from '@/components/shared/forms/image-upload-field';
import { Badge } from '@/components/ui/badge';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { cn } from '@/lib/utils';
import type { AdminProduct } from '@/types/admin/catalog';

const EMPTY_SENTINEL = '__empty__';
const textareaClassName =
    'flex min-h-[120px] w-full rounded-xl border border-input bg-background px-4 py-3 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50';

interface SelectOption {
    id: number;
    name: string;
}

interface ProductFormProps {
    product?: AdminProduct;
    categories: SelectOption[];
    brands: SelectOption[];
    tags: SelectOption[];
    collections: SelectOption[];
}

function slugify(value: string): string {
    return value.toLowerCase().trim().replace(/[^a-z0-9\s-]/g, '').replace(/\s+/g, '-').replace(/-+/g, '-');
}

function centsToDisplay(cents: number | null | undefined): string {
    return cents == null ? '' : (cents / 100).toFixed(2);
}

function displayToCents(display: string): string {
    if (!display) {
        return '';
    }

    const value = Number.parseFloat(display);

    return Number.isNaN(value) ? '0' : String(Math.round(value * 100));
}

function pricePreview(display: string): string {
    if (!display) {
        return 'Not set';
    }

    const value = Number.parseFloat(display);

    return Number.isNaN(value)
        ? 'Not set'
        : new Intl.NumberFormat('en-GH', {
              style: 'currency',
              currency: 'GHS',
              minimumFractionDigits: 2,
          }).format(value);
}

function SummaryItem({ label, value }: { label: string; value: string }) {
    return (
        <div className="flex items-center justify-between rounded-xl border border-border/60 bg-background/80 px-4 py-3">
            <span className="text-sm text-muted-foreground">{label}</span>
            <span className="text-sm font-medium">{value}</span>
        </div>
    );
}

function ToggleTile({
    id,
    checked,
    label,
    description,
    onChange,
}: {
    id: string;
    checked: boolean;
    label: string;
    description: string;
    onChange: (checked: boolean) => void;
}) {
    return (
        <label
            htmlFor={id}
            className={cn(
                'flex cursor-pointer items-start gap-4 rounded-2xl border p-4 transition',
                checked ? 'border-primary/40 bg-primary/5' : 'border-border/70 bg-background hover:bg-muted/30',
            )}
        >
            <Checkbox id={id} checked={checked} onCheckedChange={(value) => onChange(Boolean(value))} />
            <div className="space-y-1">
                <div className="font-medium">{label}</div>
                <p className="text-sm text-muted-foreground">{description}</p>
            </div>
        </label>
    );
}

export function ProductForm({ product, categories, brands, tags: tagOptions, collections: collectionOptions }: ProductFormProps) {
    const isEdit = product !== undefined;
    const [slugManual, setSlugManual] = useState(isEdit);
    const [slugValue, setSlugValue] = useState(product?.slug ?? '');
    const [status, setStatus] = useState(product?.status ?? 'draft');
    const [productType, setProductType] = useState(product?.product_type ?? 'physical');
    const [categoryId, setCategoryId] = useState(product?.category_id?.toString() ?? EMPTY_SENTINEL);
    const [brandId, setBrandId] = useState(product?.brand_id?.toString() ?? EMPTY_SENTINEL);
    const [isFeatured, setIsFeatured] = useState(product?.is_featured ?? false);
    const [trackInventory, setTrackInventory] = useState(product?.track_inventory ?? true);
    const [allowBackorders, setAllowBackorders] = useState(product?.allow_backorders ?? false);
    const [selectedTagIds, setSelectedTagIds] = useState<string[]>(product?.tags.map((tag) => String(tag.id)) ?? []);
    const [selectedCollectionIds, setSelectedCollectionIds] = useState<string[]>(product?.collections.map((collection) => String(collection.id)) ?? []);
    const [basePrice, setBasePrice] = useState(isEdit ? centsToDisplay(product.base_price) : '0.00');
    const [compareAtPrice, setCompareAtPrice] = useState(centsToDisplay(product?.compare_at_price));
    const [costPrice, setCostPrice] = useState(centsToDisplay(product?.cost_price));

    const formProps = isEdit ? ProductController.update.form.patch(product) : ProductController.store.form.post();
    const selectedCategory = categories.find((item) => String(item.id) === categoryId)?.name ?? 'Unassigned';
    const selectedBrand = brands.find((item) => String(item.id) === brandId)?.name ?? 'Unassigned';

    return (
        <Form {...formProps} encType="multipart/form-data" options={{ preserveScroll: true }} className="space-y-8">
            {({ errors }) => (
                <>
                    <input type="hidden" name="slug" value={slugValue} />
                    <input type="hidden" name="status" value={status} />
                    <input type="hidden" name="product_type" value={productType} />
                    <input type="hidden" name="category_id" value={categoryId === EMPTY_SENTINEL ? '' : categoryId} />
                    <input type="hidden" name="brand_id" value={brandId === EMPTY_SENTINEL ? '' : brandId} />
                    <input type="hidden" name="is_featured" value={isFeatured ? '1' : '0'} />
                    <input type="hidden" name="track_inventory" value={trackInventory ? '1' : '0'} />
                    <input type="hidden" name="allow_backorders" value={allowBackorders ? '1' : '0'} />
                    <input type="hidden" name="base_price" value={displayToCents(basePrice)} />
                    <input type="hidden" name="compare_at_price" value={displayToCents(compareAtPrice)} />
                    <input type="hidden" name="cost_price" value={displayToCents(costPrice)} />
                    {selectedTagIds.map((id) => <input key={`tag-${id}`} type="hidden" name="tag_ids[]" value={id} />)}
                    {selectedCollectionIds.map((id) => <input key={`collection-${id}`} type="hidden" name="collection_ids[]" value={id} />)}

                    <div className="grid gap-8 xl:grid-cols-[minmax(0,1.65fr)_340px]">
                        <div className="space-y-8">
                            <section className="overflow-hidden rounded-[28px] border border-border/70 bg-linear-to-br from-primary/10 via-background to-background shadow-sm">
                                <div className="grid gap-6 p-6 lg:grid-cols-[1.2fr_0.8fr] lg:p-8">
                                    <div className="space-y-4">
                                        <Badge variant="secondary" className="rounded-full px-3 py-1 text-[11px] uppercase tracking-[0.2em]">
                                            Product editor
                                        </Badge>
                                        <div className="space-y-3">
                                            <h2 className="text-3xl font-semibold tracking-tight">
                                                {isEdit ? 'Refine the product story' : 'Launch a product that looks deliberate'}
                                            </h2>
                                            <p className="max-w-xl text-sm leading-6 text-muted-foreground">
                                                Better copy, sharper pricing context, and a real gallery go a lot further than a default admin form.
                                            </p>
                                        </div>
                                    </div>
                                    <div className="grid gap-3">
                                        <SummaryItem label="Status" value={status === 'active' ? 'Active' : 'Draft'} />
                                        <SummaryItem label="Type" value={productType === 'physical' ? 'Physical' : 'Digital'} />
                                        <SummaryItem label="Category" value={selectedCategory} />
                                        <SummaryItem label="Brand" value={selectedBrand} />
                                    </div>
                                </div>
                            </section>

                            <FormSection title="Identity and positioning" description="Get the naming, slug, and product pitch right first." badge="Foundation" contentClassName="space-y-6">
                                <div className="grid gap-5 md:grid-cols-2">
                                    <div className="space-y-2">
                                        <Label htmlFor="name">Product name</Label>
                                        <Input
                                            id="name"
                                            name="name"
                                            defaultValue={product?.name ?? ''}
                                            placeholder="Classic Runner Sneaker"
                                            className="h-12 rounded-xl"
                                            onChange={(event) => {
                                                if (!slugManual) {
                                                    setSlugValue(slugify(event.target.value));
                                                }
                                            }}
                                        />
                                        <FieldError message={errors.name} />
                                    </div>
                                    <div className="space-y-2">
                                        <div className="flex items-center justify-between gap-3">
                                            <Label htmlFor="slug-display">Slug</Label>
                                            <button type="button" className="text-xs font-medium text-muted-foreground hover:text-foreground" onClick={() => setSlugManual((value) => !value)}>
                                                {slugManual ? 'Manual mode' : 'Auto-generate'}
                                            </button>
                                        </div>
                                        <Input
                                            id="slug-display"
                                            value={slugValue}
                                            placeholder="classic-runner-sneaker"
                                            readOnly={!slugManual}
                                            className={cn('h-12 rounded-xl font-mono text-sm', !slugManual && 'bg-muted text-muted-foreground')}
                                            onChange={(event) => {
                                                if (slugManual) {
                                                    setSlugValue(event.target.value);
                                                }
                                            }}
                                        />
                                        <FieldError message={errors.slug} />
                                    </div>
                                </div>

                                <div className="grid gap-5 md:grid-cols-[220px_minmax(0,1fr)]">
                                    <div className="space-y-2">
                                        <Label htmlFor="sku">SKU</Label>
                                        <Input id="sku" name="sku" defaultValue={product?.sku ?? ''} placeholder="SNK-001" className="h-12 rounded-xl" />
                                        <FieldError message={errors.sku} />
                                    </div>
                                    <div className="space-y-2">
                                        <Label htmlFor="short_description">Short description</Label>
                                        <Input
                                            id="short_description"
                                            name="short_description"
                                            defaultValue={product?.short_description ?? ''}
                                            placeholder="A comfortable everyday sneaker with a clean retro profile."
                                            className="h-12 rounded-xl"
                                        />
                                        <FieldError message={errors.short_description} />
                                    </div>
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="description">Full description</Label>
                                    <textarea
                                        id="description"
                                        name="description"
                                        defaultValue={product?.description ?? ''}
                                        placeholder="Write the richer product story, materials, fit notes, and standout details here."
                                        rows={6}
                                        className={textareaClassName}
                                    />
                                    <FieldError message={errors.description} />
                                </div>
                            </FormSection>

                            <FormSection title="Merchandising controls" description="Set status, type, and taxonomy without hunting around the page." badge="Catalog" contentClassName="space-y-6">
                                <div className="grid gap-5 md:grid-cols-2">
                                    <div className="space-y-2">
                                        <Label>Status</Label>
                                        <Select value={status} onValueChange={setStatus}>
                                            <SelectTrigger className="h-12 rounded-xl"><SelectValue /></SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="draft">Draft</SelectItem>
                                                <SelectItem value="active">Active</SelectItem>
                                            </SelectContent>
                                        </Select>
                                        <FieldError message={errors.status} />
                                    </div>
                                    <div className="space-y-2">
                                        <Label>Product type</Label>
                                        <Select value={productType} onValueChange={setProductType}>
                                            <SelectTrigger className="h-12 rounded-xl"><SelectValue /></SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="physical">Physical</SelectItem>
                                                <SelectItem value="digital">Digital</SelectItem>
                                            </SelectContent>
                                        </Select>
                                        <FieldError message={errors.product_type} />
                                    </div>
                                </div>
                                <div className="grid gap-5 md:grid-cols-2">
                                    <div className="space-y-2">
                                        <Label>Category</Label>
                                        <Select value={categoryId} onValueChange={setCategoryId}>
                                            <SelectTrigger className="h-12 rounded-xl"><SelectValue placeholder="Choose a category" /></SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value={EMPTY_SENTINEL}>No category</SelectItem>
                                                {categories.map((category) => (
                                                    <SelectItem key={category.id} value={String(category.id)}>{category.name}</SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        <FieldError message={errors.category_id} />
                                    </div>
                                    <div className="space-y-2">
                                        <Label>Brand</Label>
                                        <Select value={brandId} onValueChange={setBrandId}>
                                            <SelectTrigger className="h-12 rounded-xl"><SelectValue placeholder="Choose a brand" /></SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value={EMPTY_SENTINEL}>No brand</SelectItem>
                                                {brands.map((brand) => (
                                                    <SelectItem key={brand.id} value={String(brand.id)}>{brand.name}</SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        <FieldError message={errors.brand_id} />
                                    </div>
                                </div>
                                <div className="grid gap-6 lg:grid-cols-2">
                                    <div className="space-y-3">
                                        <div>
                                            <Label>Tags</Label>
                                            <p className="text-xs text-muted-foreground">Flexible merchandising labels for discovery and marketing context.</p>
                                        </div>
                                        <div className="grid gap-2 rounded-2xl border border-border/70 p-4">
                                            {tagOptions.map((tag) => {
                                                const checked = selectedTagIds.includes(String(tag.id));

                                                return (
                                                    <label key={tag.id} className="flex items-center gap-3 text-sm">
                                                        <Checkbox
                                                            checked={checked}
                                                                onCheckedChange={(value) => {
                                                                    const nextChecked = Boolean(value);
                                                                    setSelectedTagIds((current) => nextChecked
                                                                    ? Array.from(new Set([...current, String(tag.id)]))
                                                                    : current.filter((id) => id !== String(tag.id)));
                                                                }}
                                                        />
                                                        <span>{tag.name}</span>
                                                    </label>
                                                );
                                            })}
                                            {tagOptions.length === 0 && <p className="text-sm text-muted-foreground">No tags created yet.</p>}
                                        </div>
                                        <FieldError message={errors.tag_ids} />
                                    </div>
                                    <div className="space-y-3">
                                        <div>
                                            <Label>Collections</Label>
                                            <p className="text-xs text-muted-foreground">Add this product to curated merchandising rails and campaign groupings.</p>
                                        </div>
                                        <div className="grid gap-2 rounded-2xl border border-border/70 p-4">
                                            {collectionOptions.map((collection) => {
                                                const checked = selectedCollectionIds.includes(String(collection.id));

                                                return (
                                                    <label key={collection.id} className="flex items-center gap-3 text-sm">
                                                        <Checkbox
                                                            checked={checked}
                                                                onCheckedChange={(value) => {
                                                                    const nextChecked = Boolean(value);
                                                                    setSelectedCollectionIds((current) => nextChecked
                                                                    ? Array.from(new Set([...current, String(collection.id)]))
                                                                    : current.filter((id) => id !== String(collection.id)));
                                                                }}
                                                        />
                                                        <span>{collection.name}</span>
                                                    </label>
                                                );
                                            })}
                                            {collectionOptions.length === 0 && <p className="text-sm text-muted-foreground">No collections created yet.</p>}
                                        </div>
                                        <FieldError message={errors.collection_ids} />
                                    </div>
                                </div>
                            </FormSection>

                            <FormSection title="Pricing architecture" description="Enter decimal values here. The form still submits minor units behind the scenes." badge="Commerce" contentClassName="space-y-6">
                                <div className="grid gap-4 md:grid-cols-3">
                                    <div className="space-y-2">
                                        <Label htmlFor="base-price-display">Base price</Label>
                                        <Input id="base-price-display" type="number" min="0" step="0.01" value={basePrice} className="h-12 rounded-xl" onChange={(event) => setBasePrice(event.target.value)} />
                                        <FieldError message={errors.base_price} />
                                    </div>
                                    <div className="space-y-2">
                                        <Label htmlFor="compare-price-display">Compare-at price</Label>
                                        <Input id="compare-price-display" type="number" min="0" step="0.01" value={compareAtPrice} className="h-12 rounded-xl" onChange={(event) => setCompareAtPrice(event.target.value)} />
                                        <FieldError message={errors.compare_at_price} />
                                    </div>
                                    <div className="space-y-2">
                                        <Label htmlFor="cost-price-display">Cost price</Label>
                                        <Input id="cost-price-display" type="number" min="0" step="0.01" value={costPrice} className="h-12 rounded-xl" onChange={(event) => setCostPrice(event.target.value)} />
                                        <FieldError message={errors.cost_price} />
                                    </div>
                                </div>
                                <div className="grid gap-3 md:grid-cols-3">
                                    <SummaryItem label="Base" value={pricePreview(basePrice)} />
                                    <SummaryItem label="Compare-at" value={pricePreview(compareAtPrice)} />
                                    <SummaryItem label="Cost" value={pricePreview(costPrice)} />
                                </div>
                            </FormSection>

                            <FormSection title="Visual gallery" description="Replace the dead file input with something people can actually work with." badge="Imagery">
                                <ImageUploadField
                                    id="images"
                                    name="images[]"
                                    label={isEdit ? 'Refresh or extend the gallery' : 'Upload product images'}
                                    existingImages={
                                        product?.images.map((image) => ({
                                            ...image,
                                            preview_url: image.card_url,
                                        })) ?? []
                                    }
                                    helperText="Accepted: JPG, PNG, or WebP. Keep the first retained image strong enough to carry the listing."
                                    error={errors['images.0'] ?? errors.images}
                                />
                            </FormSection>

                            <FormSection title="Operational flags" description="These switches deserve more than tiny inline checkboxes." badge="Controls">
                                <div className="grid gap-4">
                                    <ToggleTile id="is_featured" checked={isFeatured} onChange={setIsFeatured} label="Featured placement" description="Push this product into featured collections and campaign-heavy storefront zones." />
                                    <ToggleTile id="track_inventory" checked={trackInventory} onChange={setTrackInventory} label="Track inventory" description="Tie the product to stock levels and movement tracking." />
                                    <ToggleTile id="allow_backorders" checked={allowBackorders} onChange={setAllowBackorders} label="Allow backorders" description="Keep selling even when stock is exhausted, with the operational risk that implies." />
                                </div>
                            </FormSection>

                            <FormSection title="Publishing window" description="Choose when the product should actually become visible." badge="Launch">
                                <div className="space-y-2">
                                    <Label htmlFor="published_at">Publish at</Label>
                                    <Input
                                        id="published_at"
                                        name="published_at"
                                        type="datetime-local"
                                        className="h-12 rounded-xl"
                                        defaultValue={product?.published_at ? new Date(product.published_at).toISOString().slice(0, 16) : ''}
                                    />
                                    <p className="text-xs text-muted-foreground">Leave blank if the product should remain unpublished after save.</p>
                                    <FieldError message={errors.published_at} />
                                </div>
                            </FormSection>
                        </div>

                        <aside className="space-y-6 xl:sticky xl:top-6 xl:self-start">
                            <div className="rounded-[28px] border border-border/70 bg-linear-to-br from-slate-950 via-slate-900 to-slate-800 p-6 text-white shadow-sm">
                                <div className="space-y-4">
                                    <div className="flex items-center gap-2 text-sm text-white/70">
                                        <Sparkles className="size-4" />
                                        <span>Editor snapshot</span>
                                    </div>
                                    <div className="space-y-2">
                                        <h3 className="text-2xl font-semibold">{product?.name ?? 'New product draft'}</h3>
                                        <p className="text-sm leading-6 text-white/70">Use this panel to sanity-check the merchandising signals before you save.</p>
                                    </div>
                                    <div className="grid gap-3">
                                        <div className="rounded-2xl bg-white/10 p-4">
                                            <div className="flex items-center gap-2 text-white/70"><DollarSign className="size-4" /><span className="text-sm">Base price</span></div>
                                            <p className="mt-2 text-xl font-semibold">{pricePreview(basePrice)}</p>
                                        </div>
                                        <div className="rounded-2xl bg-white/10 p-4">
                                            <div className="flex items-center gap-2 text-white/70"><Tag className="size-4" /><span className="text-sm">Taxonomy</span></div>
                                            <p className="mt-2 text-sm font-medium">{selectedCategory}</p>
                                            <p className="text-sm text-white/70">{selectedBrand}</p>
                                            <p className="pt-2 text-xs text-white/60">{selectedTagIds.length} tag(s) • {selectedCollectionIds.length} collection(s)</p>
                                        </div>
                                        <div className="rounded-2xl bg-white/10 p-4">
                                            <div className="flex items-center gap-2 text-white/70"><Package2 className="size-4" /><span className="text-sm">Availability</span></div>
                                            <p className="mt-2 text-sm font-medium">{trackInventory ? 'Inventory tracked' : 'Inventory not tracked'}</p>
                                            <p className="text-sm text-white/70">{allowBackorders ? 'Backorders enabled' : 'Backorders disabled'}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <FormSection title="Save checklist" description="Basic hygiene so the catalog doesn’t turn into sludge." badge="Checklist">
                                <div className="grid gap-3">
                                    <SummaryItem label="Slug mode" value={slugManual ? 'Manual' : 'Auto'} />
                                    <SummaryItem label="Featured" value={isFeatured ? 'Yes' : 'No'} />
                                    <SummaryItem label="Tags" value={String(selectedTagIds.length)} />
                                    <SummaryItem label="Collections" value={String(selectedCollectionIds.length)} />
                                    <SummaryItem label="Gallery images" value={String(product?.images.length ?? 0)} />
                                    <SummaryItem label="Current status" value={status === 'active' ? 'Active' : 'Draft'} />
                                </div>
                            </FormSection>
                        </aside>
                    </div>

                    <FormActions submitLabel={isEdit ? 'Update product' : 'Create product'} onCancel={() => window.history.back()} />
                </>
            )}
        </Form>
    );
}
