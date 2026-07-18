import { Form } from '@inertiajs/react';
import { BadgeCheck, Sparkles, Tag } from 'lucide-react';
import { useState } from 'react';
import * as BrandController from '@/actions/App/Http/Controllers/Admin/Catalog/BrandController';
import { FieldError } from '@/components/shared/forms/field-error';
import { FormActions } from '@/components/shared/forms/form-actions';
import { FormSection } from '@/components/shared/forms/form-section';
import { ImageUploadField } from '@/components/shared/forms/image-upload-field';
import { Badge } from '@/components/ui/badge';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';
import type { AdminBrand } from '@/types/admin/catalog';

const textareaClassName =
    'flex min-h-[120px] w-full rounded-xl border border-input bg-background px-4 py-3 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50';

interface BrandFormProps {
    brand?: AdminBrand;
}

function slugify(value: string): string {
    return value
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-');
}

function SummaryItem({ label, value }: { label: string; value: string }) {
    return (
        <div className="flex items-center justify-between rounded-xl border border-border/60 bg-background/80 px-4 py-3">
            <span className="text-sm text-muted-foreground">{label}</span>
            <span className="text-sm font-medium">{value}</span>
        </div>
    );
}

export function BrandForm({ brand }: BrandFormProps) {
    const isEdit = brand !== undefined;
    const [slugManual, setSlugManual] = useState(isEdit);
    const [slugValue, setSlugValue] = useState(brand?.slug ?? '');
    const [isActive, setIsActive] = useState(brand?.is_active ?? true);

    const formProps = isEdit
        ? BrandController.update.form.patch(brand)
        : BrandController.store.form.post();

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

                    <div className="grid gap-8 xl:grid-cols-[minmax(0,1.55fr)_320px]">
                        <div className="space-y-8">
                            <section className="overflow-hidden rounded-[28px] border border-border/70 bg-linear-to-br from-primary/10 via-background to-background shadow-sm">
                                <div className="grid gap-6 p-6 lg:grid-cols-[1.1fr_0.9fr] lg:p-8">
                                    <div className="space-y-4">
                                        <Badge
                                            variant="secondary"
                                            className="rounded-full px-3 py-1 text-[11px] tracking-[0.2em] uppercase"
                                        >
                                            Brand editor
                                        </Badge>
                                        <div className="space-y-3">
                                            <h2 className="text-3xl font-semibold tracking-tight">
                                                {isEdit
                                                    ? 'Sharpen the brand identity'
                                                    : 'Create a brand that feels intentional'}
                                            </h2>
                                            <p className="max-w-xl text-sm leading-6 text-muted-foreground">
                                                Strong naming, a clean slug, and
                                                a logo that does not look like
                                                an afterthought make the catalog
                                                feel much more coherent.
                                            </p>
                                        </div>
                                    </div>
                                    <div className="grid gap-3">
                                        <SummaryItem
                                            label="Status"
                                            value={
                                                isActive ? 'Active' : 'Inactive'
                                            }
                                        />
                                        <SummaryItem
                                            label="Slug mode"
                                            value={
                                                slugManual ? 'Manual' : 'Auto'
                                            }
                                        />
                                        <SummaryItem
                                            label="Products linked"
                                            value={String(
                                                brand?.products_count ?? 0,
                                            )}
                                        />
                                    </div>
                                </div>
                            </section>

                            <FormSection
                                title="Brand identity"
                                description="Name, slug, and positioning copy should all feel like the same brand."
                                badge="Foundation"
                                contentClassName="space-y-6"
                            >
                                <div className="grid gap-5 md:grid-cols-2">
                                    <div className="space-y-2">
                                        <Label htmlFor="name">Brand name</Label>
                                        <Input
                                            id="name"
                                            name="name"
                                            defaultValue={brand?.name ?? ''}
                                            placeholder="Nike"
                                            className="h-12 rounded-xl"
                                            onChange={(event) => {
                                                if (!slugManual) {
                                                    setSlugValue(
                                                        slugify(
                                                            event.target.value,
                                                        ),
                                                    );
                                                }
                                            }}
                                        />
                                        <FieldError message={errors.name} />
                                    </div>

                                    <div className="space-y-2">
                                        <div className="flex items-center justify-between gap-3">
                                            <Label htmlFor="slug-display">
                                                Slug
                                            </Label>
                                            <button
                                                type="button"
                                                className="text-xs font-medium text-muted-foreground hover:text-foreground"
                                                onClick={() =>
                                                    setSlugManual(
                                                        (value) => !value,
                                                    )
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
                                            placeholder="nike"
                                            readOnly={!slugManual}
                                            className={cn(
                                                'h-12 rounded-xl font-mono text-sm',
                                                !slugManual &&
                                                    'bg-muted text-muted-foreground',
                                            )}
                                            onChange={(event) => {
                                                if (slugManual) {
                                                    setSlugValue(
                                                        event.target.value,
                                                    );
                                                }
                                            }}
                                        />
                                        <FieldError message={errors.slug} />
                                    </div>
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="description">
                                        Description
                                    </Label>
                                    <textarea
                                        id="description"
                                        name="description"
                                        defaultValue={brand?.description ?? ''}
                                        placeholder="Describe the brand voice, positioning, or what makes it distinct in the catalog."
                                        rows={5}
                                        className={textareaClassName}
                                    />
                                    <FieldError message={errors.description} />
                                </div>
                            </FormSection>

                            <FormSection
                                title="Visual system"
                                description="Use a logo or brand image that looks clean enough to anchor the catalog."
                                badge="Imagery"
                            >
                                <ImageUploadField
                                    id="image"
                                    name="image"
                                    label={
                                        isEdit
                                            ? 'Replace the brand image'
                                            : 'Upload a brand image'
                                    }
                                    multiple={false}
                                    existingImages={
                                        brand?.image_url
                                            ? [
                                                  {
                                                      id: brand.id,
                                                      url: brand.image_url,
                                                      is_primary: true,
                                                  },
                                              ]
                                            : []
                                    }
                                    removeFieldName="remove_image"
                                    getRemoveValue={() => '1'}
                                    helperText="Recommended: square crop, clean background, at least 400×400."
                                    error={errors.image}
                                />
                            </FormSection>

                            <FormSection
                                title="Visibility"
                                description="Make the active state visible enough that nobody misses it."
                                badge="Publishing"
                            >
                                <label
                                    htmlFor="is_active"
                                    className={cn(
                                        'flex cursor-pointer items-start gap-4 rounded-2xl border p-4 transition',
                                        isActive
                                            ? 'border-primary/40 bg-primary/5'
                                            : 'border-border/70 bg-background hover:bg-muted/30',
                                    )}
                                >
                                    <Checkbox
                                        id="is_active"
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
                                            Keep this enabled when the brand
                                            should appear in navigation,
                                            filters, and product merchandising.
                                        </p>
                                    </div>
                                </label>
                                <FieldError message={errors.is_active} />
                            </FormSection>
                        </div>

                        <aside className="space-y-6 xl:sticky xl:top-6 xl:self-start">
                            <div className="rounded-[28px] border border-border/70 bg-linear-to-br from-slate-950 via-slate-900 to-slate-800 p-6 text-white shadow-sm">
                                <div className="space-y-4">
                                    <div className="flex items-center gap-2 text-sm text-white/70">
                                        <Sparkles className="size-4" />
                                        <span>Brand snapshot</span>
                                    </div>
                                    <div className="space-y-2">
                                        <h3 className="text-2xl font-semibold">
                                            {brand?.name ?? 'New brand draft'}
                                        </h3>
                                        <p className="text-sm leading-6 text-white/70">
                                            A clean logo and a consistent
                                            name/slug pair go further than most
                                            admin teams expect.
                                        </p>
                                    </div>
                                    <div className="grid gap-3">
                                        <div className="rounded-2xl bg-white/10 p-4">
                                            <div className="flex items-center gap-2 text-white/70">
                                                <Tag className="size-4" />
                                                <span className="text-sm">
                                                    Slug
                                                </span>
                                            </div>
                                            <p className="mt-2 text-sm font-medium">
                                                {slugValue ||
                                                    'Will be generated from the name'}
                                            </p>
                                        </div>
                                        <div className="rounded-2xl bg-white/10 p-4">
                                            <div className="flex items-center gap-2 text-white/70">
                                                <BadgeCheck className="size-4" />
                                                <span className="text-sm">
                                                    Visibility
                                                </span>
                                            </div>
                                            <p className="mt-2 text-sm font-medium">
                                                {isActive
                                                    ? 'Visible on storefront'
                                                    : 'Hidden from storefront'}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <FormSection
                                title="Save checklist"
                                description="The small checks that prevent a messy catalog."
                                badge="Checklist"
                            >
                                <div className="grid gap-3">
                                    <SummaryItem
                                        label="Slug mode"
                                        value={slugManual ? 'Manual' : 'Auto'}
                                    />
                                    <SummaryItem
                                        label="Current image"
                                        value={
                                            brand?.image_url
                                                ? 'Present'
                                                : 'Missing'
                                        }
                                    />
                                    <SummaryItem
                                        label="Visibility"
                                        value={isActive ? 'Active' : 'Inactive'}
                                    />
                                </div>
                            </FormSection>
                        </aside>
                    </div>

                    <FormActions
                        submitLabel={isEdit ? 'Update brand' : 'Create brand'}
                        onCancel={() => window.history.back()}
                    />
                </>
            )}
        </Form>
    );
}
