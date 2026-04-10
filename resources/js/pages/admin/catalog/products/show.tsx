import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { PageHeader } from '@/components/shared/page-header/page-header';
import { StatusBadge } from '@/components/shared/status-badge/status-badge';
import { formatMoney } from '@/lib/formatters/money';
import { AdminLayout } from '@/layouts/app/admin-layout';

export default function ProductShowPage() {
    return (
        <AdminLayout title="Product Details" description="Inspect product configuration and merchandising state.">
            <div className="space-y-6">
                <PageHeader title="Product details" description="Review the current product setup before making changes." />
                <Card>
                    <CardHeader>
                        <CardTitle>Classic sneaker</CardTitle>
                    </CardHeader>
                    <CardContent className="grid gap-3 text-sm md:grid-cols-2">
                        <div className="flex items-center justify-between">
                            <span className="text-muted-foreground">SKU</span>
                            <span>SNK-001</span>
                        </div>
                        <div className="flex items-center justify-between">
                            <span className="text-muted-foreground">Status</span>
                            <StatusBadge status="draft" />
                        </div>
                        <div className="flex items-center justify-between">
                            <span className="text-muted-foreground">Base price</span>
                            <span>{formatMoney(25000)}</span>
                        </div>
                        <div className="flex items-center justify-between">
                            <span className="text-muted-foreground">Type</span>
                            <span>Physical</span>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AdminLayout>
    );
}
