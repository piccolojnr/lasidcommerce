import { useForm } from '@inertiajs/react';
import { FieldError } from '@/components/shared/forms/field-error';
import { FormActions } from '@/components/shared/forms/form-actions';
import { FormSection } from '@/components/shared/forms/form-section';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { AdminShippingZoneDetail } from '@/types/admin/shipping';

interface Props {
    zone?: AdminShippingZoneDetail;
}

export function ZoneForm({ zone }: Props) {
    const isEdit = Boolean(zone);
    const form = useForm({
        name: zone?.name ?? '',
        code: zone?.code ?? '',
        description: zone?.description ?? '',
        country_code: zone?.country_code ?? '',
        is_active: zone?.is_active ?? true,
    });

    const submit = () => {
        if (isEdit && zone) {
            form.put(`/admin/shipping/zones/${zone.id}`, {
                preserveScroll: true,
            });

            return;
        }

        form.post('/admin/shipping/zones', { preserveScroll: true });
    };

    return (
        <form
            className="space-y-8"
            onSubmit={(event) => {
                event.preventDefault();
                submit();
            }}
        >
            <div className="grid gap-4 lg:grid-cols-3">
                <div className="rounded-[2rem] border border-border/70 bg-muted/30 p-6 lg:col-span-2">
                    <p className="text-xs font-semibold tracking-[0.24em] text-muted-foreground uppercase">
                        Zone setup
                    </p>
                    <h2 className="mt-2 text-2xl font-semibold tracking-tight">
                        {zone?.name ?? 'New shipping zone'}
                    </h2>
                    <p className="mt-2 max-w-2xl text-sm leading-6 text-muted-foreground">
                        Define the commercial territory, the zone identifier,
                        and whether it should participate in resolution logic.
                    </p>
                </div>
                <div className="rounded-[2rem] border border-border/70 bg-primary/5 p-6">
                    <p className="text-xs font-semibold tracking-[0.24em] text-primary uppercase">
                        Routing state
                    </p>
                    <p className="mt-2 text-xl font-semibold">
                        {form.data.is_active
                            ? 'Active for checkout'
                            : 'Disabled'}
                    </p>
                    <p className="mt-2 text-sm text-muted-foreground">
                        Disabled zones stay in the admin but should not be used
                        for customer resolution.
                    </p>
                </div>
            </div>

            <div className="grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
                <div className="space-y-6">
                    <FormSection
                        title="Zone identity"
                        description="Keep the zone name human-readable and the code stable for operators."
                        badge="Basics"
                        contentClassName="p-6"
                    >
                        <div className="grid gap-4 md:grid-cols-2">
                            <div className="space-y-2">
                                <Label htmlFor="name">Zone name</Label>
                                <Input
                                    id="name"
                                    value={form.data.name}
                                    onChange={(e) =>
                                        form.setData('name', e.target.value)
                                    }
                                />
                                <FieldError message={form.errors.name} />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="code">Zone code</Label>
                                <Input
                                    id="code"
                                    value={form.data.code}
                                    onChange={(e) =>
                                        form.setData('code', e.target.value)
                                    }
                                    className="font-mono uppercase"
                                />
                                <FieldError message={form.errors.code} />
                            </div>
                        </div>
                    </FormSection>

                    <FormSection
                        title="Coverage"
                        description="Define the country anchor and optional operator notes for this zone."
                        badge="Coverage"
                        contentClassName="p-6"
                    >
                        <div className="grid gap-4 md:grid-cols-2">
                            <div className="space-y-2">
                                <Label htmlFor="country_code">
                                    Country code
                                </Label>
                                <Input
                                    id="country_code"
                                    value={form.data.country_code}
                                    onChange={(e) =>
                                        form.setData(
                                            'country_code',
                                            e.target.value.toUpperCase(),
                                        )
                                    }
                                    maxLength={2}
                                    className="font-mono uppercase"
                                />
                                <FieldError
                                    message={form.errors.country_code}
                                />
                            </div>
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="description">Description</Label>
                            <textarea
                                id="description"
                                value={form.data.description}
                                onChange={(e) =>
                                    form.setData('description', e.target.value)
                                }
                                className="min-h-32 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                            />
                            <FieldError message={form.errors.description} />
                        </div>
                    </FormSection>
                </div>

                <div className="space-y-6">
                    <FormSection
                        title="Zone behavior"
                        description="Control whether this zone is eligible for active shipping resolution."
                        badge="Status"
                        contentClassName="p-6"
                    >
                        <div className="flex items-start gap-3 rounded-2xl border border-border/70 bg-muted/30 p-4">
                            <Checkbox
                                id="is_active"
                                checked={form.data.is_active}
                                onCheckedChange={(value) =>
                                    form.setData('is_active', Boolean(value))
                                }
                                className="mt-0.5"
                            />
                            <div className="space-y-1">
                                <Label
                                    htmlFor="is_active"
                                    className="cursor-pointer"
                                >
                                    Zone is active
                                </Label>
                                <p className="text-sm text-muted-foreground">
                                    Active zones can participate in shipping
                                    zone matching and pricing logic.
                                </p>
                            </div>
                        </div>
                        <FieldError message={form.errors.is_active} />
                    </FormSection>

                    <FormActions
                        submitLabel={isEdit ? 'Update zone' : 'Create zone'}
                        onCancel={() => window.history.back()}
                    />
                </div>
            </div>
        </form>
    );
}
