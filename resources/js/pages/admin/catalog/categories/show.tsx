import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { PageHeader } from '@/components/shared/page-header/page-header';
import { StatusBadge } from '@/components/shared/status-badge/status-badge';
import { AdminLayout } from '@/layouts/app/admin-layout';

export default function CategoryShowPage() {
    return (
        <AdminLayout title="Category Details" description="Inspect category configuration.">
            <div className="space-y-6">
                <PageHeader title="Category details" description="Review this category before editing or reordering." />
                <Card>
                    <CardHeader>
                        <CardTitle>Footwear</CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-3 text-sm">
                        <div className="flex items-center justify-between">
                            <span className="text-muted-foreground">Slug</span>
                            <span>footwear</span>
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
