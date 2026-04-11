import { PageHeader } from '@/components/shared/page-header/page-header';
import { StatusBadge } from '@/components/shared/status-badge/status-badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { AdminLayout } from '@/layouts/app/admin-layout';

export default function UserShowPage() {
    return (
        <AdminLayout title="User Details" description="Inspect account profile and authorization state.">
            <div className="space-y-6">
                <PageHeader title="User details" description="Review profile information, status, and access role setup." />
                <Card>
                    <CardHeader>
                        <CardTitle>Jane Doe</CardTitle>
                    </CardHeader>
                    <CardContent className="grid gap-3 text-sm md:grid-cols-2">
                        <div className="flex items-center justify-between">
                            <span className="text-muted-foreground">Email</span>
                            <span>jane@example.com</span>
                        </div>
                        <div className="flex items-center justify-between">
                            <span className="text-muted-foreground">Status</span>
                            <StatusBadge status="active" />
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AdminLayout>
    );
}
