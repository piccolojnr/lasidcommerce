import { Link, router, useForm } from '@inertiajs/react';
import { useState } from 'react';
import * as OrderController from '@/actions/App/Http/Controllers/Admin/Orders/OrderController';
import * as OrderStatusController from '@/actions/App/Http/Controllers/Admin/Orders/OrderStatusController';
import * as ShipmentController from '@/actions/App/Http/Controllers/Admin/Shipments/ShipmentController';
import { FieldError } from '@/components/shared/forms/field-error';
import { FulfillmentGuideDialog } from '@/components/shared/guides/fulfillment-guide-dialog';
import { PageHeader } from '@/components/shared/page-header/page-header';
import { StatusBadge } from '@/components/shared/status-badge/status-badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { EMPTY_SENTINEL, Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { AdminLayout } from '@/layouts/app/admin-layout';
import { formatDate } from '@/lib/formatters/date';
import type { AdminOrderDetail, AdminOrderWarehouseOption } from '@/types/admin/order';

interface Props {
    order: AdminOrderDetail;
    allowedStatuses: string[];
    availableWarehouses: AdminOrderWarehouseOption[];
    canCreateShipment: boolean;
    shipmentCreationMessage: string | null;
}

export default function OrderShowPage({
    order,
    allowedStatuses,
    availableWarehouses,
    canCreateShipment,
    shipmentCreationMessage,
}: Props) {
    const [showCustomShipmentForm, setShowCustomShipmentForm] = useState(false);
    const [quickShipmentProcessing, setQuickShipmentProcessing] = useState(false);
    const defaultWarehouse = availableWarehouses.find((warehouse) => warehouse.is_default) ?? null;
    const statusForm = useForm({ status: '', note: '' });
    const quickShipmentForm = useForm<{ order_id?: string; items?: string }>({});
    const shipmentForm = useForm({
        order_id: order.id,
        warehouse_location_id: defaultWarehouse ? defaultWarehouse.id.toString() : '',
        carrier_name: '',
        items: order.items.map((item) => ({
            order_item_id: item.id,
            quantity: item.remaining_quantity > 0 ? item.remaining_quantity.toString() : '0',
        })),
    });

    const quickShipmentLabel = order.fulfillment_summary.needs_reshipment ? 'Reship remaining items' : 'Create full shipment';

    const runStatusUpdate = (status: string) => {
        statusForm.transform((data) => ({ ...data, status }));
        statusForm.patch(OrderStatusController.update.url(order), { preserveScroll: true });
    };

    const submitShipment = (items: Array<{ order_item_id: number; quantity: number }>) => {
        shipmentForm.transform((data) => ({
            ...data,
            warehouse_location_id: data.warehouse_location_id === '' ? null : Number(data.warehouse_location_id),
            items,
        }));
        shipmentForm.post(ShipmentController.store.url(), { preserveScroll: true });
    };

    const createFullShipment = () => {
        quickShipmentForm.clearErrors();
        router.post(`/admin/orders/${order.id}/shipments/quick`, {}, {
            preserveScroll: true,
            onStart: () => setQuickShipmentProcessing(true),
            onFinish: () => setQuickShipmentProcessing(false),
            onError: (errors) => quickShipmentForm.setError(errors),
        });
    };

    const setShipmentQuantity = (orderItemId: number, quantity: string) => {
        shipmentForm.setData('items', shipmentForm.data.items.map((item) => (item.order_item_id === orderItemId ? { ...item, quantity } : item)));
    };

    const hasShipmentErrors = Object.keys(shipmentForm.errors).length > 0;
    const hasQuickShipmentErrors = Object.keys(quickShipmentForm.errors).length > 0;
    const isShipmentBusy = shipmentForm.processing || quickShipmentProcessing;

    return (
        <AdminLayout title="Order Details" description="Inspect order, payment, and fulfillment state.">
            <div className="mx-auto w-full max-w-7xl space-y-8">
                <PageHeader
                    title={order.order_number}
                    description={`Placed ${formatDate(order.placed_at)} by ${order.email}.`}
                    actions={
                        <div className="flex items-center gap-2">
                            <FulfillmentGuideDialog />
                            <Button variant="outline" asChild>
                                <Link href={OrderController.index.url()}>Back to orders</Link>
                            </Button>
                        </div>
                    }
                />
                <div className="grid gap-4 lg:grid-cols-[1.25fr_0.75fr]">
                    <Card className="border-border/70 bg-muted/30">
                        <CardHeader className="space-y-3">
                            <div className="flex items-center justify-between gap-3">
                                <p className="text-xs font-semibold uppercase tracking-[0.24em] text-muted-foreground">Order overview</p>
                                <div className="flex flex-wrap gap-2">
                                    <StatusBadge status={order.status} />
                                    <StatusBadge status={order.payment_status} />
                                    <StatusBadge status={order.fulfillment_status} />
                                    <StatusBadge status={order.shipping_summary} />
                                </div>
                            </div>
                            <CardTitle className="text-2xl">{order.email}</CardTitle>
                            <p className="text-sm leading-6 text-muted-foreground">
                                Order status is the business lifecycle. Shipment status is the physical delivery attempt.
                                Fulfillment tells you whether quantities are actually covered.
                            </p>
                        </CardHeader>
                        <CardContent className="grid gap-4 border-t border-border/70 pt-6 md:grid-cols-4">
                            <div><p className="text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">Placed</p><p className="mt-2 font-semibold">{formatDate(order.placed_at)}</p></div>
                            <div><p className="text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">Shipment summary</p><p className="mt-2 font-semibold">{order.shipping_summary.replace(/_/g, ' ')}</p></div>
                            <div><p className="text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">Shipments</p><p className="mt-2 font-semibold">{order.shipments.length}</p></div>
                            <div><p className="text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">Remaining qty</p><p className="mt-2 font-semibold">{order.fulfillment_summary.total_remaining_quantity}</p></div>
                        </CardContent>
                    </Card>
                    <Card className="border-border/70 bg-primary/5">
                        <CardHeader><CardTitle className="text-xl">Working rules</CardTitle></CardHeader>
                        <CardContent className="space-y-3 text-sm text-muted-foreground">
                            <p>1. Move the order into processing before creating the first shipment.</p>
                            <p>2. Create or reship from this page. Update package movement from the shipment page.</p>
                            <p>3. Mark the order completed only when the business considers the case closed.</p>
                        </CardContent>
                    </Card>
                </div>

                <div className="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
                    <div className="space-y-6">
                        <Card className="overflow-hidden border-border/70 pt-0">
                            <CardHeader className="border-b border-border/70 bg-muted/30 py-6">
                                <CardTitle>Business stage controls</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4 p-6">
                                <div className="rounded-2xl border border-border/70 bg-muted/20 p-4 text-sm text-muted-foreground">
                                    These controls are only for business handling: review, processing, cancellation, and final completion.
                                </div>
                                {allowedStatuses.length > 0 ? (
                                    <div className="flex flex-wrap gap-2">
                                        {allowedStatuses.map((status) => (
                                            <Button key={status} type="button" variant={status === 'completed' ? 'default' : 'outline'} disabled={statusForm.processing} onClick={() => runStatusUpdate(status)}>
                                                {status.replace(/\b\w/g, (character) => character.toUpperCase())}
                                            </Button>
                                        ))}
                                    </div>
                                ) : <p className="text-sm text-muted-foreground">No manual business-stage transitions are currently allowed for this order.</p>}
                                <form className="space-y-4 border-t border-border/70 pt-4" onSubmit={(event) => {
                                    event.preventDefault();
                                    statusForm.patch(OrderStatusController.update.url(order), { preserveScroll: true });
                                }}>
                                    <div className="space-y-2">
                                        <Label htmlFor="status">Advanced status update</Label>
                                        <Select value={statusForm.data.status} onValueChange={(value) => statusForm.setData('status', value)}>
                                            <SelectTrigger id="status"><SelectValue placeholder="Select a business status" /></SelectTrigger>
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
                                        <Input id="note" value={statusForm.data.note} onChange={(event) => statusForm.setData('note', event.target.value)} placeholder="Optional audit note" />
                                    </div>
                                    <Button type="submit" disabled={statusForm.processing || !statusForm.data.status} className="w-full">{statusForm.processing ? 'Updating…' : 'Update order status'}</Button>
                                </form>
                            </CardContent>
                        </Card>

                        <Card className="overflow-hidden border-border/70 pt-0">
                            <CardHeader className="border-b border-border/70 bg-muted/30 py-6">
                                <CardTitle>Order items</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-3 p-6">
                                {order.items.map((item) => (
                                    <div key={item.id} className="rounded-2xl border border-border/70 bg-background/80 p-4">
                                        <div className="flex items-start justify-between gap-3">
                                            <div>
                                                <p className="font-medium">{item.product_name}</p>
                                                <p className="text-sm text-muted-foreground">{item.variant_name ?? 'Base product'}{item.sku ? ` • ${item.sku}` : ''}</p>
                                            </div>
                                            <div className="text-right text-sm">
                                                <p>Ordered {item.quantity}</p>
                                                <p className="text-muted-foreground">Remaining {item.remaining_quantity}</p>
                                            </div>
                                        </div>
                                        <div className="mt-3 grid gap-3 text-xs text-muted-foreground md:grid-cols-3">
                                            <div>Active allocation: {item.allocated_quantity}</div>
                                            <div>In transit: {item.in_progress_quantity}</div>
                                            <div>Delivered: {item.delivered_quantity}</div>
                                        </div>
                                    </div>
                                ))}
                            </CardContent>
                        </Card>
                    </div>

                    <div className="space-y-6">
                        <Card className="overflow-hidden border-border/70 pt-0">
                            <CardHeader className="border-b border-border/70 bg-muted/30 py-6">
                                <div className="flex items-center justify-between gap-3">
                                    <CardTitle>Fulfillment workspace</CardTitle>
                                    {canCreateShipment ? <Button type="button" onClick={createFullShipment} disabled={isShipmentBusy || order.fulfillment_summary.total_remaining_quantity === 0}>{quickShipmentProcessing ? 'Creating…' : quickShipmentLabel}</Button> : null}
                                </div>
                            </CardHeader>
                            <CardContent className="space-y-4 p-6">
                                <div className="rounded-2xl border border-border/70 bg-muted/20 p-4 text-sm text-muted-foreground">
                                    Use this panel to create the first shipment or a later reshipment. Returned and failed shipments stay in history; they do not get reused.
                                </div>
                                <FieldError message={quickShipmentForm.errors.order_id} />
                                <FieldError message={quickShipmentForm.errors.items} />
                                <FieldError message={shipmentForm.errors.order_id} />
                                <FieldError message={shipmentForm.errors.items} />
                                <div className="grid gap-3 rounded-2xl border border-border/70 bg-muted/30 p-4 text-sm md:grid-cols-5">
                                    <div><p className="text-xs font-semibold uppercase tracking-[0.18em] text-muted-foreground">Ordered</p><p className="mt-1 font-medium">{order.fulfillment_summary.total_ordered_quantity}</p></div>
                                    <div><p className="text-xs font-semibold uppercase tracking-[0.18em] text-muted-foreground">Active allocated</p><p className="mt-1 font-medium">{order.fulfillment_summary.total_allocated_quantity}</p></div>
                                    <div><p className="text-xs font-semibold uppercase tracking-[0.18em] text-muted-foreground">In transit</p><p className="mt-1 font-medium">{order.fulfillment_summary.total_in_progress_quantity}</p></div>
                                    <div><p className="text-xs font-semibold uppercase tracking-[0.18em] text-muted-foreground">Delivered</p><p className="mt-1 font-medium">{order.fulfillment_summary.total_delivered_quantity}</p></div>
                                    <div><p className="text-xs font-semibold uppercase tracking-[0.18em] text-muted-foreground">Remaining</p><p className="mt-1 font-medium">{order.fulfillment_summary.total_remaining_quantity}</p></div>
                                </div>
                                {canCreateShipment ? (
                                    <div className="flex flex-wrap gap-2">
                                        <Button type="button" variant="outline" onClick={() => setShowCustomShipmentForm((current) => !current)}>
                                            {showCustomShipmentForm || hasShipmentErrors || hasQuickShipmentErrors ? 'Hide custom shipment' : 'Customize shipment'}
                                        </Button>
                                    </div>
                                ) : (
                                    <div className="rounded-2xl border border-dashed border-border/70 bg-muted/20 p-4 text-sm text-muted-foreground">{shipmentCreationMessage}</div>
                                )}

                                {showCustomShipmentForm || hasShipmentErrors ? (
                                    <form className="space-y-4 rounded-2xl border border-border/70 bg-background/80 p-4" onSubmit={(event) => {
                                        event.preventDefault();
                                        submitShipment(shipmentForm.data.items.filter((item) => Number(item.quantity) > 0).map((item) => ({
                                            order_item_id: item.order_item_id,
                                            quantity: Number(item.quantity),
                                        })));
                                    }}>
                                        <div>
                                            <p className="text-sm font-medium">Custom shipment</p>
                                            <p className="text-sm text-muted-foreground">Adjust quantities only when the remaining items should be split across more than one shipment attempt.</p>
                                        </div>
                                        <div className="grid gap-4 md:grid-cols-2">
                                            <div className="space-y-2">
                                                <Label htmlFor="warehouse_location_id">Warehouse</Label>
                                                <Select value={shipmentForm.data.warehouse_location_id || EMPTY_SENTINEL} onValueChange={(value) => shipmentForm.setData('warehouse_location_id', value === EMPTY_SENTINEL ? '' : value)}>
                                                    <SelectTrigger id="warehouse_location_id"><SelectValue placeholder="Select a warehouse" /></SelectTrigger>
                                                    <SelectContent>
                                                        <SelectItem value={EMPTY_SENTINEL}>No warehouse yet</SelectItem>
                                                        {availableWarehouses.map((warehouse) => (
                                                            <SelectItem key={warehouse.id} value={warehouse.id.toString()}>
                                                                {warehouse.name} ({warehouse.code}){warehouse.is_default ? ' • Default' : ''}
                                                            </SelectItem>
                                                        ))}
                                                    </SelectContent>
                                                </Select>
                                                <FieldError message={shipmentForm.errors.warehouse_location_id} />
                                            </div>
                                            <div className="space-y-2">
                                                <Label htmlFor="carrier_name">Carrier</Label>
                                                <Input id="carrier_name" value={shipmentForm.data.carrier_name} onChange={(event) => shipmentForm.setData('carrier_name', event.target.value)} placeholder="Optional carrier name" />
                                                <FieldError message={shipmentForm.errors.carrier_name} />
                                            </div>
                                        </div>
                                        <div className="space-y-3">
                                            {order.items.map((item, index) => (
                                                <div key={item.id} className="rounded-2xl border border-border/60 bg-muted/25 p-4">
                                                    <div className="flex items-center justify-between gap-3">
                                                        <div>
                                                            <p className="font-medium">{item.product_name}</p>
                                                            <p className="text-sm text-muted-foreground">{item.variant_name ?? 'Base product'}{item.sku ? ` • ${item.sku}` : ''}</p>
                                                        </div>
                                                        <p className="text-sm text-muted-foreground">Remaining <span className="font-medium text-foreground">{item.remaining_quantity}</span></p>
                                                    </div>
                                                    <div className="mt-3 space-y-2">
                                                        <Label htmlFor={`shipment-quantity-${item.id}`}>Shipment quantity</Label>
                                                        <Input
                                                            id={`shipment-quantity-${item.id}`}
                                                            type="number"
                                                            min="0"
                                                            max={item.remaining_quantity}
                                                            value={shipmentForm.data.items[index]?.quantity ?? ''}
                                                            onChange={(event) => setShipmentQuantity(item.id, event.target.value)}
                                                            placeholder={item.remaining_quantity > 0 ? `0-${item.remaining_quantity}` : 'Unavailable'}
                                                            disabled={item.remaining_quantity === 0}
                                                        />
                                                        <FieldError message={shipmentForm.errors[`items.${index}.quantity`]} />
                                                    </div>
                                                </div>
                                            ))}
                                        </div>
                                        <Button type="submit" disabled={isShipmentBusy} className="w-full">{shipmentForm.processing ? 'Creating shipment…' : 'Create custom shipment'}</Button>
                                    </form>
                                ) : null}

                                <div className="space-y-3 border-t border-border/70 pt-4">
                                    {order.shipments.length === 0 ? <p className="text-sm text-muted-foreground">No shipment attempts have been created for this order yet.</p> : order.shipments.map((shipment) => (
                                        <div key={shipment.id} className="rounded-2xl border border-border/70 bg-background/80 p-4 text-sm">
                                            <div className="mb-3 flex items-center justify-between gap-3">
                                                <Link href={ShipmentController.show.url(shipment.id)} className="font-medium transition hover:text-primary">
                                                    Shipment #{shipment.id}
                                                </Link>
                                                <div className="flex flex-wrap gap-2">
                                                    <StatusBadge status={shipment.status} />
                                                    {shipment.status === 'returned' || shipment.status === 'failed' ? <StatusBadge status="attention_required" /> : null}
                                                </div>
                                            </div>
                                            <div className="space-y-1 text-muted-foreground">
                                                <p>Tracking: {shipment.tracking_number ?? 'N/A'}</p>
                                                <p>Carrier: {shipment.carrier_name ?? 'N/A'}</p>
                                                <p>Warehouse: {shipment.warehouse_location ? `${shipment.warehouse_location.name} (${shipment.warehouse_location.code})` : 'N/A'}</p>
                                                <p>Items: {shipment.items.map((item) => `${item.product_name ?? 'Unknown'} × ${item.quantity}`).join(', ')}</p>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </CardContent>
                        </Card>

                        <Card className="overflow-hidden border-border/70 pt-0">
                            <CardHeader className="border-b border-border/70 bg-muted/30 py-6">
                                <CardTitle>Status history</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-3 p-6 text-sm">
                                {order.history.length === 0 ? <p className="text-muted-foreground">No business-stage changes have been recorded yet.</p> : order.history.map((entry) => (
                                    <div key={entry.id} className="rounded-2xl border border-border/70 bg-background/80 p-4">
                                        <div className="flex items-center justify-between gap-3">
                                            <p className="font-medium">{entry.from_status ? `${entry.from_status} -> ${entry.to_status}` : entry.to_status}</p>
                                            <p className="text-xs text-muted-foreground">{formatDate(entry.created_at)}</p>
                                        </div>
                                        <p className="mt-2 text-muted-foreground">{entry.note ?? 'No note provided.'}</p>
                                        {entry.changed_by_name ? <p className="mt-2 text-xs text-muted-foreground">Changed by {entry.changed_by_name}</p> : null}
                                    </div>
                                ))}
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
