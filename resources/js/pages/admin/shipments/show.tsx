import { Link, useForm } from '@inertiajs/react';
import * as OrderController from '@/actions/App/Http/Controllers/Admin/Orders/OrderController';
import * as ShipmentController from '@/actions/App/Http/Controllers/Admin/Shipments/ShipmentController';
import * as ShipmentStatusController from '@/actions/App/Http/Controllers/Admin/Shipments/ShipmentStatusController';
import { EmptyState } from '@/components/shared/empty-state/empty-state';
import { FieldError } from '@/components/shared/forms/field-error';
import { PageHeader } from '@/components/shared/page-header/page-header';
import { StatusBadge } from '@/components/shared/status-badge/status-badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { AdminLayout } from '@/layouts/app/admin-layout';
import { formatDate } from '@/lib/formatters/date';
import type { AdminShipmentDetail } from '@/types/admin/shipment';

interface Props {
    shipment: AdminShipmentDetail;
    allowedStatuses: string[];
}

export default function ShipmentShowPage({ shipment, allowedStatuses }: Props) {
    const statusForm = useForm({
        status: '',
        note: '',
    });

    return (
        <AdminLayout title="Shipment Details" description="Inspect shipment routing and delivery events.">
            <div className="mx-auto w-full max-w-7xl space-y-8">
                <PageHeader
                    title={`Shipment #${shipment.id}`}
                    description={`Current state: ${shipment.status.replace(/_/g, ' ')}.`}
                    actions={
                        <div className="flex items-center gap-2">
                            <Button variant="outline" asChild>
                                <Link href={ShipmentController.index.url()}>Back to shipments</Link>
                            </Button>
                            {shipment.order ? (
                                <Button asChild>
                                    <Link href={OrderController.show.url(shipment.order.id)}>View order</Link>
                                </Button>
                            ) : null}
                        </div>
                    }
                />

                <div className="grid gap-4 lg:grid-cols-3">
                    <Card className="border-border/70 bg-muted/30 lg:col-span-2">
                        <CardHeader className="space-y-3">
                            <div className="flex items-center justify-between gap-3">
                                <p className="text-xs font-semibold uppercase tracking-[0.24em] text-muted-foreground">
                                    Shipment profile
                                </p>
                                <StatusBadge status={shipment.status} />
                            </div>
                            <CardTitle className="text-2xl">
                                {shipment.tracking_number ?? `Shipment #${shipment.id}`}
                            </CardTitle>
                            <p className="max-w-2xl text-sm leading-6 text-muted-foreground">
                                {shipment.carrier_name ?? 'No carrier assigned'} moving order {shipment.order?.order_number ?? 'without linked order context'}.
                            </p>
                        </CardHeader>
                        <CardContent className="grid gap-4 border-t border-border/70 pt-6 md:grid-cols-3">
                            <div>
                                <p className="text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">Customer</p>
                                <p className="mt-2 font-semibold">{shipment.order?.email ?? 'N/A'}</p>
                            </div>
                            <div>
                                <p className="text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">Carrier</p>
                                <p className="mt-2 font-semibold">{shipment.carrier_name ?? 'N/A'}</p>
                            </div>
                            <div>
                                <p className="text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">Items</p>
                                <p className="mt-2 font-semibold">{shipment.items.length}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <Card className="border-border/70 bg-primary/5">
                        <CardHeader className="space-y-2">
                            <p className="text-xs font-semibold uppercase tracking-[0.24em] text-primary">
                                Milestones
                            </p>
                            <CardTitle className="text-xl">Delivery timeline</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-3 text-sm">
                            <div className="flex items-center justify-between">
                                <span className="text-muted-foreground">Packed</span>
                                <span className="font-medium">{formatDate(shipment.packed_at)}</span>
                            </div>
                            <div className="flex items-center justify-between">
                                <span className="text-muted-foreground">Shipped</span>
                                <span className="font-medium">{formatDate(shipment.shipped_at)}</span>
                            </div>
                            <div className="flex items-center justify-between">
                                <span className="text-muted-foreground">Delivered</span>
                                <span className="font-medium">{formatDate(shipment.delivered_at)}</span>
                            </div>
                            <div className="flex items-center justify-between">
                                <span className="text-muted-foreground">Failed</span>
                                <span className="font-medium">{formatDate(shipment.failed_at)}</span>
                            </div>
                            <div className="flex items-center justify-between">
                                <span className="text-muted-foreground">Returned</span>
                                <span className="font-medium">{formatDate(shipment.returned_at)}</span>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <div className="grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
                    <div className="space-y-6">
                        <Card className="overflow-hidden border-border/70 pt-0">
                            <CardHeader className="border-b border-border/70 bg-muted/30 py-6">
                                <CardTitle>Shipment items</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-3 p-6">
                                {shipment.items.length === 0 ? (
                                    <EmptyState
                                        title="No shipment items"
                                        description="This shipment has no linked items, which would make it fairly useless."
                                    />
                                ) : (
                                    shipment.items.map((item) => (
                                        <div key={item.id} className="rounded-2xl border border-border/70 bg-background/80 p-4">
                                            <div className="flex flex-col gap-2 md:flex-row md:items-start md:justify-between">
                                                <div>
                                                    <p className="font-medium">{item.product_name ?? 'Unknown product'}</p>
                                                    <p className="text-sm text-muted-foreground">
                                                        {item.variant_name ?? 'Base product'}
                                                        {item.sku ? ` • ${item.sku}` : ''}
                                                    </p>
                                                </div>
                                                <div className="text-right text-sm">
                                                    <p>Qty shipped: {item.quantity}</p>
                                                    <p className="text-muted-foreground">Order item #{item.order_item_id}</p>
                                                </div>
                                            </div>
                                        </div>
                                    ))
                                )}
                            </CardContent>
                        </Card>
                    </div>

                    <div className="space-y-6">
                        <Card className="overflow-hidden border-border/70 pt-0">
                            <CardHeader className="border-b border-border/70 bg-muted/30 py-6">
                                <CardTitle>Update status</CardTitle>
                            </CardHeader>
                            <CardContent className="p-6">
                                {allowedStatuses.length === 0 ? (
                                    <p className="text-sm text-muted-foreground">
                                        No further status transitions are allowed for this shipment.
                                    </p>
                                ) : (
                                    <form
                                        className="space-y-4"
                                        onSubmit={(event) => {
                                            event.preventDefault();
                                            statusForm.patch(ShipmentStatusController.update.url(shipment), {
                                                preserveScroll: true,
                                            });
                                        }}
                                    >
                                        <div className="space-y-2">
                                            <Label htmlFor="status">Next status</Label>
                                            <Select
                                                value={statusForm.data.status}
                                                onValueChange={(value) => statusForm.setData('status', value)}
                                            >
                                                <SelectTrigger id="status" className="w-full">
                                                    <SelectValue placeholder="Select a status" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    {allowedStatuses.map((status) => (
                                                        <SelectItem key={status} value={status}>
                                                            {status.replace(/\b\w/g, (character) => character.toUpperCase())}
                                                        </SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                            <FieldError message={statusForm.errors.status} />
                                        </div>
                                        <div className="space-y-2">
                                            <Label htmlFor="note">Note</Label>
                                            <Input
                                                id="note"
                                                name="note"
                                                value={statusForm.data.note}
                                                onChange={(event) => statusForm.setData('note', event.target.value)}
                                                placeholder="Optional operational note"
                                            />
                                            <FieldError message={statusForm.errors.note} />
                                        </div>
                                        <Button
                                            type="submit"
                                            disabled={statusForm.processing || !statusForm.data.status}
                                            className="w-full"
                                        >
                                            {statusForm.processing ? 'Updating…' : 'Update status'}
                                        </Button>
                                    </form>
                                )}
                            </CardContent>
                        </Card>

                        <Card className="overflow-hidden border-border/70 pt-0">
                            <CardHeader className="border-b border-border/70 bg-muted/30 py-6">
                                <CardTitle>Assigned people</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-2 p-6 text-sm">
                                <div className="flex items-center justify-between">
                                    <span className="text-muted-foreground">Rider</span>
                                    <span>{shipment.rider_name ?? 'N/A'}</span>
                                </div>
                                <div className="flex items-center justify-between">
                                    <span className="text-muted-foreground">Phone</span>
                                    <span>{shipment.rider_phone ?? 'N/A'}</span>
                                </div>
                            </CardContent>
                        </Card>

                        <Card className="overflow-hidden border-border/70 pt-0">
                            <CardHeader className="border-b border-border/70 bg-muted/30 py-6">
                                <CardTitle>Warehouse and method</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4 p-6 text-sm">
                                {shipment.warehouse_location ? (
                                    <div className="space-y-2">
                                        <p className="font-medium">{shipment.warehouse_location.name}</p>
                                        <p className="text-muted-foreground">{shipment.warehouse_location.code}</p>
                                        <p className="text-muted-foreground">
                                            {[
                                                shipment.warehouse_location.city,
                                                shipment.warehouse_location.region,
                                                shipment.warehouse_location.country,
                                            ]
                                                .filter(Boolean)
                                                .join(', ')}
                                        </p>
                                    </div>
                                ) : (
                                    <p className="text-muted-foreground">No warehouse location is attached.</p>
                                )}
                                {shipment.shipping_method ? (
                                    <div className="border-t border-border/60 pt-4">
                                        <p className="font-medium">{shipment.shipping_method.name}</p>
                                        <p className="text-muted-foreground">
                                            {shipment.shipping_method.code} • {shipment.shipping_method.method_type}
                                        </p>
                                    </div>
                                ) : null}
                            </CardContent>
                        </Card>

                        {shipment.notes ? (
                            <Card className="overflow-hidden border-border/70 pt-0">
                                <CardHeader className="border-b border-border/70 bg-muted/30 py-6">
                                    <CardTitle>Notes</CardTitle>
                                </CardHeader>
                                <CardContent className="whitespace-pre-wrap p-6 text-sm text-muted-foreground">
                                    {shipment.notes}
                                </CardContent>
                            </Card>
                        ) : null}
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
