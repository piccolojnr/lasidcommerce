import { PageHeader } from '@/components/shared/page-header/page-header';
import { StatusBadge } from '@/components/shared/status-badge/status-badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { AdminLayout } from '@/layouts/app/admin-layout';
import { formatMoney } from '@/lib/formatters/money';

export default function OrderShowPage() {
    return (
        <AdminLayout title="Order Details" description="Inspect order, payment, and fulfillment state.">
            <div className="space-y-6">
                <PageHeader title="Order #ORD-0001" description="Review the current lifecycle and customer snapshot." />
                <Card>
                    <CardHeader>
                        <CardTitle>Order summary</CardTitle>
                    </CardHeader>
                    <CardContent className="grid gap-3 text-sm md:grid-cols-2">
                        <div className="flex items-center justify-between">
                            <span className="text-muted-foreground">Status</span>
                            <StatusBadge status="pending" />
                        </div>
                        <div className="flex items-center justify-between">
                            <span className="text-muted-foreground">Payment</span>
                            <StatusBadge status="unpaid" />
                        </div>
                        <div className="flex items-center justify-between">
                            <span className="text-muted-foreground">Fulfillment</span>
                            <StatusBadge status="unfulfilled" />
                        </div>
                        <div className="flex items-center justify-between">
                            <span className="text-muted-foreground">Total</span>
                            <span>{formatMoney(65000)}</span>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AdminLayout>
    );
}
