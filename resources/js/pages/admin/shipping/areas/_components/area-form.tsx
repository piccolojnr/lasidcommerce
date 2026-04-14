import { useForm } from '@inertiajs/react';
import { FieldError } from '@/components/shared/forms/field-error';
import { FormActions } from '@/components/shared/forms/form-actions';
import { FormSection } from '@/components/shared/forms/form-section';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { AdminShippingZoneAreaDetail } from '@/types/admin/shipping';

type ZoneRef = { id: number; name: string; code: string };

export function AreaForm({
    zone,
    area,
}: {
    zone: ZoneRef;
    area?: AdminShippingZoneAreaDetail;
}) {
    const isEdit = Boolean(area);
    const form = useForm({
        area_type: area?.area_type ?? '',
        area_name: area?.area_name ?? '',
    });

    const submit = () => {
        if (isEdit && area) {
            form.put(`/admin/shipping/areas/${area.id}`, { preserveScroll: true });

            return;
        }

        form.post(`/admin/shipping/zones/${zone.id}/areas`, { preserveScroll: true });
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
                <p className="text-xs font-semibold uppercase tracking-[0.24em] text-muted-foreground">Area setup</p>
                <h2 className="mt-2 text-2xl font-semibold tracking-tight">{area?.area_name ?? 'New zone area'}</h2>
                <p className="mt-2 text-sm leading-6 text-muted-foreground">
                    This rule belongs to {zone.name} ({zone.code}) and helps the zone resolver understand which addresses belong here.
                </p>
            </div>

            <FormSection
                title="Area rule"
                description="Define the area type and the operator-facing name used for matching."
                badge="Rule"
                contentClassName="p-6"
            >
                <div className="grid gap-4 md:grid-cols-2">
                    <div className="space-y-2">
                        <Label htmlFor="area_type">Area type</Label>
                        <Input id="area_type" value={form.data.area_type} onChange={(e) => form.setData('area_type', e.target.value)} placeholder="city" />
                        <FieldError message={form.errors.area_type} />
                    </div>
                    <div className="space-y-2">
                        <Label htmlFor="area_name">Area name</Label>
                        <Input id="area_name" value={form.data.area_name} onChange={(e) => form.setData('area_name', e.target.value)} placeholder="Lagos Island" />
                        <FieldError message={form.errors.area_name} />
                    </div>
                </div>
            </FormSection>

            <FormActions
                submitLabel={isEdit ? 'Update area' : 'Create area'}
                onCancel={() => window.history.back()}
            />
        </form>
    );
}
