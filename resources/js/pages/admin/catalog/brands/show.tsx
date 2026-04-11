import { PageHeader } from '@/components/shared/page-header/page-header';
import { StatusBadge } from '@/components/shared/status-badge/status-badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { AdminLayout } from '@/layouts/app/admin-layout';

export default function BrandShowPage() {
    return (
        <AdminLayout title="Brand Details" description="Inspect brand configuration.">
            <div className="space-y-6">
                <PageHeader title="Brand details" description="Review this brand's storefront configuration." />
                <Card>
                    <CardHeader>
                        <CardTitle>Acme</CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-3 text-sm">
                        <div className="flex items-center justify-between">
                            <span className="text-muted-foreground">Slug</span>
                            <span>acme</span>
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
