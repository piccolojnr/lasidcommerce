import { Form } from '@inertiajs/react';
import { useState } from 'react';
import { FieldError } from '@/components/shared/forms/field-error';
import { FormActions } from '@/components/shared/forms/form-actions';
import { FormSection } from '@/components/shared/forms/form-section';
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
import * as CategoryController from '@/actions/App/Http/Controllers/Admin/Catalog/CategoryController';
import type { AdminCategory } from '@/types/admin/catalog';

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

export function CategoryForm({ category, categories }: CategoryFormProps) {
    const isEdit = category !== undefined;
    const [slugManual, setSlugManual] = useState(isEdit);
    const [slugValue, setSlugValue] = useState(category?.slug ?? '');

    const parentOptions = isEdit
        ? categories.filter((c) => c.id !== category.id)
        : categories;

    const formProps = isEdit
        ? CategoryController.update.form.patch(category)
        : CategoryController.store.form.post();

    return (
        <Form
            {...formProps}
            options={{ preserveScroll: true }}
            className="space-y-6"
        >
            {({ processing, errors }) => (
                <>
                    {/* Hidden slug field so it is always submitted */}
                    <input type="hidden" name="slug" value={slugValue} />

                    <FormSection title="Category details" description="Basic information for organising the catalog.">
                        <div className="grid gap-4 md:grid-cols-2">
                            <div className="space-y-2">
                                <Label htmlFor="name">Name</Label>
                                <Input
                                    id="name"
                                    name="name"
                                    defaultValue={category?.name ?? ''}
                                    placeholder="New arrivals"
                                    onChange={(e) => {
                                        if (!slugManual) {
                                            setSlugValue(slugify(e.target.value));
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
                                        className="text-xs text-muted-foreground hover:text-foreground"
                                        onClick={() => setSlugManual((v) => !v)}
                                    >
                                        {slugManual ? '🔒 Manual' : '🔓 Auto'}
                                    </button>
                                </div>
                                <Input
                                    id="slug-display"
                                    value={slugValue}
                                    placeholder="new-arrivals"
                                    readOnly={!slugManual}
                                    className={!slugManual ? 'bg-muted text-muted-foreground' : ''}
                                    onChange={(e) => {
                                        if (slugManual) {
                                            setSlugValue(e.target.value);
                                        }
                                    }}
                                />
                                <FieldError message={errors.slug} />
                            </div>
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="parent_id">Parent category</Label>
                            <Select
                                name="parent_id"
                                defaultValue={category?.parent_id?.toString() ?? ''}
                            >
                                <SelectTrigger id="parent_id">
                                    <SelectValue placeholder="None (root category)" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">None (root category)</SelectItem>
                                    {parentOptions.map((opt) => (
                                        <SelectItem key={opt.id} value={opt.id.toString()}>
                                            {opt.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            <FieldError message={errors.parent_id} />
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="description">Description</Label>
                            <textarea
                                id="description"
                                name="description"
                                defaultValue={category?.description ?? ''}
                                placeholder="Optional description shown on storefront..."
                                rows={3}
                                className="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                            />
                            <FieldError message={errors.description} />
                        </div>
                    </FormSection>

                    <FormSection title="Visibility" description="Control whether this category is shown on the storefront.">
                        <div className="flex items-center gap-3">
                            <Checkbox
                                id="is_active"
                                name="is_active"
                                defaultChecked={category?.is_active ?? true}
                            />
                            <Label htmlFor="is_active" className="cursor-pointer font-normal">
                                Active — visible on the storefront
                            </Label>
                        </div>
                        <FieldError message={errors.is_active} />
                    </FormSection>

                    <FormSection title="Image" description="Upload a category image. Recommended: square, at least 400×400px.">
                        {isEdit && category.image_url && (
                            <div className="space-y-2">
                                <p className="text-sm text-muted-foreground">Current image</p>
                                <img
                                    src={category.image_url}
                                    alt={category.name}
                                    className="h-20 w-20 rounded-md border object-cover"
                                />
                            </div>
                        )}
                        <div className="space-y-2">
                            <Label htmlFor="image">
                                {isEdit && category.image_url ? 'Replace image' : 'Upload image'}
                            </Label>
                            <Input
                                id="image"
                                name="image"
                                type="file"
                                accept="image/jpeg,image/png,image/webp"
                                className="cursor-pointer"
                            />
                            <FieldError message={errors.image} />
                        </div>
                        {isEdit && category.image_url && (
                            <div className="flex items-center gap-2">
                                <Checkbox id="remove_image" name="remove_image" value="1" />
                                <Label htmlFor="remove_image" className="cursor-pointer font-normal text-destructive">
                                    Remove current image
                                </Label>
                            </div>
                        )}
                    </FormSection>

                    <FormActions
                        submitLabel={isEdit ? 'Update category' : 'Create category'}
                        onCancel={() => window.history.back()}
                    />
                </>
            )}
        </Form>
    );
}
