import { Link, useForm } from '@inertiajs/react';
import * as OrderController from '@/actions/App/Http/Controllers/Admin/Orders/OrderController';
import * as ShipmentController from '@/actions/App/Http/Controllers/Admin/Shipments/ShipmentController';
import * as ShipmentStatusController from '@/actions/App/Http/Controllers/Admin/Shipments/ShipmentStatusController';
import { FieldError } from '@/components/shared/forms/field-error';
import { FulfillmentGuideDialog } from '@/components/shared/guides/fulfillment-guide-dialog';
import { PageHeader } from '@/components/shared/page-header/page-header';
import { StatusBadge } from '@/components/shared/status-badge/status-badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    EMPTY_SENTINEL,
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { AdminLayout } from '@/layouts/app/admin-layout';
import { formatDate } from '@/lib/formatters/date';
import type {
    AdminShipmentDetail,
    AdminShipmentShippingMethodOption,
    AdminShipmentWarehouseOption,
} from '@/types/admin/shipment';

interface Props {
    shipment: AdminShipmentDetail;
    allowedStatuses: string[];
    availableWarehouses: AdminShipmentWarehouseOption[];
    availableShippingMethods: AdminShipmentShippingMethodOption[];
}

export default function ShipmentShowPage({
    shipment,
    allowedStatuses,
    availableWarehouses,
    availableShippingMethods,
}: Props) {
    const statusForm = useForm({ status: '', note: '' });
    const updateForm = useForm({
        warehouse_location_id: shipment.warehouse_location
            ? shipment.warehouse_location.id.toString()
            : '',
        shipping_method_id: shipment.shipping_method
            ? shipment.shipping_method.id.toString()
            : '',
        carrier_name: shipment.carrier_name ?? '',
        tracking_number: shipment.tracking_number ?? '',
        tracking_url: shipment.tracking_url ?? '',
        rider_name: shipment.rider_name ?? '',
        rider_phone: shipment.rider_phone ?? '',
        notes: shipment.notes ?? '',
    });

    const runStatusUpdate = (status: string) => {
        statusForm.transform((data) => ({ ...data, status }));
        statusForm.patch(ShipmentStatusController.update.url(shipment), {
            preserveScroll: true,
        });
    };

    const submitShipmentUpdate = () => {
        updateForm.transform((data) => ({
            ...data,
            warehouse_location_id:
                data.warehouse_location_id === ''
                    ? null
                    : Number(data.warehouse_location_id),
            shipping_method_id:
                data.shipping_method_id === ''
                    ? null
                    : Number(data.shipping_method_id),
        }));
        updateForm.patch(ShipmentController.update.url(shipment), {
            preserveScroll: true,
        });
    };

    return (
        <AdminLayout
            title="Shipment Details"
            description="Inspect shipment routing and delivery events."
        >
            <div className="mx-auto w-full max-w-7xl space-y-8">
                <PageHeader
                    title={`Shipment #${shipment.id}`}
                    description={`Current state: ${shipment.status.replace(/_/g, ' ')}.`}
                    actions={
                        <div className="flex items-center gap-2">
                            <FulfillmentGuideDialog triggerLabel="Workflow guide" />
                            <Button variant="outline" asChild>
                                <Link href={ShipmentController.index.url()}>
                                    Back to shipments
                                </Link>
                            </Button>
                            {shipment.order ? (
                                <Button asChild>
                                    <Link
                                        href={OrderController.show.url(
                                            shipment.order.id,
                                        )}
                                    >
                                        View order
                                    </Link>
                                </Button>
                            ) : null}
                        </div>
                    }
                />

                <div className="grid gap-4 lg:grid-cols-[1.25fr_0.75fr]">
                    <Card className="border-border/70 bg-muted/30">
                        <CardHeader className="space-y-3">
                            <div className="flex items-center justify-between gap-3">
                                <p className="text-xs font-semibold tracking-[0.24em] text-muted-foreground uppercase">
                                    Shipment attempt
                                </p>
                                <div className="flex flex-wrap gap-2">
                                    <StatusBadge status={shipment.status} />
                                    {shipment.order_fulfillment_status ? (
                                        <StatusBadge
                                            status={
                                                shipment.order_fulfillment_status
                                            }
                                        />
                                    ) : null}
                                    {shipment.order_shipping_summary ? (
                                        <StatusBadge
                                            status={
                                                shipment.order_shipping_summary
                                            }
                                        />
                                    ) : null}
                                </div>
                            </div>
                            <CardTitle className="text-2xl">
                                {shipment.tracking_number ??
                                    `Shipment #${shipment.id}`}
                            </CardTitle>
                            <p className="text-sm leading-6 text-muted-foreground">
                                Shipment status tracks package movement only. If
                                this attempt fails or is returned, create the
                                next shipment from the order page instead of
                                reusing this record.
                            </p>
                        </CardHeader>
                        <CardContent className="grid gap-4 border-t border-border/70 pt-6 md:grid-cols-4">
                            <div>
                                <p className="text-xs font-semibold tracking-[0.2em] text-muted-foreground uppercase">
                                    Order
                                </p>
                                <p className="mt-2 font-semibold">
                                    {shipment.order?.order_number ?? 'N/A'}
                                </p>
                            </div>
                            <div>
                                <p className="text-xs font-semibold tracking-[0.2em] text-muted-foreground uppercase">
                                    Carrier
                                </p>
                                <p className="mt-2 font-semibold">
                                    {shipment.carrier_name ?? 'N/A'}
                                </p>
                            </div>
                            <div>
                                <p className="text-xs font-semibold tracking-[0.2em] text-muted-foreground uppercase">
                                    Items
                                </p>
                                <p className="mt-2 font-semibold">
                                    {shipment.items.length}
                                </p>
                            </div>
                            <div>
                                <p className="text-xs font-semibold tracking-[0.2em] text-muted-foreground uppercase">
                                    Remaining order qty
                                </p>
                                <p className="mt-2 font-semibold">
                                    {shipment.order_remaining_quantity}
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                    <Card className="border-border/70 bg-primary/5">
                        <CardHeader>
                            <CardTitle className="text-xl">
                                Milestones
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-3 text-sm">
                            <div className="flex items-center justify-between">
                                <span className="text-muted-foreground">
                                    Packed
                                </span>
                                <span className="font-medium">
                                    {formatDate(shipment.packed_at)}
                                </span>
                            </div>
                            <div className="flex items-center justify-between">
                                <span className="text-muted-foreground">
                                    Shipped
                                </span>
                                <span className="font-medium">
                                    {formatDate(shipment.shipped_at)}
                                </span>
                            </div>
                            <div className="flex items-center justify-between">
                                <span className="text-muted-foreground">
                                    Delivered
                                </span>
                                <span className="font-medium">
                                    {formatDate(shipment.delivered_at)}
                                </span>
                            </div>
                            <div className="flex items-center justify-between">
                                <span className="text-muted-foreground">
                                    Failed
                                </span>
                                <span className="font-medium">
                                    {formatDate(shipment.failed_at)}
                                </span>
                            </div>
                            <div className="flex items-center justify-between">
                                <span className="text-muted-foreground">
                                    Returned
                                </span>
                                <span className="font-medium">
                                    {formatDate(shipment.returned_at)}
                                </span>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <div className="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
                    <div className="space-y-6">
                        {(shipment.status === 'failed' ||
                            shipment.status === 'returned') &&
                        shipment.can_reship_from_order ? (
                            <Card className="border-border/70 bg-amber-500/10">
                                <CardContent className="p-6 text-sm text-muted-foreground">
                                    This shipment attempt is closed. The order
                                    still has remaining quantity, so create the
                                    next shipment from the order page.
                                </CardContent>
                            </Card>
                        ) : null}

                        <Card className="overflow-hidden border-border/70 pt-0">
                            <CardHeader className="border-b border-border/70 bg-muted/30 py-6">
                                <CardTitle>Shipment controls</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4 p-6">
                                <div className="rounded-2xl border border-border/70 bg-muted/20 p-4 text-sm text-muted-foreground">
                                    Use these controls only for packing,
                                    dispatch, delivery, failure, return, or
                                    cancellation of this shipment.
                                </div>
                                {allowedStatuses.length > 0 ? (
                                    <div className="flex flex-wrap gap-2">
                                        {allowedStatuses.map((status) => (
                                            <Button
                                                key={status}
                                                type="button"
                                                variant={
                                                    allowedStatuses.length === 1
                                                        ? 'default'
                                                        : 'outline'
                                                }
                                                disabled={statusForm.processing}
                                                onClick={() =>
                                                    runStatusUpdate(status)
                                                }
                                            >
                                                {status.replace(
                                                    /\b\w/g,
                                                    (character) =>
                                                        character.toUpperCase(),
                                                )}
                                            </Button>
                                        ))}
                                    </div>
                                ) : (
                                    <p className="text-sm text-muted-foreground">
                                        No further status transitions are
                                        allowed for this shipment.
                                    </p>
                                )}
                                <form
                                    className="space-y-4 border-t border-border/70 pt-4"
                                    onSubmit={(event) => {
                                        event.preventDefault();
                                        statusForm.patch(
                                            ShipmentStatusController.update.url(
                                                shipment,
                                            ),
                                            { preserveScroll: true },
                                        );
                                    }}
                                >
                                    <div className="space-y-2">
                                        <Label htmlFor="status">
                                            Advanced shipment update
                                        </Label>
                                        <Select
                                            value={statusForm.data.status}
                                            onValueChange={(value) =>
                                                statusForm.setData(
                                                    'status',
                                                    value,
                                                )
                                            }
                                        >
                                            <SelectTrigger id="status">
                                                <SelectValue placeholder="Select a status" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectGroup>
                                                    {allowedStatuses.map(
                                                        (status) => (
                                                            <SelectItem
                                                                key={status}
                                                                value={status}
                                                            >
                                                                {status.replace(
                                                                    /\b\w/g,
                                                                    (
                                                                        character,
                                                                    ) =>
                                                                        character.toUpperCase(),
                                                                )}
                                                            </SelectItem>
                                                        ),
                                                    )}
                                                </SelectGroup>
                                            </SelectContent>
                                        </Select>
                                        <FieldError
                                            message={statusForm.errors.status}
                                        />
                                    </div>
                                    <div className="space-y-2">
                                        <Label htmlFor="note">Note</Label>
                                        <Input
                                            id="note"
                                            value={statusForm.data.note}
                                            onChange={(event) =>
                                                statusForm.setData(
                                                    'note',
                                                    event.target.value,
                                                )
                                            }
                                            placeholder="Optional operational note"
                                        />
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
                                            : 'Update shipment status'}
                                    </Button>
                                </form>
                            </CardContent>
                        </Card>

                        <Card className="overflow-hidden border-border/70 pt-0">
                            <CardHeader className="border-b border-border/70 bg-muted/30 py-6">
                                <CardTitle>Assignment and tracking</CardTitle>
                            </CardHeader>
                            <CardContent className="p-6">
                                <form
                                    className="flex flex-col gap-4"
                                    onSubmit={(event) => {
                                        event.preventDefault();
                                        submitShipmentUpdate();
                                    }}
                                >
                                    <div className="grid gap-4 md:grid-cols-2">
                                        <div className="flex flex-col gap-2">
                                            <Label htmlFor="warehouse_location_id">
                                                Warehouse
                                            </Label>
                                            <Select
                                                value={
                                                    updateForm.data
                                                        .warehouse_location_id ||
                                                    EMPTY_SENTINEL
                                                }
                                                onValueChange={(value) =>
                                                    updateForm.setData(
                                                        'warehouse_location_id',
                                                        value === EMPTY_SENTINEL
                                                            ? ''
                                                            : value,
                                                    )
                                                }
                                            >
                                                <SelectTrigger id="warehouse_location_id">
                                                    <SelectValue placeholder="Select warehouse" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectGroup>
                                                        <SelectItem
                                                            value={
                                                                EMPTY_SENTINEL
                                                            }
                                                        >
                                                            No warehouse
                                                        </SelectItem>
                                                        {availableWarehouses.map(
                                                            (warehouse) => (
                                                                <SelectItem
                                                                    key={
                                                                        warehouse.id
                                                                    }
                                                                    value={warehouse.id.toString()}
                                                                >
                                                                    {
                                                                        warehouse.name
                                                                    }{' '}
                                                                    (
                                                                    {
                                                                        warehouse.code
                                                                    }
                                                                    )
                                                                    {warehouse.is_default
                                                                        ? ' • Default'
                                                                        : ''}
                                                                </SelectItem>
                                                            ),
                                                        )}
                                                    </SelectGroup>
                                                </SelectContent>
                                            </Select>
                                            <FieldError
                                                message={
                                                    updateForm.errors
                                                        .warehouse_location_id
                                                }
                                            />
                                        </div>
                                        <div className="flex flex-col gap-2">
                                            <Label htmlFor="shipping_method_id">
                                                Shipping method
                                            </Label>
                                            <Select
                                                value={
                                                    updateForm.data
                                                        .shipping_method_id ||
                                                    EMPTY_SENTINEL
                                                }
                                                onValueChange={(value) =>
                                                    updateForm.setData(
                                                        'shipping_method_id',
                                                        value === EMPTY_SENTINEL
                                                            ? ''
                                                            : value,
                                                    )
                                                }
                                            >
                                                <SelectTrigger id="shipping_method_id">
                                                    <SelectValue placeholder="Select shipping method" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectGroup>
                                                        <SelectItem
                                                            value={
                                                                EMPTY_SENTINEL
                                                            }
                                                        >
                                                            No method
                                                        </SelectItem>
                                                        {availableShippingMethods.map(
                                                            (method) => (
                                                                <SelectItem
                                                                    key={
                                                                        method.id
                                                                    }
                                                                    value={method.id.toString()}
                                                                >
                                                                    {
                                                                        method.name
                                                                    }{' '}
                                                                    •{' '}
                                                                    {
                                                                        method.code
                                                                    }
                                                                </SelectItem>
                                                            ),
                                                        )}
                                                    </SelectGroup>
                                                </SelectContent>
                                            </Select>
                                            <FieldError
                                                message={
                                                    updateForm.errors
                                                        .shipping_method_id
                                                }
                                            />
                                        </div>
                                    </div>
                                    <div className="grid gap-4 md:grid-cols-2">
                                        <div className="flex flex-col gap-2">
                                            <Label htmlFor="carrier_name">
                                                Carrier
                                            </Label>
                                            <Input
                                                id="carrier_name"
                                                value={
                                                    updateForm.data.carrier_name
                                                }
                                                onChange={(event) =>
                                                    updateForm.setData(
                                                        'carrier_name',
                                                        event.target.value,
                                                    )
                                                }
                                            />
                                            <FieldError
                                                message={
                                                    updateForm.errors
                                                        .carrier_name
                                                }
                                            />
                                        </div>
                                        <div className="flex flex-col gap-2">
                                            <Label htmlFor="tracking_number">
                                                Tracking number
                                            </Label>
                                            <Input
                                                id="tracking_number"
                                                value={
                                                    updateForm.data
                                                        .tracking_number
                                                }
                                                onChange={(event) =>
                                                    updateForm.setData(
                                                        'tracking_number',
                                                        event.target.value,
                                                    )
                                                }
                                            />
                                            <FieldError
                                                message={
                                                    updateForm.errors
                                                        .tracking_number
                                                }
                                            />
                                        </div>
                                    </div>
                                    <div className="flex flex-col gap-2">
                                        <Label htmlFor="tracking_url">
                                            Tracking URL
                                        </Label>
                                        <Input
                                            id="tracking_url"
                                            type="url"
                                            value={updateForm.data.tracking_url}
                                            onChange={(event) =>
                                                updateForm.setData(
                                                    'tracking_url',
                                                    event.target.value,
                                                )
                                            }
                                        />
                                        <FieldError
                                            message={
                                                updateForm.errors.tracking_url
                                            }
                                        />
                                    </div>
                                    <div className="grid gap-4 md:grid-cols-2">
                                        <div className="flex flex-col gap-2">
                                            <Label htmlFor="rider_name">
                                                Rider
                                            </Label>
                                            <Input
                                                id="rider_name"
                                                value={
                                                    updateForm.data.rider_name
                                                }
                                                onChange={(event) =>
                                                    updateForm.setData(
                                                        'rider_name',
                                                        event.target.value,
                                                    )
                                                }
                                            />
                                            <FieldError
                                                message={
                                                    updateForm.errors.rider_name
                                                }
                                            />
                                        </div>
                                        <div className="flex flex-col gap-2">
                                            <Label htmlFor="rider_phone">
                                                Rider phone
                                            </Label>
                                            <Input
                                                id="rider_phone"
                                                value={
                                                    updateForm.data.rider_phone
                                                }
                                                onChange={(event) =>
                                                    updateForm.setData(
                                                        'rider_phone',
                                                        event.target.value,
                                                    )
                                                }
                                            />
                                            <FieldError
                                                message={
                                                    updateForm.errors
                                                        .rider_phone
                                                }
                                            />
                                        </div>
                                    </div>
                                    <div className="flex flex-col gap-2">
                                        <Label htmlFor="notes">Notes</Label>
                                        <textarea
                                            id="notes"
                                            value={updateForm.data.notes}
                                            onChange={(event) =>
                                                updateForm.setData(
                                                    'notes',
                                                    event.target.value,
                                                )
                                            }
                                            className="min-h-24 rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs ring-offset-background placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                                        />
                                        <FieldError
                                            message={updateForm.errors.notes}
                                        />
                                    </div>
                                    <Button
                                        type="submit"
                                        disabled={updateForm.processing}
                                        className="w-full"
                                    >
                                        {updateForm.processing
                                            ? 'Saving…'
                                            : 'Save shipment details'}
                                    </Button>
                                </form>
                            </CardContent>
                        </Card>
                    </div>

                    <div className="space-y-6">
                        <Card className="overflow-hidden border-border/70 pt-0">
                            <CardHeader className="border-b border-border/70 bg-muted/30 py-6">
                                <CardTitle>Shipment items</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-3 p-6">
                                {shipment.items.map((item) => (
                                    <div
                                        key={item.id}
                                        className="rounded-2xl border border-border/70 bg-background/80 p-4"
                                    >
                                        <div className="flex items-start justify-between gap-3">
                                            <div>
                                                <p className="font-medium">
                                                    {item.product_name ??
                                                        'Unknown product'}
                                                </p>
                                                <p className="text-sm text-muted-foreground">
                                                    {item.variant_name ??
                                                        'Base product'}
                                                    {item.sku
                                                        ? ` • ${item.sku}`
                                                        : ''}
                                                </p>
                                            </div>
                                            <div className="text-right text-sm">
                                                <p>Qty {item.quantity}</p>
                                                <p className="text-muted-foreground">
                                                    Order item #
                                                    {item.order_item_id}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </CardContent>
                        </Card>
                        {shipment.notes ? (
                            <Card className="overflow-hidden border-border/70 pt-0">
                                <CardHeader className="border-b border-border/70 bg-muted/30 py-6">
                                    <CardTitle>Notes</CardTitle>
                                </CardHeader>
                                <CardContent className="p-6 text-sm whitespace-pre-wrap text-muted-foreground">
                                    {shipment.notes}
                                </CardContent>
                            </Card>
                        ) : null}
                        <Card className="overflow-hidden border-border/70 pt-0">
                            <CardHeader className="border-b border-border/70 bg-muted/30 py-6">
                                <CardTitle>Status history</CardTitle>
                            </CardHeader>
                            <CardContent className="flex flex-col gap-3 p-6 text-sm">
                                {shipment.history.length === 0 ? (
                                    <p className="text-muted-foreground">
                                        No package-stage changes have been
                                        recorded yet.
                                    </p>
                                ) : (
                                    shipment.history.map((entry) => (
                                        <div
                                            key={entry.id}
                                            className="rounded-2xl border border-border/70 bg-background/80 p-4"
                                        >
                                            <div className="flex items-center justify-between gap-3">
                                                <p className="font-medium">
                                                    {entry.from_status
                                                        ? `${entry.from_status} -> ${entry.to_status}`
                                                        : entry.to_status}
                                                </p>
                                                <p className="text-xs text-muted-foreground">
                                                    {formatDate(
                                                        entry.created_at,
                                                    )}
                                                </p>
                                            </div>
                                            <p className="mt-2 text-muted-foreground">
                                                {entry.note ??
                                                    'No note provided.'}
                                            </p>
                                            {entry.changed_by_name ? (
                                                <p className="mt-2 text-xs text-muted-foreground">
                                                    Changed by{' '}
                                                    {entry.changed_by_name}
                                                </p>
                                            ) : null}
                                        </div>
                                    ))
                                )}
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
