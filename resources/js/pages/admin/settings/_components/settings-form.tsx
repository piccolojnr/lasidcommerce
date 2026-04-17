import { useForm } from '@inertiajs/react';
import * as SettingController from '@/actions/App/Http/Controllers/Admin/Settings/SettingController';
import { FormActions } from '@/components/shared/forms/form-actions';
import { FormSection } from '@/components/shared/forms/form-section';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { FieldError } from '@/components/shared/forms/field-error';

interface Props {
    catalogSettings: {
        new_arrival_window_days: number;
    };
}

export function SettingsForm({ catalogSettings }: Props) {
    const form = useForm({
        settings: [
            {
                key: 'catalog.new_arrival_window_days',
                value: String(catalogSettings.new_arrival_window_days),
                type: 'integer',
                group: 'catalog',
            },
        ],
    });

    return (
        <form
            className="space-y-6"
            onSubmit={(event) => {
                event.preventDefault();
                form.put(SettingController.update.url());
            }}
        >
            <FormSection
                title="Catalog merchandising"
                description="Tune computed merchandising defaults that affect storefront badges and discovery."
            >
                <div className="grid gap-4 md:grid-cols-2">
                    <div className="space-y-2">
                        <Label htmlFor="setting-new-arrival-window">New arrival window (days)</Label>
                        <Input
                            id="setting-new-arrival-window"
                            type="number"
                            min={1}
                            value={form.data.settings[0].value}
                            onChange={(event) =>
                                form.setData('settings', [
                                    {
                                        ...form.data.settings[0],
                                        value: event.target.value,
                                    },
                                ])
                            }
                        />
                        <p className="text-sm text-muted-foreground">
                            Products published within this window receive the computed <code>new_arrival</code> badge.
                        </p>
                        <FieldError message={(form.errors as Record<string, string>)['settings.0.value']} />
                    </div>
                </div>
            </FormSection>
            <FormActions />
        </form>
    );
}
