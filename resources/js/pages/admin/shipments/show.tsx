import { PageHeader } from '@/components/shared/page-header/page-header';
import { StatusBadge } from '@/components/shared/status-badge/status-badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { AdminLayout } from '@/layouts/app/admin-layout';
import { formatDate } from '@/lib/formatters/date';

export default function ShipmentShowPage() {
    return (
        <AdminLayout title="Shipment Details" description="Inspect shipment routing and delivery events.">
            <div className="space-y-6">
                <PageHeader title="Shipment #1" description="Review delivery progress and tracking metadata." />
                <Card>
                    <CardHeader>
                        <CardTitle>Shipment summary</CardTitle>
                    </CardHeader>
                    <CardContent className="grid gap-3 text-sm md:grid-cols-2">
                        <div className="flex items-center justify-between">
                            <span className="text-muted-foreground">Status</span>
                            <StatusBadge status="pending" />
                        </div>
                        <div className="flex items-center justify-between">
                            <span className="text-muted-foreground">Tracking</span>
                            <span>TRK-001</span>
                        </div>
                        <div className="flex items-center justify-between">
                            <span className="text-muted-foreground">Carrier</span>
                            <span>Internal rider</span>
                        </div>
                        <div className="flex items-center justify-between">
                            <span className="text-muted-foreground">Shipped at</span>
                            <span>{formatDate(null)}</span>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AdminLayout>
    );
}
