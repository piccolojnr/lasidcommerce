import { Link } from '@inertiajs/react';
import * as ShipmentController from '@/actions/App/Http/Controllers/Admin/Shipments/ShipmentController';
import { EmptyState } from '@/components/shared/empty-state/empty-state';
import { StatusBadge } from '@/components/shared/status-badge/status-badge';
import { Button } from '@/components/ui/button';
import { formatDate } from '@/lib/formatters/date';
import type { AdminShipment } from '@/types/admin/shipment';

interface ShipmentTableProps {
    shipments: AdminShipment[];
}

export function ShipmentTable({ shipments }: ShipmentTableProps) {
    if (shipments.length === 0) {
        return (
            <div className="rounded-3xl border border-dashed border-border/70 bg-muted/30 px-6 py-14 text-center">
                <EmptyState
                    title="No shipments found"
                    description="Try a different search or status filter. Right now this list is empty."
                />
            </div>
        );
    }

    return (
        <div className="overflow-hidden rounded-3xl border border-border/70 bg-background shadow-sm">
            <div className="overflow-x-auto">
                <table className="min-w-full text-sm">
                    <thead className="bg-muted/35">
                        <tr>
                            <th className="px-5 py-4 text-left font-medium text-muted-foreground">Shipment</th>
                            <th className="px-5 py-4 text-left font-medium text-muted-foreground">Route context</th>
                            <th className="px-5 py-4 text-left font-medium text-muted-foreground">Tracking</th>
                            <th className="px-5 py-4 text-left font-medium text-muted-foreground">State</th>
                            <th className="px-5 py-4 text-left font-medium text-muted-foreground">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        {shipments.map((shipment) => (
                            <tr key={shipment.id} className="border-t border-border/60 align-top">
                                <td className="px-5 py-4">
                                    <div className="space-y-1">
                                        <Link
                                            href={ShipmentController.show.url(shipment)}
                                            className="font-semibold text-foreground transition hover:text-primary"
                                        >
                                            {shipment.tracking_number ?? `Shipment #${shipment.id}`}
                                        </Link>
                                        <p className="text-xs text-muted-foreground">
                                            {shipment.order?.order_number ?? 'No linked order'}
                                        </p>
                                    </div>
                                </td>
                                <td className="px-5 py-4 text-muted-foreground">
                                    <div className="space-y-1 text-xs">
                                        <p>{shipment.carrier_name ?? 'No carrier assigned'}</p>
                                        <p>{shipment.order?.email ?? 'No customer email'}</p>
                                    </div>
                                </td>
                                <td className="px-5 py-4 text-muted-foreground">
                                    <div className="space-y-1 text-xs">
                                        <p>Tracking: {shipment.tracking_number ?? 'Pending'}</p>
                                        <p>Shipped: {formatDate(shipment.shipped_at)}</p>
                                    </div>
                                </td>
                                <td className="px-5 py-4">
                                    <StatusBadge status={shipment.status} />
                                </td>
                                <td className="px-5 py-4">
                                    <Button variant="outline" size="sm" asChild>
                                        <Link href={ShipmentController.show.url(shipment)}>View shipment</Link>
                                    </Button>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </div>
    );
}
