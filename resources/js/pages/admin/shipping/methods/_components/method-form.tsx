import { useForm } from '@inertiajs/react';
import { FieldError } from '@/components/shared/forms/field-error';
import { FormActions } from '@/components/shared/forms/form-actions';
import { FormSection } from '@/components/shared/forms/form-section';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { adminRoutes } from '@/lib/routes';
import type { AdminShippingMethodDetail } from '@/types/admin/shipping';

export function MethodForm({ method }: { method?: AdminShippingMethodDetail }) {
    const isEdit = Boolean(method);
    const form = useForm({
        name: method?.name ?? '',
        code: method?.code ?? '',
        method_type: method?.method_type ?? 'delivery',
        price_type: method?.price_type ?? 'flat_rate',
        flat_rate_amount: method?.flat_rate_amount?.toString() ?? '',
        min_delivery_days: method?.min_delivery_days?.toString() ?? '',
        max_delivery_days: method?.max_delivery_days?.toString() ?? '',
        description: method?.description ?? '',
        is_active: method?.is_active ?? true,
    });

    const submit = () => {
        if (isEdit && method) {
            form.put(`${adminRoutes.shipping.methods}/${method.id}`, { preserveScroll: true });

            return;
        }

        form.post(adminRoutes.shipping.methods, { preserveScroll: true });
    };

    return (
        <form
            className="space-y-8"
            onSubmit={(event) => {
                event.preventDefault();
                submit();
            }}
        >
            <div className="rounded-[2rem] border border-border/70 bg-muted/30 p-6">
                <p className="text-xs font-semibold uppercase tracking-[0.24em] text-muted-foreground">Method setup</p>
                <h2 className="mt-2 text-2xl font-semibold tracking-tight">{method?.name ?? 'New shipping method'}</h2>
                <p className="mt-2 text-sm leading-6 text-muted-foreground">
                    Define a reusable shipping method once, then attach it to any zone that should offer it.
                </p>
            </div>

            <div className="grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
                <div className="space-y-6">
                    <FormSection title="Method identity" description="Define the customer-facing and operator-facing identifiers." badge="Basics" contentClassName="p-6">
                        <div className="grid gap-4 md:grid-cols-2">
                            <div className="space-y-2"><Label htmlFor="name">Method name</Label><Input id="name" value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} /><FieldError message={form.errors.name} /></div>
                            <div className="space-y-2"><Label htmlFor="code">Method code</Label><Input id="code" value={form.data.code} onChange={(e) => form.setData('code', e.target.value)} className="font-mono uppercase" /><FieldError message={form.errors.code} /></div>
                        </div>
                        <div className="grid gap-4 md:grid-cols-2">
                            <div className="space-y-2">
                                <Label htmlFor="method_type">Method type</Label>
                                <Select value={form.data.method_type} onValueChange={(value) => form.setData('method_type', value)}>
                                    <SelectTrigger id="method_type"><SelectValue placeholder="Select method type" /></SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="delivery">Delivery</SelectItem>
                                        <SelectItem value="pickup">Pickup</SelectItem>
                                        <SelectItem value="express">Express</SelectItem>
                                    </SelectContent>
                                </Select>
                                <FieldError message={form.errors.method_type} />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="price_type">Price type</Label>
                                <Select value={form.data.price_type} onValueChange={(value) => form.setData('price_type', value)}>
                                    <SelectTrigger id="price_type"><SelectValue placeholder="Select price type" /></SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="flat_rate">Flat rate</SelectItem>
                                        <SelectItem value="free">Free</SelectItem>
                                    </SelectContent>
                                </Select>
                                <FieldError message={form.errors.price_type} />
                            </div>
                        </div>
                    </FormSection>

                    <FormSection title="Pricing and delivery window" description="Set the commercial and timeline expectations for this method." badge="Commercial" contentClassName="p-6">
                        <div className="grid gap-4 md:grid-cols-3">
                            <div className="space-y-2"><Label htmlFor="flat_rate_amount">Flat rate amount</Label><Input id="flat_rate_amount" type="number" min="0" value={form.data.flat_rate_amount} onChange={(e) => form.setData('flat_rate_amount', e.target.value)} placeholder="2500" /><FieldError message={form.errors.flat_rate_amount} /></div>
                            <div className="space-y-2"><Label htmlFor="min_delivery_days">Min days</Label><Input id="min_delivery_days" type="number" min="0" value={form.data.min_delivery_days} onChange={(e) => form.setData('min_delivery_days', e.target.value)} placeholder="1" /><FieldError message={form.errors.min_delivery_days} /></div>
                            <div className="space-y-2"><Label htmlFor="max_delivery_days">Max days</Label><Input id="max_delivery_days" type="number" min="0" value={form.data.max_delivery_days} onChange={(e) => form.setData('max_delivery_days', e.target.value)} placeholder="3" /><FieldError message={form.errors.max_delivery_days} /></div>
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="description">Description</Label>
                            <textarea id="description" value={form.data.description} onChange={(e) => form.setData('description', e.target.value)} className="min-h-32 w-full rounded-md border border-input bg-background px-3 py-2 text-sm" />
                            <FieldError message={form.errors.description} />
                        </div>
                    </FormSection>
                </div>

                <div className="space-y-6">
                    <FormSection title="Method behavior" description="Control whether this reusable method is available for zone assignment and checkout." badge="Status" contentClassName="p-6">
                        <div className="flex items-start gap-3 rounded-2xl border border-border/70 bg-muted/30 p-4">
                            <Checkbox id="is_active" checked={form.data.is_active} onCheckedChange={(value) => form.setData('is_active', Boolean(value))} className="mt-0.5" />
                            <div>
                                <Label htmlFor="is_active" className="cursor-pointer">Method is active</Label>
                                <p className="text-sm text-muted-foreground">Inactive methods stay in the library but should not be used for new zone assignments or checkout.</p>
                            </div>
                        </div>
                        <FieldError message={form.errors.is_active} />
                    </FormSection>
                    <FormActions submitLabel={isEdit ? 'Update method' : 'Create method'} onCancel={() => window.history.back()} />
                </div>
            </div>
        </form>
    );
}
