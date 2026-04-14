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
        <AdminLayout
            title="Shipment Details"
            description="Inspect shipment routing and delivery events."
        >
            <div className="mx-auto w-full max-w-6xl space-y-6">
                <PageHeader
                    title={`Shipment #${shipment.id}`}
                    description={`Current state: ${shipment.status.replace(/_/g, ' ')}.`}
                    actions={
                        <div className="flex items-center gap-2">
                            <Button variant="outline" asChild>
                                <Link href={ShipmentController.index.url()}>
                                    Back to shipments
                                </Link>
                            </Button>
                            {shipment.order ? (
                                <Button asChild>
                                    <Link href={OrderController.show.url(shipment.order.id)}>
                                        View order
                                    </Link>
                                </Button>
                            ) : null}
                        </div>
                    }
                />

                <div className="grid gap-6 xl:grid-cols-[2fr_1fr]">
                    <div className="space-y-6">
                        <Card>
                            <CardHeader>
                                <CardTitle>Shipment summary</CardTitle>
                            </CardHeader>
                            <CardContent className="grid gap-3 text-sm md:grid-cols-2">
                                <div className="flex items-center justify-between border-b pb-3">
                                    <span className="text-muted-foreground">Status</span>
                                    <StatusBadge status={shipment.status} />
                                </div>
                                <div className="flex items-center justify-between border-b pb-3">
                                    <span className="text-muted-foreground">Order</span>
                                    {shipment.order ? (
                                        <Link
                                            href={OrderController.show.url(shipment.order.id)}
                                            className="font-medium hover:underline"
                                        >
                                            {shipment.order.order_number}
                                        </Link>
                                    ) : (
                                        <span>N/A</span>
                                    )}
                                </div>
                                <div className="flex items-center justify-between border-b pb-3">
                                    <span className="text-muted-foreground">Customer</span>
                                    <span>{shipment.order?.email ?? 'N/A'}</span>
                                </div>
                                <div className="flex items-center justify-between border-b pb-3">
                                    <span className="text-muted-foreground">Tracking number</span>
                                    <span>{shipment.tracking_number ?? 'N/A'}</span>
                                </div>
                                <div className="flex items-center justify-between border-b pb-3">
                                    <span className="text-muted-foreground">Carrier</span>
                                    <span>{shipment.carrier_name ?? 'N/A'}</span>
                                </div>
                                <div className="flex items-center justify-between border-b pb-3">
                                    <span className="text-muted-foreground">Tracking URL</span>
                                    {shipment.tracking_url ? (
                                        <a
                                            href={shipment.tracking_url}
                                            target="_blank"
                                            rel="noreferrer"
                                            className="text-primary hover:underline"
                                        >
                                            Open link
                                        </a>
                                    ) : (
                                        <span>N/A</span>
                                    )}
                                </div>
                                <div className="flex items-center justify-between border-b pb-3">
                                    <span className="text-muted-foreground">Packed at</span>
                                    <span>{formatDate(shipment.packed_at)}</span>
                                </div>
                                <div className="flex items-center justify-between border-b pb-3">
                                    <span className="text-muted-foreground">Shipped at</span>
                                    <span>{formatDate(shipment.shipped_at)}</span>
                                </div>
                                <div className="flex items-center justify-between border-b pb-3">
                                    <span className="text-muted-foreground">Delivered at</span>
                                    <span>{formatDate(shipment.delivered_at)}</span>
                                </div>
                                <div className="flex items-center justify-between border-b pb-3">
                                    <span className="text-muted-foreground">Failed at</span>
                                    <span>{formatDate(shipment.failed_at)}</span>
                                </div>
                                <div className="flex items-center justify-between">
                                    <span className="text-muted-foreground">Returned at</span>
                                    <span>{formatDate(shipment.returned_at)}</span>
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>Shipment items</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                {shipment.items.length === 0 ? (
                                    <EmptyState
                                        title="No shipment items"
                                        description="This shipment has no linked items, which would make it fairly useless."
                                    />
                                ) : (
                                    <div className="space-y-3">
                                        {shipment.items.map((item) => (
                                            <div key={item.id} className="rounded-lg border p-4">
                                                <div className="flex flex-col gap-2 md:flex-row md:items-start md:justify-between">
                                                    <div>
                                                        <p className="font-medium">
                                                            {item.product_name ?? 'Unknown product'}
                                                        </p>
                                                        <p className="text-sm text-muted-foreground">
                                                            {item.variant_name ?? 'Base product'}
                                                            {item.sku ? ` • ${item.sku}` : ''}
                                                        </p>
                                                    </div>
                                                    <div className="text-right text-sm">
                                                        <p>Qty shipped: {item.quantity}</p>
                                                        <p className="text-muted-foreground">
                                                            Order item #{item.order_item_id}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    </div>

                    <div className="space-y-6">
                        <Card>
                            <CardHeader>
                                <CardTitle>Update status</CardTitle>
                            </CardHeader>
                            <CardContent>
                                {allowedStatuses.length === 0 ? (
                                    <p className="text-sm text-muted-foreground">
                                        No further status transitions are allowed for this shipment.
                                    </p>
                                ) : (
                                    <form
                                        className="space-y-4"
                                        onSubmit={(event) => {
                                            event.preventDefault();
                                            statusForm.patch(
                                                ShipmentStatusController.update.url(shipment),
                                                { preserveScroll: true },
                                            );
                                        }}
                                    >
                                        <div className="space-y-2">
                                            <Label htmlFor="status">Next status</Label>
                                            <Select
                                                value={statusForm.data.status}
                                                onValueChange={(value) =>
                                                    statusForm.setData('status', value)
                                                }
                                            >
                                                <SelectTrigger id="status" className="w-full">
                                                    <SelectValue placeholder="Select a status" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    {allowedStatuses.map((status) => (
                                                        <SelectItem key={status} value={status}>
                                                            {status.replace(/\b\w/g, (character) =>
                                                                character.toUpperCase(),
                                                            )}
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
                                                onChange={(event) =>
                                                    statusForm.setData(
                                                        'note',
                                                        event.target.value,
                                                    )
                                                }
                                                placeholder="Optional operational note"
                                            />
                                            <FieldError message={statusForm.errors.note} />
                                        </div>
                                        <Button
                                            type="submit"
                                            disabled={
                                                statusForm.processing ||
                                                !statusForm.data.status
                                            }
                                            className="w-full"
                                        >
                                            {statusForm.processing
                                                ? 'Updating…'
                                                : 'Update status'}
                                        </Button>
                                    </form>
                                )}
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>Assigned people</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-2 text-sm">
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

                        <Card>
                            <CardHeader>
                                <CardTitle>Warehouse</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-2 text-sm">
                                {shipment.warehouse_location ? (
                                    <>
                                        <div className="flex items-center justify-between">
                                            <span className="text-muted-foreground">Name</span>
                                            <span>{shipment.warehouse_location.name}</span>
                                        </div>
                                        <div className="flex items-center justify-between">
                                            <span className="text-muted-foreground">Code</span>
                                            <span>{shipment.warehouse_location.code}</span>
                                        </div>
                                        <div className="flex items-center justify-between">
                                            <span className="text-muted-foreground">Location</span>
                                            <span>
                                                {[
                                                    shipment.warehouse_location.city,
                                                    shipment.warehouse_location.region,
                                                    shipment.warehouse_location.country,
                                                ]
                                                    .filter(Boolean)
                                                    .join(', ')}
                                            </span>
                                        </div>
                                    </>
                                ) : (
                                    <p className="text-muted-foreground">
                                        No warehouse location is attached to this shipment.
                                    </p>
                                )}
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>Shipping method</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-2 text-sm">
                                {shipment.shipping_method ? (
                                    <>
                                        <div className="flex items-center justify-between">
                                            <span className="text-muted-foreground">Name</span>
                                            <span>{shipment.shipping_method.name}</span>
                                        </div>
                                        <div className="flex items-center justify-between">
                                            <span className="text-muted-foreground">Code</span>
                                            <span>{shipment.shipping_method.code}</span>
                                        </div>
                                        <div className="flex items-center justify-between">
                                            <span className="text-muted-foreground">Type</span>
                                            <span>{shipment.shipping_method.method_type}</span>
                                        </div>
                                    </>
                                ) : (
                                    <p className="text-muted-foreground">
                                        No shipping method is attached to this shipment.
                                    </p>
                                )}
                            </CardContent>
                        </Card>

                        {shipment.notes ? (
                            <Card>
                                <CardHeader>
                                    <CardTitle>Notes</CardTitle>
                                </CardHeader>
                                <CardContent className="text-sm text-muted-foreground whitespace-pre-wrap">
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
