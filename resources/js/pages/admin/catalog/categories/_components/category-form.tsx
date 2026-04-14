import { Form } from '@inertiajs/react';
import { FolderTree, Layers3, Sparkles, Tag } from 'lucide-react';
import { useState } from 'react';
import * as CategoryController from '@/actions/App/Http/Controllers/Admin/Catalog/CategoryController';
import { FieldError } from '@/components/shared/forms/field-error';
import { FormActions } from '@/components/shared/forms/form-actions';
import { FormSection } from '@/components/shared/forms/form-section';
import { ImageUploadField } from '@/components/shared/forms/image-upload-field';
import { Badge } from '@/components/ui/badge';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    EMPTY_SENTINEL,
    normalizeSelectValue,
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { cn } from '@/lib/utils';
import type { AdminCategory } from '@/types/admin/catalog';

const textareaClassName =
    'flex min-h-[120px] w-full rounded-xl border border-input bg-background px-4 py-3 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50';

interface ParentOption {
    id: number;
    name: string;
    parent_id: number | null;
}

interface CategoryFormProps {
    category?: AdminCategory;
    categories: ParentOption[];
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

export function CategoryForm({ category, categories }: CategoryFormProps) {
    const isEdit = category !== undefined;
    const [slugManual, setSlugManual] = useState(isEdit);
    const [slugValue, setSlugValue] = useState(category?.slug ?? '');
    const [isActive, setIsActive] = useState(category?.is_active ?? true);
    const [parentId, setParentId] = useState(
        normalizeSelectValue(category?.parent_id?.toString() ?? null),
    );

    const parentOptions = isEdit
        ? categories.filter((item) => item.id !== category.id)
        : categories;
    const selectedParent =
        parentOptions.find((item) => String(item.id) === parentId)?.name ??
        'Root category';
    const formProps = isEdit
        ? CategoryController.update.form.patch(category)
        : CategoryController.store.form.post();

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
                        name="parent_id"
                        value={parentId === EMPTY_SENTINEL ? '' : parentId}
                    />
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
                                            Category editor
                                        </Badge>
                                        <div className="space-y-3">
                                            <h2 className="text-3xl font-semibold tracking-tight">
                                                {isEdit
                                                    ? 'Reshape the catalog hierarchy'
                                                    : 'Create a category that makes the catalog clearer'}
                                            </h2>
                                            <p className="max-w-xl text-sm leading-6 text-muted-foreground">
                                                Good category structure reduces
                                                friction everywhere: navigation,
                                                filters, merchandising, and
                                                reporting.
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
                                            label="Parent"
                                            value={selectedParent}
                                        />
                                        <SummaryItem
                                            label="Depth"
                                            value={
                                                category
                                                    ? String(category.depth)
                                                    : '0'
                                            }
                                        />
                                    </div>
                                </div>
                            </section>

                            <FormSection
                                title="Category identity"
                                description="The name, slug, and description should make the hierarchy obvious."
                                badge="Foundation"
                                contentClassName="space-y-6"
                            >
                                <div className="grid gap-5 md:grid-cols-2">
                                    <div className="space-y-2">
                                        <Label htmlFor="name">
                                            Category name
                                        </Label>
                                        <Input
                                            id="name"
                                            name="name"
                                            defaultValue={category?.name ?? ''}
                                            placeholder="New Arrivals"
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
                                            placeholder="new-arrivals"
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
                                        defaultValue={
                                            category?.description ?? ''
                                        }
                                        placeholder="Describe the kind of products this category is meant to group."
                                        rows={5}
                                        className={textareaClassName}
                                    />
                                    <FieldError message={errors.description} />
                                </div>
                            </FormSection>

                            <FormSection
                                title="Hierarchy"
                                description="Choose whether this category sits at the root or nests under another branch."
                                badge="Structure"
                            >
                                <div className="space-y-2">
                                    <Label htmlFor="parent_id">
                                        Parent category
                                    </Label>
                                    <Select
                                        defaultValue={parentId}
                                        onValueChange={setParentId}
                                    >
                                        <SelectTrigger
                                            id="parent_id"
                                            className="h-12 rounded-xl"
                                        >
                                            <SelectValue placeholder="None (root category)" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value={EMPTY_SENTINEL}>
                                                None (root category)
                                            </SelectItem>
                                            {parentOptions.map((option) => (
                                                <SelectItem
                                                    key={option.id}
                                                    value={option.id.toString()}
                                                >
                                                    {option.name}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    <FieldError message={errors.parent_id} />
                                </div>
                            </FormSection>

                            <FormSection
                                title="Visual identifier"
                                description="Use an image that helps customers recognize the category in navigation or landing pages."
                                badge="Imagery"
                            >
                                <ImageUploadField
                                    id="image"
                                    name="image"
                                    label={
                                        isEdit
                                            ? 'Replace the category image'
                                            : 'Upload a category image'
                                    }
                                    multiple={false}
                                    existingImages={
                                        category?.image_url
                                            ? [
                                                  {
                                                      id: category.id,
                                                      url: category.image_url,
                                                      is_primary: true,
                                                  },
                                              ]
                                            : []
                                    }
                                    removeFieldName="remove_image"
                                    getRemoveValue={() => '1'}
                                    helperText="Recommended: square crop, clear subject, at least 400×400."
                                    error={errors.image}
                                />
                            </FormSection>

                            <FormSection
                                title="Visibility"
                                description="Make the publishing decision obvious instead of hiding it in a tiny checkbox."
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
                                            Enable this when the category should
                                            appear in navigation, collections,
                                            and shopper-facing discovery flows.
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
                                        <span>Category snapshot</span>
                                    </div>
                                    <div className="space-y-2">
                                        <h3 className="text-2xl font-semibold">
                                            {category?.name ??
                                                'New category draft'}
                                        </h3>
                                        <p className="text-sm leading-6 text-white/70">
                                            Categories should clarify the
                                            catalog tree, not make it noisier.
                                        </p>
                                    </div>
                                    <div className="grid gap-3">
                                        <div className="rounded-2xl bg-white/10 p-4">
                                            <div className="flex items-center gap-2 text-white/70">
                                                <FolderTree className="size-4" />
                                                <span className="text-sm">
                                                    Parent branch
                                                </span>
                                            </div>
                                            <p className="mt-2 text-sm font-medium">
                                                {selectedParent}
                                            </p>
                                        </div>
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
                                                <Layers3 className="size-4" />
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
                                description="A few basic checks before this enters the catalog tree."
                                badge="Checklist"
                            >
                                <div className="grid gap-3">
                                    <SummaryItem
                                        label="Slug mode"
                                        value={slugManual ? 'Manual' : 'Auto'}
                                    />
                                    <SummaryItem
                                        label="Parent"
                                        value={selectedParent}
                                    />
                                    <SummaryItem
                                        label="Current image"
                                        value={
                                            category?.image_url
                                                ? 'Present'
                                                : 'Missing'
                                        }
                                    />
                                </div>
                            </FormSection>
                        </aside>
                    </div>

                    <FormActions
                        submitLabel={
                            isEdit ? 'Update category' : 'Create category'
                        }
                        onCancel={() => window.history.back()}
                    />
                </>
            )}
        </Form>
    );
}
