import { PageHeader } from '@/components/shared/page-header/page-header';
import { AdminLayout } from '@/layouts/app/admin-layout';
import { SettingsForm } from '@/pages/admin/settings/_components/settings-form';

export default function SettingsIndexPage() {
    return (
        <AdminLayout title="Settings" description="Configure admin-controlled system defaults.">
            <div className="space-y-6">
                <PageHeader title="Settings" description="Manage configurable application defaults and operational preferences." />
                <SettingsForm />
            </div>
        </AdminLayout>
    );
}
