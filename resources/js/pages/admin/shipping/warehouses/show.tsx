import { Link, router } from '@inertiajs/react';
import { PageHeader } from '@/components/shared/page-header/page-header';
import { StatusBadge } from '@/components/shared/status-badge/status-badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { AdminLayout } from '@/layouts/app/admin-layout';
import { formatDate } from '@/lib/formatters/date';
import type { AdminWarehouseDetail } from '@/types/admin/shipping';

export default function WarehouseShowPage({
    warehouse,
}: {
    warehouse: AdminWarehouseDetail;
}) {
    return (
        <AdminLayout title="Warehouse">
            <div className="mx-auto w-full max-w-7xl space-y-8">
                <PageHeader
                    title={warehouse.name}
                    description="Review fulfillment origin details, contact points, and warehouse status."
                    actions={
                        <div className="flex items-center gap-2">
                            <Button variant="outline" asChild>
                                <Link href="/admin/shipping/warehouse-locations">
                                    Back to warehouses
                                </Link>
                            </Button>
                            <Button variant="outline" asChild>
                                <Link
                                    href={`/admin/shipping/warehouse-locations/${warehouse.id}/edit`}
                                >
                                    Edit warehouse
                                </Link>
                            </Button>
                            <Button
                                variant="outline"
                                className="text-destructive hover:text-destructive"
                                onClick={() => {
                                    if (
                                        window.confirm(
                                            `Delete warehouse "${warehouse.name}"?`,
                                        )
                                    ) {
                                        router.delete(
                                            `/admin/shipping/warehouse-locations/${warehouse.id}`,
                                        );
                                    }
                                }}
                            >
                                Delete
                            </Button>
                        </div>
                    }
                />
                <div className="grid gap-4 lg:grid-cols-3">
                    <Card className="border-border/70 bg-muted/30 lg:col-span-2">
                        <CardHeader className="space-y-3">
                            <div className="flex items-center justify-between gap-3">
                                <p className="text-xs font-semibold tracking-[0.24em] text-muted-foreground uppercase">
                                    Warehouse profile
                                </p>
                                <StatusBadge
                                    status={
                                        warehouse.is_active
                                            ? 'active'
                                            : 'inactive'
                                    }
                                />
                            </div>
                            <CardTitle className="text-2xl">
                                {warehouse.name}
                            </CardTitle>
                            <p className="max-w-2xl text-sm leading-6 text-muted-foreground">
                                {warehouse.address_line_1}
                                {warehouse.address_line_2
                                    ? `, ${warehouse.address_line_2}`
                                    : ''}
                                , {warehouse.city}
                                {warehouse.region
                                    ? `, ${warehouse.region}`
                                    : ''}
                                , {warehouse.country}.
                            </p>
                        </CardHeader>
                        <CardContent className="grid gap-4 border-t border-border/70 pt-6 md:grid-cols-4">
                            <div>
                                <p className="text-xs font-semibold tracking-[0.2em] text-muted-foreground uppercase">
                                    Code
                                </p>
                                <p className="mt-2 font-mono font-semibold">
                                    {warehouse.code}
                                </p>
                            </div>
                            <div>
                                <p className="text-xs font-semibold tracking-[0.2em] text-muted-foreground uppercase">
                                    Shipments
                                </p>
                                <p className="mt-2 font-semibold">
                                    {warehouse.shipments_count}
                                </p>
                            </div>
                            <div>
                                <p className="text-xs font-semibold tracking-[0.2em] text-muted-foreground uppercase">
                                    Phone
                                </p>
                                <p className="mt-2 font-semibold">
                                    {warehouse.phone ?? 'N/A'}
                                </p>
                            </div>
                            <div>
                                <p className="text-xs font-semibold tracking-[0.2em] text-muted-foreground uppercase">
                                    Email
                                </p>
                                <p className="mt-2 font-semibold">
                                    {warehouse.email ?? 'N/A'}
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                    <Card className="border-border/70 bg-primary/5">
                        <CardHeader>
                            <CardTitle className="text-xl">
                                Operational state
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-3 text-sm">
                            <div className="flex items-center justify-between">
                                <span className="text-muted-foreground">
                                    Default warehouse
                                </span>
                                <span className="font-medium">
                                    {warehouse.is_default ? 'Yes' : 'No'}
                                </span>
                            </div>
                            <div className="flex items-center justify-between">
                                <span className="text-muted-foreground">
                                    Created
                                </span>
                                <span className="font-medium">
                                    {formatDate(warehouse.created_at)}
                                </span>
                            </div>
                            <div className="flex items-center justify-between">
                                <span className="text-muted-foreground">
                                    Updated
                                </span>
                                <span className="font-medium">
                                    {formatDate(warehouse.updated_at)}
                                </span>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AdminLayout>
    );
}
