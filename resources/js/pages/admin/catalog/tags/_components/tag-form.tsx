import { Form } from '@inertiajs/react';
import { useState } from 'react';
import * as TagController from '@/actions/App/Http/Controllers/Admin/Catalog/TagController';
import { FieldError } from '@/components/shared/forms/field-error';
import { FormActions } from '@/components/shared/forms/form-actions';
import { FormSection } from '@/components/shared/forms/form-section';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { AdminTag } from '@/types/admin/catalog';

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

export function TagForm({ tag }: { tag?: AdminTag }) {
    const isEdit = tag !== undefined;
    const [slugManual, setSlugManual] = useState(isEdit);
    const [slugValue, setSlugValue] = useState(tag?.slug ?? '');
    const [isActive, setIsActive] = useState(tag?.is_active ?? true);
    const formProps = isEdit
        ? TagController.update.form.patch(tag)
        : TagController.store.form.post();

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

                    <FormSection
                        title="Tag identity"
                        description="Keep merchandising labels consistent and readable."
                        badge="Foundation"
                        contentClassName="space-y-6"
                    >
                        <div className="grid gap-5 md:grid-cols-2">
                            <div className="space-y-2">
                                <Label htmlFor="name">Tag name</Label>
                                <Input
                                    id="name"
                                    name="name"
                                    defaultValue={tag?.name ?? ''}
                                    placeholder="Editor Pick"
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
                                    readOnly={!slugManual}
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
                        <div className="space-y-2">
                            <Label htmlFor="description">Description</Label>
                            <textarea
                                id="description"
                                name="description"
                                defaultValue={tag?.description ?? ''}
                                rows={5}
                                placeholder="Describe when this tag should be used."
                                className={textareaClassName}
                            />
                            <FieldError message={errors.description} />
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
                                    Inactive tags remain in admin history but
                                    should not appear in storefront discovery.
                                </p>
                            </div>
                        </label>
                    </FormSection>

                    <FormActions
                        submitLabel={isEdit ? 'Update tag' : 'Create tag'}
                        onCancel={() => window.history.back()}
                    />
                </>
            )}
        </Form>
    );
}
