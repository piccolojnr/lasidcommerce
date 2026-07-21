import { Form } from '@inertiajs/react';
import { Plus, Trash2, X } from 'lucide-react';
import { useRef, useState } from 'react';
import * as ProductOptionTypeController from '@/actions/App/Http/Controllers/Admin/Catalog/ProductOptionTypeController';
import * as ProductOptionValueController from '@/actions/App/Http/Controllers/Admin/Catalog/ProductOptionValueController';
import * as ProductVariantMatrixController from '@/actions/App/Http/Controllers/Admin/Catalog/ProductVariantMatrixController';
import { FieldError } from '@/components/shared/forms/field-error';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { AdminProductOptionType } from '@/types/admin/catalog';

interface OptionBuilderProps {
    product: { id: number };
    optionTypes: AdminProductOptionType[];
}

// ─── Tag input ────────────────────────────────────────────────────────────────

function TagInput({
    values,
    onChange,
    placeholder,
}: {
    values: string[];
    onChange: (values: string[]) => void;
    placeholder?: string;
}) {
    const inputRef = useRef<HTMLInputElement>(null);
    const [draft, setDraft] = useState('');

    const commit = () => {
        const trimmed = draft.trim();
        if (trimmed && !values.includes(trimmed)) {
            onChange([...values, trimmed]);
        }
        setDraft('');
    };

    const handleKeyDown = (e: React.KeyboardEvent<HTMLInputElement>) => {
        if (e.key === 'Enter' || e.key === ',') {
            e.preventDefault();
            commit();
        }
        if (e.key === 'Backspace' && draft === '' && values.length > 0) {
            onChange(values.slice(0, -1));
        }
    };

    return (
        <div
            className="flex min-h-10 min-w-0 cursor-text flex-wrap items-center gap-1.5 rounded-xl border border-input bg-background px-3 py-2 focus-within:ring-2 focus-within:ring-ring"
            onClick={() => inputRef.current?.focus()}
        >
            {values.map((v) => (
                <span
                    key={v}
                    className="flex items-center gap-1 rounded-full bg-primary/10 px-2.5 py-0.5 text-xs font-medium text-primary"
                >
                    {v}
                    <button
                        type="button"
                        className="text-primary/60 hover:text-primary"
                        onClick={(e) => {
                            e.stopPropagation();
                            onChange(values.filter((x) => x !== v));
                        }}
                    >
                        <X className="size-3" />
                    </button>
                </span>
            ))}
            <input
                ref={inputRef}
                value={draft}
                onChange={(e) => setDraft(e.target.value)}
                onKeyDown={handleKeyDown}
                onBlur={commit}
                placeholder={values.length === 0 ? placeholder : ''}
                className="min-w-[120px] flex-1 bg-transparent text-sm outline-none placeholder:text-muted-foreground"
            />
        </div>
    );
}

// ─── Existing option type card ────────────────────────────────────────────────

function ExistingOptionCard({
    optionType,
}: {
    optionType: AdminProductOptionType;
}) {
    const [pendingValues, setPendingValues] = useState<string[]>([]);

    return (
        <Card className="border-border/70">
            <CardHeader className="flex flex-row items-start justify-between gap-4 pb-3">
                <div className="space-y-1.5">
                    <CardTitle className="text-base">
                        {optionType.name}
                    </CardTitle>
                    {/* Existing values — click × to delete */}
                    <div className="flex flex-wrap gap-1.5">
                        {optionType.values.map((v) => (
                            <Form
                                key={v.id}
                                {...ProductOptionValueController.destroy.form.delete(
                                    v,
                                )}
                                options={{ preserveScroll: true }}
                            >
                                <button
                                    type="submit"
                                    className="group flex items-center gap-1 rounded-full border border-border/60 bg-muted px-2.5 py-0.5 text-xs font-medium transition hover:border-destructive/50 hover:bg-destructive/10 hover:text-destructive"
                                    title={`Remove "${v.value}"`}
                                >
                                    {v.value}
                                    <X className="size-3 opacity-40 group-hover:opacity-100" />
                                </button>
                            </Form>
                        ))}
                        {optionType.values.length === 0 && (
                            <span className="text-xs text-muted-foreground">
                                No values yet
                            </span>
                        )}
                    </div>
                </div>
                {/* Delete entire option type */}
                <Form
                    {...ProductOptionTypeController.destroy.form.delete(
                        optionType,
                    )}
                    options={{ preserveScroll: true }}
                >
                    <Button
                        type="submit"
                        variant="ghost"
                        size="icon"
                        className="shrink-0"
                    >
                        <Trash2 className="size-4" />
                        <span className="sr-only">Delete option type</span>
                    </Button>
                </Form>
            </CardHeader>

            <CardContent className="pt-0">
                {/* Add values in bulk via tag-input → sends values[] array */}
                <Form
                    {...ProductOptionValueController.store.form.post(
                        optionType,
                    )}
                    options={{ preserveScroll: true }}
                    onSuccess={() => setPendingValues([])}
                    className="flex gap-2"
                >
                    {({ errors }) => (
                        <>
                            {pendingValues.map((val, i) => (
                                <input
                                    key={i}
                                    type="hidden"
                                    name="values[]"
                                    value={val}
                                />
                            ))}
                            <div className="flex min-w-0 flex-1 gap-2">
                                <div className="min-w-0 flex-1">
                                    <TagInput
                                        values={pendingValues}
                                        onChange={setPendingValues}
                                        placeholder="Type values, press Enter or comma…"
                                    />
                                    <FieldError
                                        message={
                                            errors.value ?? errors['values.0']
                                        }
                                    />
                                </div>
                                <Button
                                    type="submit"
                                    variant="outline"
                                    disabled={pendingValues.length === 0}
                                    className="self-start"
                                >
                                    <Plus className="size-4" />
                                    {pendingValues.length > 1
                                        ? `Add ${pendingValues.length}`
                                        : 'Add'}
                                </Button>
                            </div>
                        </>
                    )}
                </Form>
            </CardContent>
        </Card>
    );
}

// ─── Option builder panel ─────────────────────────────────────────────────────

export function OptionBuilder({ product, optionTypes }: OptionBuilderProps) {
    const [newName, setNewName] = useState('');
    const [newValues, setNewValues] = useState<string[]>([]);

    return (
        <div className="flex flex-col gap-5">
            {/* Existing option types */}
            {optionTypes.map((ot) => (
                <ExistingOptionCard key={ot.id} optionType={ot} />
            ))}

            {/* Add a new option type (with optional bulk values) */}
            <Form
                {...ProductVariantMatrixController.storeOptionType.form.post(
                    product,
                )}
                options={{ preserveScroll: true }}
                onSuccess={() => {
                    setNewName('');
                    setNewValues([]);
                }}
                className="rounded-xl border border-dashed border-border/70 p-4"
            >
                {({ errors }) => (
                    <>
                        {newValues.map((val, i) => (
                            <input
                                key={i}
                                type="hidden"
                                name="values[]"
                                value={val}
                            />
                        ))}
                        <div className="flex flex-col gap-3">
                            <div className="grid gap-3 sm:grid-cols-[200px_minmax(0,1fr)]">
                                <div className="flex flex-col gap-1.5">
                                    <Label htmlFor="new-option-name">
                                        Option name
                                    </Label>
                                    <Input
                                        id="new-option-name"
                                        name="name"
                                        value={newName}
                                        onChange={(e) =>
                                            setNewName(e.target.value)
                                        }
                                        placeholder="Size, Color, Material…"
                                        className="h-10 rounded-xl"
                                    />
                                    <FieldError message={errors.name} />
                                </div>
                                <div className="flex min-w-0 flex-col gap-1.5">
                                    <Label>Values</Label>
                                    <TagInput
                                        values={newValues}
                                        onChange={setNewValues}
                                        placeholder="S, M, L — type and press Enter or comma"
                                    />
                                    <FieldError
                                        message={
                                            errors['values.0'] ?? errors.values
                                        }
                                    />
                                </div>
                            </div>
                            <div className="flex justify-end">
                                <Button
                                    type="submit"
                                    disabled={!newName.trim()}
                                >
                                    <Plus className="mr-1 size-4" />
                                    Add option
                                </Button>
                            </div>
                        </div>
                    </>
                )}
            </Form>
        </div>
    );
}
