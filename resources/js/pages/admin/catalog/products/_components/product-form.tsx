import { Form } from '@inertiajs/react';
import { useState } from 'react';
import * as ProductController from '@/actions/App/Http/Controllers/Admin/Catalog/ProductController';
import { FieldError } from '@/components/shared/forms/field-error';
import { FormActions } from '@/components/shared/forms/form-actions';
import { FormSection } from '@/components/shared/forms/form-section';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import type { AdminProduct } from '@/types/admin/catalog';

const EMPTY_SENTINEL = '__empty__';

interface SelectOption {
    id: number;
    name: string;
}

interface ProductFormProps {
    product?: AdminProduct;
    categories: SelectOption[];
    brands: SelectOption[];
}

function slugify(value: string): string {
    return value
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-');
}

function centsToDisplay(cents: number | null | undefined): string {
    if (cents == null) return '';
    return (cents / 100).toFixed(2);
}

function displayToCents(display: string): string {
    if (display === '' || display == null) return '';
    const num = parseFloat(display);
    if (isNaN(num)) return '0';
    return String(Math.round(num * 100));
}

export function ProductForm({ product, categories, brands }: ProductFormProps) {
    const isEdit = product !== undefined;

    // Slug
    const [slugManual, setSlugManual] = useState(isEdit);
    const [slugValue, setSlugValue] = useState(product?.slug ?? '');

    // Selects
    const [status, setStatus] = useState(product?.status ?? 'draft');
    const [productType, setProductType] = useState(product?.product_type ?? 'physical');
    const [categoryId, setCategoryId] = useState(product?.category_id?.toString() ?? EMPTY_SENTINEL);
    const [brandId, setBrandId] = useState(product?.brand_id?.toString() ?? EMPTY_SENTINEL);

    // Booleans
    const [isFeatured, setIsFeatured] = useState(product?.is_featured ?? false);
    const [trackInventory, setTrackInventory] = useState(product?.track_inventory ?? true);
    const [allowBackorders, setAllowBackorders] = useState(product?.allow_backorders ?? false);

    // Prices (display in dollars, submit in cents)
    const [basePrice, setBasePrice] = useState(isEdit ? centsToDisplay(product.base_price) : '0.00');
    const [compareAtPrice, setCompareAtPrice] = useState(centsToDisplay(product?.compare_at_price));
    const [costPrice, setCostPrice] = useState(centsToDisplay(product?.cost_price));

    const formProps = isEdit
        ? ProductController.update.form.patch(product)
        : ProductController.store.form.post();

    return (
        <Form
            {...formProps}
            encType="multipart/form-data"
            options={{ preserveScroll: true }}
            className="space-y-6"
        >
            {({ errors }) => (
                <>
                    {/* Hidden controlled fields */}
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

                    {/* Core Details */}
                    <FormSection title="Core details" description="Essential identifiers for this product.">
                        <div className="grid gap-4 md:grid-cols-2">
                            <div className="space-y-2">
                                <Label htmlFor="name">Name</Label>
                                <Input
                                    id="name"
                                    name="name"
                                    defaultValue={product?.name ?? ''}
                                    placeholder="Classic Sneaker"
                                    onChange={(e) => {
                                        if (!slugManual) setSlugValue(slugify(e.target.value));
                                    }}
                                />
                                <FieldError message={errors.name} />
                            </div>

                            <div className="space-y-2">
                                <div className="flex items-center justify-between">
                                    <Label htmlFor="slug-display">Slug</Label>
                                    <button
                                        type="button"
                                        className="text-xs text-muted-foreground hover:text-foreground"
                                        onClick={() => setSlugManual((v) => !v)}
                                    >
                                        {slugManual ? '🔒 Manual' : '🔓 Auto'}
                                    </button>
                                </div>
                                <Input
                                    id="slug-display"
                                    value={slugValue}
                                    placeholder="classic-sneaker"
                                    readOnly={!slugManual}
                                    className={!slugManual ? 'bg-muted text-muted-foreground' : ''}
                                    onChange={(e) => { if (slugManual) setSlugValue(e.target.value); }}
                                />
                                <FieldError message={errors.slug} />
                            </div>
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="sku">SKU</Label>
                            <Input
                                id="sku"
                                name="sku"
                                defaultValue={product?.sku ?? ''}
                                placeholder="SNK-001"
                            />
                            <FieldError message={errors.sku} />
                        </div>
                    </FormSection>

                    {/* Catalog */}
                    <FormSection title="Catalog" description="Status, type, category, and brand.">
                        <div className="grid gap-4 md:grid-cols-2">
                            <div className="space-y-2">
                                <Label>Status</Label>
                                <Select value={status} onValueChange={setStatus}>
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
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
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="physical">Physical</SelectItem>
                                        <SelectItem value="digital">Digital</SelectItem>
                                    </SelectContent>
                                </Select>
                                <FieldError message={errors.product_type} />
                            </div>

                            <div className="space-y-2">
                                <Label>Category</Label>
                                <Select value={categoryId} onValueChange={setCategoryId}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="None" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value={EMPTY_SENTINEL}>None</SelectItem>
                                        {categories.map((c) => (
                                            <SelectItem key={c.id} value={String(c.id)}>{c.name}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <FieldError message={errors.category_id} />
                            </div>

                            <div className="space-y-2">
                                <Label>Brand</Label>
                                <Select value={brandId} onValueChange={setBrandId}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="None" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value={EMPTY_SENTINEL}>None</SelectItem>
                                        {brands.map((b) => (
                                            <SelectItem key={b.id} value={String(b.id)}>{b.name}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <FieldError message={errors.brand_id} />
                            </div>
                        </div>
                    </FormSection>

                    {/* Pricing */}
                    <FormSection title="Pricing" description="All prices in your store's currency. Enter as decimals (e.g. 29.99).">
                        <div className="grid gap-4 md:grid-cols-3">
                            <div className="space-y-2">
                                <Label htmlFor="base-price-display">Base price</Label>
                                <Input
                                    id="base-price-display"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    value={basePrice}
                                    placeholder="0.00"
                                    onChange={(e) => setBasePrice(e.target.value)}
                                />
                                <FieldError message={errors.base_price} />
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="compare-price-display">Compare-at price</Label>
                                <Input
                                    id="compare-price-display"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    value={compareAtPrice}
                                    placeholder="—"
                                    onChange={(e) => setCompareAtPrice(e.target.value)}
                                />
                                <FieldError message={errors.compare_at_price} />
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="cost-price-display">Cost price</Label>
                                <Input
                                    id="cost-price-display"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    value={costPrice}
                                    placeholder="—"
                                    onChange={(e) => setCostPrice(e.target.value)}
                                />
                                <FieldError message={errors.cost_price} />
                            </div>
                        </div>
                    </FormSection>

                    {/* Description */}
                    <FormSection title="Description" description="Short description shown in listings; long description shown on product page.">
                        <div className="space-y-2">
                            <Label htmlFor="short_description">Short description</Label>
                            <Input
                                id="short_description"
                                name="short_description"
                                defaultValue={product?.short_description ?? ''}
                                placeholder="One-line summary..."
                            />
                            <FieldError message={errors.short_description} />
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="description">Description</Label>
                            <textarea
                                id="description"
                                name="description"
                                defaultValue={product?.description ?? ''}
                                placeholder="Full product description..."
                                rows={5}
                                className="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                            />
                            <FieldError message={errors.description} />
                        </div>
                    </FormSection>

                    {/* Images */}
                    <FormSection title="Images" description="First image is the primary display image. Accepted: JPG, PNG, WebP, max 4 MB each.">
                        {isEdit && product.images.length > 0 && (
                            <div className="space-y-2">
                                <p className="text-sm font-medium">Current images</p>
                                <div className="flex flex-wrap gap-3">
                                    {product.images.map((image) => (
                                        <div key={image.id} className="relative space-y-1">
                                            <img
                                                src={image.url}
                                                alt=""
                                                className="h-20 w-20 rounded-md border object-cover"
                                            />
                                            {image.is_primary && (
                                                <span className="block text-center text-xs text-muted-foreground">Primary</span>
                                            )}
                                            <label className="flex cursor-pointer items-center gap-1 text-xs text-destructive">
                                                <input
                                                    type="checkbox"
                                                    name="remove_image_ids[]"
                                                    value={image.id}
                                                    className="h-3 w-3"
                                                />
                                                Remove
                                            </label>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        )}
                        <div className="space-y-2">
                            <Label htmlFor="images">
                                {isEdit && product.images.length > 0 ? 'Add more images' : 'Upload images'}
                            </Label>
                            <Input
                                id="images"
                                name="images[]"
                                type="file"
                                multiple
                                accept="image/jpeg,image/png,image/webp"
                                className="cursor-pointer"
                            />
                            <FieldError message={errors['images.0'] ?? errors.images} />
                        </div>
                    </FormSection>

                    {/* Flags */}
                    <FormSection title="Flags" description="Inventory and feature settings.">
                        <div className="space-y-3">
                            <div className="flex items-center gap-3">
                                <Checkbox
                                    id="is_featured"
                                    checked={isFeatured}
                                    onCheckedChange={(checked) => setIsFeatured(Boolean(checked))}
                                />
                                <Label htmlFor="is_featured" className="cursor-pointer font-normal">
                                    Featured — show in featured sections on the storefront
                                </Label>
                            </div>

                            <div className="flex items-center gap-3">
                                <Checkbox
                                    id="track_inventory"
                                    checked={trackInventory}
                                    onCheckedChange={(checked) => setTrackInventory(Boolean(checked))}
                                />
                                <Label htmlFor="track_inventory" className="cursor-pointer font-normal">
                                    Track inventory — manage stock levels for this product
                                </Label>
                            </div>

                            <div className="flex items-center gap-3">
                                <Checkbox
                                    id="allow_backorders"
                                    checked={allowBackorders}
                                    onCheckedChange={(checked) => setAllowBackorders(Boolean(checked))}
                                />
                                <Label htmlFor="allow_backorders" className="cursor-pointer font-normal">
                                    Allow backorders — customers can order when out of stock
                                </Label>
                            </div>
                        </div>
                    </FormSection>

                    {/* Publishing */}
                    <FormSection title="Publishing" description="Schedule when this product becomes visible on the storefront.">
                        <div className="space-y-2">
                            <Label htmlFor="published_at">Publish date</Label>
                            <Input
                                id="published_at"
                                name="published_at"
                                type="datetime-local"
                                defaultValue={
                                    product?.published_at
                                        ? new Date(product.published_at).toISOString().slice(0, 16)
                                        : ''
                                }
                            />
                            <p className="text-xs text-muted-foreground">Leave blank to keep unpublished.</p>
                            <FieldError message={errors.published_at} />
                        </div>
                    </FormSection>

                    <FormActions
                        submitLabel={isEdit ? 'Update product' : 'Create product'}
                        onCancel={() => window.history.back()}
                    />
                </>
            )}
        </Form>
    );
}
