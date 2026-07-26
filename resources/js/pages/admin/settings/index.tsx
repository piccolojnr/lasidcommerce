import { PageHeader } from '@/components/shared/page-header/page-header';
import { AdminLayout } from '@/layouts/app/admin-layout';
import { SettingsForm } from '@/pages/admin/settings/_components/settings-form';

interface Props {
    catalog_settings: {
        new_arrival_window_days: number;
    };
    announcement_settings: {
        enabled: boolean | string;
        message: string;
        cta_label: string | null;
        cta_url: string | null;
        variant: string;
        starts_at: string | null;
        ends_at: string | null;
    };
}

export default function SettingsIndexPage({
    catalog_settings,
    announcement_settings,
}: Props) {
    return (
        <AdminLayout
            title="Settings"
            description="Configure admin-controlled system defaults."
        >
            <div className="space-y-6">
                <PageHeader
                    title="Settings"
                    description="Manage configurable application defaults and operational preferences."
                />
                <SettingsForm
                    catalogSettings={catalog_settings}
                    announcementSettings={announcement_settings}
                />
            </div>
        </AdminLayout>
    );
}
