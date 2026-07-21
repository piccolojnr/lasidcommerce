import { Form } from '@inertiajs/react';
import {
    Check,
    ChevronLeft,
    ChevronRight,
    DollarSign,
    Image,
    PackageCheck,
    PackagePlus,
    Settings2,
    Shapes,
} from 'lucide-react';
import { useMemo, useState } from 'react';
import * as ProductController from '@/actions/App/Http/Controllers/Admin/Catalog/ProductController';
import { FieldError } from '@/components/shared/forms/field-error';
import { ImageUploadField } from '@/components/shared/forms/image-upload-field';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
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
import { displayToCents, pricePreview, slugify } from './product-form-utils';

const EMPTY_SENTINEL = '__empty__';

interface SelectOption {
    id: number;
    name: string;
}

interface ProductCreateWizardProps {
    categories: SelectOption[];
    brands: SelectOption[];
    tags: SelectOption[];
    collections: SelectOption[];
}

// 5 real steps — Variants step removed (it was a fake info screen)
const steps = [
    { key: 'details', label: 'Details', icon: Shapes },
    { key: 'pricing', label: 'Pricing', icon: DollarSign },
    { key: 'media', label: 'Media', icon: Image },
    { key: 'inventory', label: 'Inventory', icon: PackageCheck },
    { key: 'review', label: 'Review', icon: Check },
] as const;

type StepKey = (typeof steps)[number]['key'];

const textareaClassName =
    'flex min-h-[120px] w-full rounded-lg border border-input bg-background px-4 py-3 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50';

function SummaryRow({ label, value }: { label: string; value: string }) {
    return (
        <div className="flex items-center justify-between gap-4 rounded-lg border border-border/70 bg-background px-4 py-3">
            <span className="text-sm text-muted-foreground">{label}</span>
            <span className="truncate text-sm font-medium">{value}</span>
        </div>
    );
}

export function ProductCreateWizard({
    categories,
    brands,
    tags,
    collections,
}: ProductCreateWizardProps) {
    const [activeStep, setActiveStep] = useState<StepKey>('details');

    // Identity
    const [name, setName] = useState('');
    const [slug, setSlug] = useState('');
    const [slugManual, setSlugManual] = useState(false);
    const [sku, setSku] = useState('');
    const [shortDescription, setShortDescription] = useState('');
    const [description, setDescription] = useState('');
    const [status, setStatus] = useState('draft');
    const [productType, setProductType] = useState('physical');

    // Taxonomy (now in Details step, not Pricing)
    const [categoryId, setCategoryId] = useState(EMPTY_SENTINEL);
    const [brandId, setBrandId] = useState(EMPTY_SENTINEL);

    const [isFeatured, setIsFeatured] = useState(false);
    const [selectedTagIds, setSelectedTagIds] = useState<string[]>([]);
    const [selectedCollectionIds, setSelectedCollectionIds] = useState<
        string[]
    >([]);

    // Pricing
    const [basePrice, setBasePrice] = useState('0.00');
    const [compareAtPrice, setCompareAtPrice] = useState('');
    const [costPrice, setCostPrice] = useState('');

    // Inventory
    const [trackInventory, setTrackInventory] = useState(true);
    const [allowBackorders, setAllowBackorders] = useState(false);
    const [initialQuantity, setInitialQuantity] = useState('');
    const [initialReorderLevel, setInitialReorderLevel] = useState('');
    const [initialStockNote, setInitialStockNote] = useState('');

    // Track queued image count for the Review step summary
    const [queuedImageCount, setQueuedImageCount] = useState(0);

    const activeStepIndex = steps.findIndex((step) => step.key === activeStep);

    const selectedCategory = useMemo(
        () =>
            categories.find((c) => String(c.id) === categoryId)?.name ??
            'Unassigned',
        [categories, categoryId],
    );
    const selectedBrand = useMemo(
        () =>
            brands.find((b) => String(b.id) === brandId)?.name ?? 'Unassigned',
        [brands, brandId],
    );

    const goToRelativeStep = (delta: number) => {
        const next = steps[activeStepIndex + delta];
        if (next) setActiveStep(next.key);
    };

    const toggleId = (
        id: number,
        selectedIds: string[],
        setSelectedIds: (ids: string[]) => void,
    ) => {
        const value = String(id);
        setSelectedIds(
            selectedIds.includes(value)
                ? selectedIds.filter((s) => s !== value)
                : [...selectedIds, value],
        );
    };

    return (
        <Form
            {...ProductController.store.form.post()}
            encType="multipart/form-data"
            options={{ preserveScroll: true }}
            className="grid gap-6 xl:grid-cols-[280px_minmax(0,1fr)]"
        >
            {({ errors, processing }) => (
                <>
                    {/* Hidden fields */}
                    <input type="hidden" name="slug" value={slug} />
                    <input type="hidden" name="status" value={status} />
                    <input
                        type="hidden"
                        name="product_type"
                        value={productType}
                    />
                    <input
                        type="hidden"
                        name="category_id"
                        value={categoryId === EMPTY_SENTINEL ? '' : categoryId}
                    />
                    <input
                        type="hidden"
                        name="brand_id"
                        value={brandId === EMPTY_SENTINEL ? '' : brandId}
                    />
                    <input
                        type="hidden"
                        name="is_featured"
                        value={isFeatured ? '1' : '0'}
                    />
                    <input
                        type="hidden"
                        name="base_price"
                        value={displayToCents(basePrice)}
                    />
                    <input
                        type="hidden"
                        name="compare_at_price"
                        value={displayToCents(compareAtPrice)}
                    />
                    <input
                        type="hidden"
                        name="cost_price"
                        value={displayToCents(costPrice)}
                    />
                    <input
                        type="hidden"
                        name="track_inventory"
                        value={trackInventory ? '1' : '0'}
                    />
                    <input
                        type="hidden"
                        name="allow_backorders"
                        value={allowBackorders ? '1' : '0'}
                    />
                    <input
                        type="hidden"
                        name="initial_quantity_on_hand"
                        value={trackInventory ? initialQuantity : ''}
                    />
                    <input
                        type="hidden"
                        name="initial_reorder_level"
                        value={trackInventory ? initialReorderLevel : ''}
                    />
                    <input
                        type="hidden"
                        name="initial_stock_note"
                        value={trackInventory ? initialStockNote : ''}
                    />
                    {selectedTagIds.map((id) => (
                        <input
                            key={`tag-${id}`}
                            type="hidden"
                            name="tag_ids[]"
                            value={id}
                        />
                    ))}
                    {selectedCollectionIds.map((id) => (
                        <input
                            key={`collection-${id}`}
                            type="hidden"
                            name="collection_ids[]"
                            value={id}
                        />
                    ))}

                    {/* Sidebar */}
                    <aside className="flex flex-col gap-4 xl:sticky xl:top-6 xl:self-start">
                        <Card className="border-border/70">
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2 text-base">
                                    <PackagePlus data-icon="inline-start" />
                                    Product setup
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="flex flex-col gap-2">
                                {steps.map((step, index) => {
                                    const Icon = step.icon;
                                    const isActive = step.key === activeStep;
                                    const isComplete = index < activeStepIndex;

                                    return (
                                        <button
                                            key={step.key}
                                            type="button"
                                            onClick={() =>
                                                setActiveStep(step.key)
                                            }
                                            className={cn(
                                                'flex items-center gap-3 rounded-lg border px-3 py-3 text-left text-sm transition',
                                                isActive
                                                    ? 'border-primary bg-primary/5 text-foreground'
                                                    : 'border-border/70 bg-background text-muted-foreground hover:bg-muted/40',
                                            )}
                                        >
                                            <span className="flex size-8 items-center justify-center rounded-md bg-muted">
                                                {isComplete ? (
                                                    <Check />
                                                ) : (
                                                    <Icon />
                                                )}
                                            </span>
                                            <span className="font-medium">
                                                {step.label}
                                            </span>
                                        </button>
                                    );
                                })}
                            </CardContent>
                        </Card>

                        <Card className="border-border/70">
                            <CardHeader>
                                <CardTitle className="text-base">
                                    Setup snapshot
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="flex flex-col gap-3">
                                <SummaryRow
                                    label="Name"
                                    value={name || 'Untitled product'}
                                />
                                <SummaryRow
                                    label="SKU"
                                    value={sku || 'Unset'}
                                />
                                <SummaryRow
                                    label="Price"
                                    value={pricePreview(basePrice)}
                                />
                                <SummaryRow
                                    label="Inventory"
                                    value={
                                        trackInventory
                                            ? `${initialQuantity || 0} starting`
                                            : 'Not tracked'
                                    }
                                />
                            </CardContent>
                        </Card>
                    </aside>

                    {/* Main step panel */}
                    <div className="flex flex-col gap-6">
                        <Card className="border-border/70">
                            <CardHeader className="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                                <div>
                                    <Badge variant="secondary">
                                        Step {activeStepIndex + 1} of{' '}
                                        {steps.length}
                                    </Badge>
                                    <CardTitle className="mt-3 text-2xl">
                                        {steps[activeStepIndex].label}
                                    </CardTitle>
                                </div>
                                <div className="flex gap-2">
                                    <Button
                                        type="button"
                                        variant="outline"
                                        disabled={activeStepIndex === 0}
                                        onClick={() => goToRelativeStep(-1)}
                                    >
                                        <ChevronLeft data-icon="inline-start" />
                                        Back
                                    </Button>
                                    {activeStep !== 'review' ? (
                                        <Button
                                            type="button"
                                            onClick={() => goToRelativeStep(1)}
                                        >
                                            Next
                                            <ChevronRight data-icon="inline-end" />
                                        </Button>
                                    ) : (
                                        <Button
                                            type="submit"
                                            disabled={processing}
                                        >
                                            <Check data-icon="inline-start" />
                                            {processing
                                                ? 'Creating…'
                                                : 'Create product'}
                                        </Button>
                                    )}
                                </div>
                            </CardHeader>

                            <CardContent>
                                {/* ── Details step ──────────────────────────── */}
                                {activeStep === 'details' ? (
                                    <div className="flex flex-col gap-6">
                                        <div className="grid gap-5 md:grid-cols-2">
                                            <div className="flex flex-col gap-2">
                                                <Label htmlFor="name">
                                                    Product name
                                                </Label>
                                                <Input
                                                    id="name"
                                                    name="name"
                                                    value={name}
                                                    onChange={(e) => {
                                                        setName(e.target.value);
                                                        if (!slugManual)
                                                            setSlug(
                                                                slugify(
                                                                    e.target
                                                                        .value,
                                                                ),
                                                            );
                                                    }}
                                                    placeholder="Classic Runner Sneaker"
                                                />
                                                <FieldError
                                                    message={errors.name}
                                                />
                                            </div>
                                            <div className="flex flex-col gap-2">
                                                <div className="flex items-center justify-between gap-3">
                                                    <Label htmlFor="slug-display">
                                                        Slug
                                                    </Label>
                                                    <Button
                                                        type="button"
                                                        variant="ghost"
                                                        size="sm"
                                                        onClick={() =>
                                                            setSlugManual(
                                                                (v) => !v,
                                                            )
                                                        }
                                                    >
                                                        {slugManual
                                                            ? 'Manual'
                                                            : 'Auto'}
                                                    </Button>
                                                </div>
                                                <Input
                                                    id="slug-display"
                                                    value={slug}
                                                    readOnly={!slugManual}
                                                    onChange={(e) =>
                                                        setSlug(e.target.value)
                                                    }
                                                    placeholder="classic-runner-sneaker"
                                                />
                                                <FieldError
                                                    message={errors.slug}
                                                />
                                            </div>
                                        </div>

                                        <div className="grid gap-5 md:grid-cols-[220px_minmax(0,1fr)]">
                                            <div className="flex flex-col gap-2">
                                                <Label htmlFor="sku">SKU</Label>
                                                <Input
                                                    id="sku"
                                                    name="sku"
                                                    value={sku}
                                                    onChange={(e) =>
                                                        setSku(e.target.value)
                                                    }
                                                    placeholder="SNK-001"
                                                />
                                                <FieldError
                                                    message={errors.sku}
                                                />
                                            </div>
                                            <div className="flex flex-col gap-2">
                                                <Label htmlFor="short_description">
                                                    Short description
                                                </Label>
                                                <Input
                                                    id="short_description"
                                                    name="short_description"
                                                    value={shortDescription}
                                                    onChange={(e) =>
                                                        setShortDescription(
                                                            e.target.value,
                                                        )
                                                    }
                                                    placeholder="A comfortable everyday sneaker with a clean retro profile."
                                                />
                                                <FieldError
                                                    message={
                                                        errors.short_description
                                                    }
                                                />
                                            </div>
                                        </div>

                                        <div className="flex flex-col gap-2">
                                            <Label htmlFor="description">
                                                Full description
                                            </Label>
                                            <textarea
                                                id="description"
                                                name="description"
                                                value={description}
                                                onChange={(e) =>
                                                    setDescription(
                                                        e.target.value,
                                                    )
                                                }
                                                rows={5}
                                                className={textareaClassName}
                                                placeholder="Materials, fit notes, launch context, and product story."
                                            />
                                            <FieldError
                                                message={errors.description}
                                            />
                                        </div>

                                        <div className="grid gap-5 md:grid-cols-2">
                                            <div className="flex flex-col gap-2">
                                                <Label>Status</Label>
                                                <Select
                                                    value={status}
                                                    onValueChange={setStatus}
                                                >
                                                    <SelectTrigger>
                                                        <SelectValue />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectItem value="draft">
                                                            Draft
                                                        </SelectItem>
                                                        <SelectItem value="active">
                                                            Active
                                                        </SelectItem>
                                                    </SelectContent>
                                                </Select>
                                            </div>
                                            <div className="flex flex-col gap-2">
                                                <Label>Product type</Label>
                                                <Select
                                                    value={productType}
                                                    onValueChange={
                                                        setProductType
                                                    }
                                                >
                                                    <SelectTrigger>
                                                        <SelectValue />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectItem value="physical">
                                                            Physical
                                                        </SelectItem>
                                                        <SelectItem value="digital">
                                                            Digital
                                                        </SelectItem>
                                                    </SelectContent>
                                                </Select>
                                            </div>
                                        </div>

                                        {/* Taxonomy lives here (not Pricing) */}
                                        <div className="grid gap-5 md:grid-cols-2">
                                            <div className="flex flex-col gap-2">
                                                <Label>Category</Label>
                                                <Select
                                                    value={categoryId}
                                                    onValueChange={
                                                        setCategoryId
                                                    }
                                                >
                                                    <SelectTrigger>
                                                        <SelectValue placeholder="Choose a category" />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectItem
                                                            value={
                                                                EMPTY_SENTINEL
                                                            }
                                                        >
                                                            No category
                                                        </SelectItem>
                                                        {categories.map((c) => (
                                                            <SelectItem
                                                                key={c.id}
                                                                value={String(
                                                                    c.id,
                                                                )}
                                                            >
                                                                {c.name}
                                                            </SelectItem>
                                                        ))}
                                                    </SelectContent>
                                                </Select>
                                            </div>
                                            <div className="flex flex-col gap-2">
                                                <Label>Brand</Label>
                                                <Select
                                                    value={brandId}
                                                    onValueChange={setBrandId}
                                                >
                                                    <SelectTrigger>
                                                        <SelectValue placeholder="Choose a brand" />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectItem
                                                            value={
                                                                EMPTY_SENTINEL
                                                            }
                                                        >
                                                            No brand
                                                        </SelectItem>
                                                        {brands.map((b) => (
                                                            <SelectItem
                                                                key={b.id}
                                                                value={String(
                                                                    b.id,
                                                                )}
                                                            >
                                                                {b.name}
                                                            </SelectItem>
                                                        ))}
                                                    </SelectContent>
                                                </Select>
                                            </div>
                                        </div>
                                    </div>
                                ) : null}

                                {/* ── Pricing step ──────────────────────────── */}
                                {activeStep === 'pricing' ? (
                                    <div className="flex flex-col gap-6">
                                        <div className="grid gap-5 md:grid-cols-3">
                                            <div className="flex flex-col gap-2">
                                                <Label>Base price</Label>
                                                <Input
                                                    type="number"
                                                    min="0"
                                                    step="0.01"
                                                    value={basePrice}
                                                    onChange={(e) =>
                                                        setBasePrice(
                                                            e.target.value,
                                                        )
                                                    }
                                                />
                                                <FieldError
                                                    message={errors.base_price}
                                                />
                                            </div>
                                            <div className="flex flex-col gap-2">
                                                <Label>Compare-at price</Label>
                                                <Input
                                                    type="number"
                                                    min="0"
                                                    step="0.01"
                                                    value={compareAtPrice}
                                                    onChange={(e) =>
                                                        setCompareAtPrice(
                                                            e.target.value,
                                                        )
                                                    }
                                                />
                                                <FieldError
                                                    message={
                                                        errors.compare_at_price
                                                    }
                                                />
                                            </div>
                                            <div className="flex flex-col gap-2">
                                                <Label>Cost price</Label>
                                                <Input
                                                    type="number"
                                                    min="0"
                                                    step="0.01"
                                                    value={costPrice}
                                                    onChange={(e) =>
                                                        setCostPrice(
                                                            e.target.value,
                                                        )
                                                    }
                                                />
                                                <FieldError
                                                    message={errors.cost_price}
                                                />
                                            </div>
                                        </div>

                                        <div className="grid gap-4 md:grid-cols-3">
                                            <SummaryRow
                                                label="Base"
                                                value={pricePreview(basePrice)}
                                            />
                                            <SummaryRow
                                                label="Compare"
                                                value={pricePreview(
                                                    compareAtPrice,
                                                )}
                                            />
                                            <SummaryRow
                                                label="Cost"
                                                value={pricePreview(costPrice)}
                                            />
                                        </div>
                                    </div>
                                ) : null}

                                {/* ── Media step ────────────────────────────── */}
                                {activeStep === 'media' ? (
                                    <ImageUploadField
                                        id="images"
                                        name="images[]"
                                        label="Upload product images"
                                        helperText="Accepted: JPG, PNG, or WebP. The first image uploaded becomes the primary listing image."
                                        error={
                                            errors['images.0'] ?? errors.images
                                        }
                                        onQueueChange={setQueuedImageCount}
                                    />
                                ) : null}

                                {/* ── Inventory step ────────────────────────── */}
                                {activeStep === 'inventory' ? (
                                    <div className="flex flex-col gap-6">
                                        <div className="grid gap-4 md:grid-cols-3">
                                            <label className="flex items-start gap-3 rounded-lg border border-border/70 p-4">
                                                <Checkbox
                                                    checked={isFeatured}
                                                    onCheckedChange={(v) =>
                                                        setIsFeatured(
                                                            Boolean(v),
                                                        )
                                                    }
                                                />
                                                <span className="flex flex-col gap-1">
                                                    <span className="font-medium">
                                                        Featured
                                                    </span>
                                                    <span className="text-sm text-muted-foreground">
                                                        Mark for prominent
                                                        merchandising.
                                                    </span>
                                                </span>
                                            </label>
                                            <label className="flex items-start gap-3 rounded-lg border border-border/70 p-4">
                                                <Checkbox
                                                    checked={trackInventory}
                                                    onCheckedChange={(v) =>
                                                        setTrackInventory(
                                                            Boolean(v),
                                                        )
                                                    }
                                                />
                                                <span className="flex flex-col gap-1">
                                                    <span className="font-medium">
                                                        Track inventory
                                                    </span>
                                                    <span className="text-sm text-muted-foreground">
                                                        Create a base stock item
                                                        on save.
                                                    </span>
                                                </span>
                                            </label>
                                            <label className="flex items-start gap-3 rounded-lg border border-border/70 p-4">
                                                <Checkbox
                                                    checked={allowBackorders}
                                                    onCheckedChange={(v) =>
                                                        setAllowBackorders(
                                                            Boolean(v),
                                                        )
                                                    }
                                                />
                                                <span className="flex flex-col gap-1">
                                                    <span className="font-medium">
                                                        Backorders
                                                    </span>
                                                    <span className="text-sm text-muted-foreground">
                                                        Keep selling when stock
                                                        is exhausted.
                                                    </span>
                                                </span>
                                            </label>
                                        </div>

                                        <div
                                            className={cn(
                                                'grid gap-5 md:grid-cols-3',
                                                !trackInventory && 'opacity-50',
                                            )}
                                        >
                                            <div className="flex flex-col gap-2">
                                                <Label>Initial quantity</Label>
                                                <Input
                                                    type="number"
                                                    min="0"
                                                    value={initialQuantity}
                                                    disabled={!trackInventory}
                                                    onChange={(e) =>
                                                        setInitialQuantity(
                                                            e.target.value,
                                                        )
                                                    }
                                                />
                                                <FieldError
                                                    message={
                                                        errors.initial_quantity_on_hand
                                                    }
                                                />
                                            </div>
                                            <div className="flex flex-col gap-2">
                                                <Label>Reorder level</Label>
                                                <Input
                                                    type="number"
                                                    min="0"
                                                    value={initialReorderLevel}
                                                    disabled={!trackInventory}
                                                    onChange={(e) =>
                                                        setInitialReorderLevel(
                                                            e.target.value,
                                                        )
                                                    }
                                                />
                                                <FieldError
                                                    message={
                                                        errors.initial_reorder_level
                                                    }
                                                />
                                            </div>
                                            <div className="flex flex-col gap-2">
                                                <Label>Stock note</Label>
                                                <Input
                                                    value={initialStockNote}
                                                    disabled={!trackInventory}
                                                    onChange={(e) =>
                                                        setInitialStockNote(
                                                            e.target.value,
                                                        )
                                                    }
                                                    placeholder="Opening count"
                                                />
                                                <FieldError
                                                    message={
                                                        errors.initial_stock_note
                                                    }
                                                />
                                            </div>
                                        </div>

                                        <div className="grid gap-5 md:grid-cols-2">
                                            <div className="flex flex-col gap-2">
                                                <Label>Tags</Label>
                                                <div className="flex flex-wrap gap-2 rounded-lg border border-border/70 p-3">
                                                    {tags.map((tag) => (
                                                        <label
                                                            key={tag.id}
                                                            className="flex items-center gap-2 rounded-md bg-muted px-3 py-2 text-sm"
                                                        >
                                                            <Checkbox
                                                                checked={selectedTagIds.includes(
                                                                    String(
                                                                        tag.id,
                                                                    ),
                                                                )}
                                                                onCheckedChange={() =>
                                                                    toggleId(
                                                                        tag.id,
                                                                        selectedTagIds,
                                                                        setSelectedTagIds,
                                                                    )
                                                                }
                                                            />
                                                            <span>
                                                                {tag.name}
                                                            </span>
                                                        </label>
                                                    ))}
                                                    {tags.length === 0 ? (
                                                        <p className="text-sm text-muted-foreground">
                                                            No tags created.
                                                        </p>
                                                    ) : null}
                                                </div>
                                            </div>
                                            <div className="flex flex-col gap-2">
                                                <Label>Collections</Label>
                                                <div className="flex flex-wrap gap-2 rounded-lg border border-border/70 p-3">
                                                    {collections.map(
                                                        (collection) => (
                                                            <label
                                                                key={
                                                                    collection.id
                                                                }
                                                                className="flex items-center gap-2 rounded-md bg-muted px-3 py-2 text-sm"
                                                            >
                                                                <Checkbox
                                                                    checked={selectedCollectionIds.includes(
                                                                        String(
                                                                            collection.id,
                                                                        ),
                                                                    )}
                                                                    onCheckedChange={() =>
                                                                        toggleId(
                                                                            collection.id,
                                                                            selectedCollectionIds,
                                                                            setSelectedCollectionIds,
                                                                        )
                                                                    }
                                                                />
                                                                <span>
                                                                    {
                                                                        collection.name
                                                                    }
                                                                </span>
                                                            </label>
                                                        ),
                                                    )}
                                                    {collections.length ===
                                                    0 ? (
                                                        <p className="text-sm text-muted-foreground">
                                                            No collections
                                                            created.
                                                        </p>
                                                    ) : null}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                ) : null}

                                {/* ── Review step ───────────────────────────── */}
                                {activeStep === 'review' ? (
                                    <div className="flex flex-col gap-4">
                                        <div className="grid gap-4 md:grid-cols-2">
                                            <SummaryRow
                                                label="Product"
                                                value={
                                                    name || 'Untitled product'
                                                }
                                            />
                                            <SummaryRow
                                                label="SKU"
                                                value={sku || 'Unset'}
                                            />
                                            <SummaryRow
                                                label="Status"
                                                value={
                                                    status === 'active'
                                                        ? 'Active'
                                                        : 'Draft'
                                                }
                                            />
                                            <SummaryRow
                                                label="Type"
                                                value={
                                                    productType === 'physical'
                                                        ? 'Physical'
                                                        : 'Digital'
                                                }
                                            />
                                            <SummaryRow
                                                label="Category"
                                                value={selectedCategory}
                                            />
                                            <SummaryRow
                                                label="Brand"
                                                value={selectedBrand}
                                            />
                                            <SummaryRow
                                                label="Base price"
                                                value={pricePreview(basePrice)}
                                            />
                                            <SummaryRow
                                                label="Initial stock"
                                                value={
                                                    trackInventory
                                                        ? `${initialQuantity || 0} on hand`
                                                        : 'Not tracked'
                                                }
                                            />
                                            <SummaryRow
                                                label="Tags"
                                                value={String(
                                                    selectedTagIds.length,
                                                )}
                                            />
                                            <SummaryRow
                                                label="Collections"
                                                value={String(
                                                    selectedCollectionIds.length,
                                                )}
                                            />
                                            <SummaryRow
                                                label="Images queued"
                                                value={
                                                    queuedImageCount > 0
                                                        ? `${queuedImageCount} ready to upload`
                                                        : 'None — add images after save'
                                                }
                                            />
                                            <SummaryRow
                                                label="Featured"
                                                value={
                                                    isFeatured ? 'Yes' : 'No'
                                                }
                                            />
                                        </div>

                                        {queuedImageCount === 0 && (
                                            <p className="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-800/40 dark:bg-amber-950/30 dark:text-amber-300">
                                                No images uploaded yet. You can
                                                add them from the product editor
                                                after creating.
                                            </p>
                                        )}
                                    </div>
                                ) : null}
                            </CardContent>
                        </Card>

                        {/* Footer hint bar */}
                        <Card className="border-border/70 bg-muted/30">
                            <CardContent className="flex flex-col gap-3 p-4 md:flex-row md:items-center md:justify-between">
                                <div className="flex items-center gap-2 text-sm text-muted-foreground">
                                    <Settings2 data-icon="inline-start" />
                                    <span>
                                        After save, the product editor opens so
                                        you can add options and variants
                                        immediately.
                                    </span>
                                </div>
                                <Button type="submit" disabled={processing}>
                                    <Check data-icon="inline-start" />
                                    {processing
                                        ? 'Creating product…'
                                        : 'Create product'}
                                </Button>
                            </CardContent>
                        </Card>
                    </div>
                </>
            )}
        </Form>
    );
}
