import { useForm } from '@inertiajs/react';
import { FieldError } from '@/components/shared/forms/field-error';
import { FormActions } from '@/components/shared/forms/form-actions';
import { FormSection } from '@/components/shared/forms/form-section';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { AdminWarehouseDetail } from '@/types/admin/shipping';

export function WarehouseForm({
    warehouse,
}: {
    warehouse?: AdminWarehouseDetail;
}) {
    const isEdit = Boolean(warehouse);
    const form = useForm({
        name: warehouse?.name ?? '',
        code: warehouse?.code ?? '',
        country: warehouse?.country ?? '',
        region: warehouse?.region ?? '',
        city: warehouse?.city ?? '',
        address_line_1: warehouse?.address_line_1 ?? '',
        address_line_2: warehouse?.address_line_2 ?? '',
        phone: warehouse?.phone ?? '',
        email: warehouse?.email ?? '',
        is_active: warehouse?.is_active ?? true,
        is_default: warehouse?.is_default ?? false,
    });

    const submit = () => {
        if (isEdit && warehouse) {
            form.put(`/admin/shipping/warehouse-locations/${warehouse.id}`, {
                preserveScroll: true,
            });

            return;
        }

        form.post('/admin/shipping/warehouse-locations', {
            preserveScroll: true,
        });
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
                        Warehouse setup
                    </p>
                    <h2 className="mt-2 text-2xl font-semibold tracking-tight">
                        {warehouse?.name ?? 'New warehouse location'}
                    </h2>
                    <p className="mt-2 max-w-2xl text-sm leading-6 text-muted-foreground">
                        Define the fulfillment origin, contact points, and
                        whether this warehouse should be active or default.
                    </p>
                </div>
                <div className="rounded-[2rem] border border-border/70 bg-primary/5 p-6">
                    <p className="text-xs font-semibold tracking-[0.24em] text-primary uppercase">
                        Fulfillment role
                    </p>
                    <p className="mt-2 text-xl font-semibold">
                        {form.data.is_default
                            ? 'Default warehouse'
                            : 'Standard warehouse'}
                    </p>
                    <p className="mt-2 text-sm text-muted-foreground">
                        {form.data.is_active
                            ? 'Active for shipment assignment.'
                            : 'Disabled for new operational use.'}
                    </p>
                </div>
            </div>

            <div className="grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
                <div className="space-y-6">
                    <FormSection
                        title="Warehouse identity"
                        description="Keep the site name and code stable for operators and integrations."
                        badge="Basics"
                        contentClassName="p-6"
                    >
                        <div className="grid gap-4 md:grid-cols-2">
                            <div className="space-y-2">
                                <Label htmlFor="name">Name</Label>
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
                                <Label htmlFor="code">Code</Label>
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
                        title="Address and contact"
                        description="Store the location details the operations team actually uses."
                        badge="Location"
                        contentClassName="p-6"
                    >
                        <div className="grid gap-4 md:grid-cols-3">
                            <div className="space-y-2">
                                <Label htmlFor="country">Country</Label>
                                <Input
                                    id="country"
                                    value={form.data.country}
                                    onChange={(e) =>
                                        form.setData('country', e.target.value)
                                    }
                                />
                                <FieldError message={form.errors.country} />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="region">Region</Label>
                                <Input
                                    id="region"
                                    value={form.data.region}
                                    onChange={(e) =>
                                        form.setData('region', e.target.value)
                                    }
                                />
                                <FieldError message={form.errors.region} />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="city">City</Label>
                                <Input
                                    id="city"
                                    value={form.data.city}
                                    onChange={(e) =>
                                        form.setData('city', e.target.value)
                                    }
                                />
                                <FieldError message={form.errors.city} />
                            </div>
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="address_line_1">
                                Address line 1
                            </Label>
                            <Input
                                id="address_line_1"
                                value={form.data.address_line_1}
                                onChange={(e) =>
                                    form.setData(
                                        'address_line_1',
                                        e.target.value,
                                    )
                                }
                            />
                            <FieldError message={form.errors.address_line_1} />
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="address_line_2">
                                Address line 2
                            </Label>
                            <Input
                                id="address_line_2"
                                value={form.data.address_line_2}
                                onChange={(e) =>
                                    form.setData(
                                        'address_line_2',
                                        e.target.value,
                                    )
                                }
                            />
                            <FieldError message={form.errors.address_line_2} />
                        </div>
                        <div className="grid gap-4 md:grid-cols-2">
                            <div className="space-y-2">
                                <Label htmlFor="phone">Phone</Label>
                                <Input
                                    id="phone"
                                    value={form.data.phone}
                                    onChange={(e) =>
                                        form.setData('phone', e.target.value)
                                    }
                                />
                                <FieldError message={form.errors.phone} />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="email">Email</Label>
                                <Input
                                    id="email"
                                    type="email"
                                    value={form.data.email}
                                    onChange={(e) =>
                                        form.setData('email', e.target.value)
                                    }
                                />
                                <FieldError message={form.errors.email} />
                            </div>
                        </div>
                    </FormSection>
                </div>

                <div className="space-y-6">
                    <FormSection
                        title="Warehouse behavior"
                        description="Control whether the warehouse is available and whether it acts as the default site."
                        badge="Status"
                        contentClassName="p-6"
                    >
                        <div className="space-y-4">
                            <div className="flex items-start gap-3 rounded-2xl border border-border/70 bg-muted/30 p-4">
                                <Checkbox
                                    id="is_active"
                                    checked={form.data.is_active}
                                    onCheckedChange={(value) =>
                                        form.setData(
                                            'is_active',
                                            Boolean(value),
                                        )
                                    }
                                    className="mt-0.5"
                                />
                                <div>
                                    <Label
                                        htmlFor="is_active"
                                        className="cursor-pointer"
                                    >
                                        Warehouse is active
                                    </Label>
                                    <p className="text-sm text-muted-foreground">
                                        Active warehouses are available for
                                        shipment assignment.
                                    </p>
                                </div>
                            </div>
                            <div className="flex items-start gap-3 rounded-2xl border border-border/70 bg-muted/30 p-4">
                                <Checkbox
                                    id="is_default"
                                    checked={form.data.is_default}
                                    onCheckedChange={(value) =>
                                        form.setData(
                                            'is_default',
                                            Boolean(value),
                                        )
                                    }
                                    className="mt-0.5"
                                />
                                <div>
                                    <Label
                                        htmlFor="is_default"
                                        className="cursor-pointer"
                                    >
                                        Default warehouse
                                    </Label>
                                    <p className="text-sm text-muted-foreground">
                                        Use this as the operational default when
                                        no location is specified.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <FieldError
                            message={
                                form.errors.is_active || form.errors.is_default
                            }
                        />
                    </FormSection>
                    <FormActions
                        submitLabel={
                            isEdit ? 'Update warehouse' : 'Create warehouse'
                        }
                        onCancel={() => window.history.back()}
                    />
                </div>
            </div>
        </form>
    );
}
