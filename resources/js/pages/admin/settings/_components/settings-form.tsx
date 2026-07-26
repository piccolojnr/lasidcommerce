import { useForm } from '@inertiajs/react';
import * as SettingController from '@/actions/App/Http/Controllers/Admin/Settings/SettingController';
import { FieldError } from '@/components/shared/forms/field-error';
import { FormActions } from '@/components/shared/forms/form-actions';
import { FormSection } from '@/components/shared/forms/form-section';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface Props {
    catalogSettings: {
        new_arrival_window_days: number;
    };
    announcementSettings: {
        enabled: boolean | string;
        message: string;
        cta_label: string | null;
        cta_url: string | null;
        variant: string;
        starts_at: string | null;
        ends_at: string | null;
    };
}

export function SettingsForm({ catalogSettings, announcementSettings }: Props) {
    const announcement = announcementSettings;
    const form = useForm({
        settings: [
            {
                key: 'catalog.new_arrival_window_days',
                value: String(catalogSettings.new_arrival_window_days),
                type: 'integer',
                group: 'catalog',
            },
            {
                key: 'storefront.announcement.enabled',
                value: String(
                    announcement.enabled === true ||
                        announcement.enabled === '1' ||
                        announcement.enabled === 'true',
                ),
                type: 'boolean',
                group: 'storefront',
            },
            {
                key: 'storefront.announcement.message',
                value: announcement.message,
                type: 'string',
                group: 'storefront',
            },
            {
                key: 'storefront.announcement.cta_label',
                value: announcement.cta_label ?? '',
                type: 'string',
                group: 'storefront',
            },
            {
                key: 'storefront.announcement.cta_url',
                value: announcement.cta_url ?? '',
                type: 'string',
                group: 'storefront',
            },
            {
                key: 'storefront.announcement.variant',
                value: announcement.variant,
                type: 'string',
                group: 'storefront',
            },
            {
                key: 'storefront.announcement.starts_at',
                value: announcement.starts_at
                    ? announcement.starts_at.slice(0, 16)
                    : '',
                type: 'datetime',
                group: 'storefront',
            },
            {
                key: 'storefront.announcement.ends_at',
                value: announcement.ends_at
                    ? announcement.ends_at.slice(0, 16)
                    : '',
                type: 'datetime',
                group: 'storefront',
            },
        ],
    });

    const setAnnouncementValue = (index: number, value: string) => {
        form.setData(
            'settings',
            form.data.settings.map((setting, settingIndex) =>
                settingIndex === index ? { ...setting, value } : setting,
            ),
        );
    };

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
                        <Label htmlFor="setting-new-arrival-window">
                            New arrival window (days)
                        </Label>
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
                            Products published within this window receive the
                            computed <code>new_arrival</code> badge.
                        </p>
                        <FieldError
                            message={
                                (form.errors as Record<string, string>)[
                                    'settings.0.value'
                                ]
                            }
                        />
                    </div>
                </div>
            </FormSection>
            <FormSection
                title="Announcement bar"
                description="Control the message displayed in the storefront header. Schedule it when it should automatically appear."
            >
                <div className="space-y-5">
                    <div className="flex items-center justify-between rounded-lg border p-4">
                        <div className="space-y-1">
                            <Label htmlFor="announcement-enabled">
                                Show announcement
                            </Label>
                            <p className="text-sm text-muted-foreground">
                                The announcement is also hidden outside its
                                scheduled dates.
                            </p>
                        </div>
                        <Checkbox
                            id="announcement-enabled"
                            checked={form.data.settings[1].value === 'true'}
                            onCheckedChange={(checked) =>
                                setAnnouncementValue(
                                    1,
                                    String(checked === true),
                                )
                            }
                        />
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="announcement-message">Message</Label>
                        <Input
                            id="announcement-message"
                            maxLength={255}
                            value={form.data.settings[2].value}
                            onChange={(event) =>
                                setAnnouncementValue(2, event.target.value)
                            }
                        />
                    </div>

                    <div className="grid gap-4 md:grid-cols-2">
                        <div className="space-y-2">
                            <Label htmlFor="announcement-cta-label">
                                CTA label
                            </Label>
                            <Input
                                id="announcement-cta-label"
                                maxLength={80}
                                placeholder="Shop now"
                                value={form.data.settings[3].value}
                                onChange={(event) =>
                                    setAnnouncementValue(3, event.target.value)
                                }
                            />
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="announcement-cta-url">
                                CTA URL
                            </Label>
                            <Input
                                id="announcement-cta-url"
                                placeholder="/products"
                                value={form.data.settings[4].value}
                                onChange={(event) =>
                                    setAnnouncementValue(4, event.target.value)
                                }
                            />
                        </div>
                    </div>

                    <div className="grid gap-4 md:grid-cols-3">
                        <div className="space-y-2">
                            <Label htmlFor="announcement-variant">Style</Label>
                            <select
                                id="announcement-variant"
                                className="flex h-9 w-full rounded-md border border-input bg-background px-3 text-sm"
                                value={form.data.settings[5].value}
                                onChange={(event) =>
                                    setAnnouncementValue(5, event.target.value)
                                }
                            >
                                <option value="default">Default</option>
                                <option value="success">Success</option>
                                <option value="sale">Sale</option>
                                <option value="warning">Warning</option>
                            </select>
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="announcement-starts-at">
                                Starts at
                            </Label>
                            <Input
                                id="announcement-starts-at"
                                type="datetime-local"
                                value={form.data.settings[6].value}
                                onChange={(event) =>
                                    setAnnouncementValue(6, event.target.value)
                                }
                            />
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="announcement-ends-at">
                                Ends at
                            </Label>
                            <Input
                                id="announcement-ends-at"
                                type="datetime-local"
                                value={form.data.settings[7].value}
                                onChange={(event) =>
                                    setAnnouncementValue(7, event.target.value)
                                }
                            />
                        </div>
                    </div>
                </div>
            </FormSection>
            <FormActions />
        </form>
    );
}
