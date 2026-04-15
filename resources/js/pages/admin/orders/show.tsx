import { Link, router, useForm } from '@inertiajs/react';
import { useState } from 'react';
import * as OrderController from '@/actions/App/Http/Controllers/Admin/Orders/OrderController';
import * as OrderStatusController from '@/actions/App/Http/Controllers/Admin/Orders/OrderStatusController';
import * as ShipmentController from '@/actions/App/Http/Controllers/Admin/Shipments/ShipmentController';
import { FieldError } from '@/components/shared/forms/field-error';
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

export default function OrderShowPage({ order, allowedStatuses, availableWarehouses, canCreateShipment, shipmentCreationMessage }: Props) {
    const [showCustomShipmentForm, setShowCustomShipmentForm] = useState(false);
    const [quickShipmentProcessing, setQuickShipmentProcessing] = useState(false);
    const defaultWarehouse = availableWarehouses.find((warehouse) => warehouse.is_default) ?? null;
    const statusForm = useForm({ status: '', note: '' });
    const quickShipmentForm = useForm({});
    const shipmentForm = useForm({
        order_id: order.id,
        warehouse_location_id: defaultWarehouse ? defaultWarehouse.id.toString() : '',
        carrier_name: '',
        items: order.items.map((item) => ({ order_item_id: item.id, quantity: item.remaining_quantity > 0 ? item.remaining_quantity.toString() : '0' })),
    });

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
                <PageHeader title={order.order_number} description={`Placed ${formatDate(order.placed_at)} by ${order.email}.`} actions={<Button variant="outline" asChild><Link href={OrderController.index.url()}>Back to orders</Link></Button>} />
                <div className="grid gap-4 lg:grid-cols-[1.25fr_0.75fr]">
                    <Card className="border-border/70 bg-muted/30">
                        <CardHeader className="space-y-3">
                            <div className="flex items-center justify-between gap-3">
                                <p className="text-xs font-semibold uppercase tracking-[0.24em] text-muted-foreground">Order stage</p>
                                <div className="flex flex-wrap gap-2"><StatusBadge status={order.status} /><StatusBadge status={order.payment_status} /><StatusBadge status={order.fulfillment_status} /></div>
                            </div>
                            <CardTitle className="text-2xl">{order.email}</CardTitle>
                            <p className="text-sm leading-6 text-muted-foreground">Order status is for review and approval. Shipment status is for physical movement after fulfillment starts.</p>
                        </CardHeader>
                        <CardContent className="grid gap-4 border-t border-border/70 pt-6 md:grid-cols-3">
                            <div><p className="text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">Placed</p><p className="mt-2 font-semibold">{formatDate(order.placed_at)}</p></div>
                            <div><p className="text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">Shipments</p><p className="mt-2 font-semibold">{order.shipments.length}</p></div>
                            <div><p className="text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">Remaining qty</p><p className="mt-2 font-semibold">{order.fulfillment_summary.total_remaining_quantity}</p></div>
                        </CardContent>
                    </Card>
                    <Card className="border-border/70 bg-primary/5">
                        <CardHeader><CardTitle className="text-xl">Next actions</CardTitle></CardHeader>
                        <CardContent className="space-y-3 text-sm text-muted-foreground">
                            <p>1. Advance the order stage when review is done.</p>
                            <p>2. Create a shipment only after the order reaches processing.</p>
                            <p>3. Use shipment statuses to track packing, dispatch, and delivery.</p>
                        </CardContent>
                    </Card>
                </div>

                <div className="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
                    <div className="space-y-6">
                        <Card className="overflow-hidden border-border/70 pt-0">
                            <CardHeader className="border-b border-border/70 bg-muted/30 py-6"><CardTitle>Order controls</CardTitle></CardHeader>
                            <CardContent className="space-y-4 p-6">
                                <div className="rounded-2xl border border-border/70 bg-muted/20 p-4 text-sm text-muted-foreground">
                                    Use these buttons for order-stage changes only. They do not create deliveries.
                                </div>
                                {allowedStatuses.length > 0 ? (
                                    <div className="flex flex-wrap gap-2">
                                        {allowedStatuses.map((status) => (
                                            <Button key={status} type="button" variant={allowedStatuses.length === 1 ? 'default' : 'outline'} disabled={statusForm.processing} onClick={() => runStatusUpdate(status)}>
                                                {status.replace(/\b\w/g, (character) => character.toUpperCase())}
                                            </Button>
                                        ))}
                                    </div>
                                ) : <p className="text-sm text-muted-foreground">No further status transitions are allowed for this order.</p>}
                                <form className="space-y-4 border-t border-border/70 pt-4" onSubmit={(event) => {
 event.preventDefault(); statusForm.patch(OrderStatusController.update.url(order), { preserveScroll: true }); 
}}>
                                    <div className="space-y-2">
                                        <Label htmlFor="status">Advanced status update</Label>
                                        <Select value={statusForm.data.status} onValueChange={(value) => statusForm.setData('status', value)}>
                                            <SelectTrigger id="status"><SelectValue placeholder="Select a status" /></SelectTrigger>
                                            <SelectContent>{allowedStatuses.map((status) => <SelectItem key={status} value={status}>{status.replace(/\b\w/g, (character) => character.toUpperCase())}</SelectItem>)}</SelectContent>
                                        </Select>
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
                            <CardHeader className="border-b border-border/70 bg-muted/30 py-6"><CardTitle>Order items</CardTitle></CardHeader>
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
                                    </div>
                                ))}
                            </CardContent>
                        </Card>
                    </div>

                    <div className="space-y-6">
                        <Card className="overflow-hidden border-border/70 pt-0">
                            <CardHeader className="border-b border-border/70 bg-muted/30 py-6">
                                <div className="flex items-center justify-between gap-3">
                                    <CardTitle>Shipment controls</CardTitle>
                                    {canCreateShipment ? <Button type="button" onClick={createFullShipment} disabled={isShipmentBusy || order.fulfillment_summary.total_remaining_quantity === 0}>{quickShipmentProcessing ? 'Creating…' : 'Create full shipment'}</Button> : null}
                                </div>
                            </CardHeader>
                            <CardContent className="space-y-4 p-6">
                                <div className="rounded-2xl border border-border/70 bg-muted/20 p-4 text-sm text-muted-foreground">
                                    This area is only for fulfillment. The quick button creates one shipment using all remaining quantities and the default warehouse if one exists.
                                </div>
                                <FieldError message={quickShipmentForm.errors.order_id} />
                                <FieldError message={quickShipmentForm.errors.items} />
                                <FieldError message={shipmentForm.errors.order_id} />
                                <FieldError message={shipmentForm.errors.items} />
                                <div className="grid gap-3 rounded-2xl border border-border/70 bg-muted/30 p-4 text-sm md:grid-cols-4">
                                    <div><p className="text-xs font-semibold uppercase tracking-[0.18em] text-muted-foreground">Ordered</p><p className="mt-1 font-medium">{order.fulfillment_summary.total_ordered_quantity}</p></div>
                                    <div><p className="text-xs font-semibold uppercase tracking-[0.18em] text-muted-foreground">Allocated</p><p className="mt-1 font-medium">{order.fulfillment_summary.total_allocated_quantity}</p></div>
                                    <div><p className="text-xs font-semibold uppercase tracking-[0.18em] text-muted-foreground">Delivered</p><p className="mt-1 font-medium">{order.fulfillment_summary.total_delivered_quantity}</p></div>
                                    <div><p className="text-xs font-semibold uppercase tracking-[0.18em] text-muted-foreground">Remaining</p><p className="mt-1 font-medium">{order.fulfillment_summary.total_remaining_quantity}</p></div>
                                </div>
                                {canCreateShipment ? (
                                    <>
                                        <div className="flex flex-wrap gap-2">
                                            <Button type="button" variant="outline" onClick={() => setShowCustomShipmentForm((current) => !current)}>
                                                {showCustomShipmentForm || hasShipmentErrors || hasQuickShipmentErrors ? 'Hide custom shipment' : 'Modify shipment'}
                                            </Button>
                                        </div>

                                        {showCustomShipmentForm || hasShipmentErrors ? (
                                            <form className="space-y-4 rounded-2xl border border-border/70 bg-background/80 p-4" onSubmit={(event) => {
                                                event.preventDefault();
                                                submitShipment(shipmentForm.data.items.filter((item) => Number(item.quantity) > 0).map((item) => ({ order_item_id: item.order_item_id, quantity: Number(item.quantity) })));
                                            }}>
                                                <div>
                                                    <p className="text-sm font-medium">Custom shipment</p>
                                                    <p className="text-sm text-muted-foreground">Adjust the defaults only if this shipment should not use the full remaining quantities.</p>
                                                </div>
                                                <div className="grid gap-4 md:grid-cols-2">
                                                    <div className="space-y-2">
                                                        <Label htmlFor="warehouse_location_id">Warehouse</Label>
                                                        <Select value={shipmentForm.data.warehouse_location_id || EMPTY_SENTINEL} onValueChange={(value) => shipmentForm.setData('warehouse_location_id', value === EMPTY_SENTINEL ? '' : value)}>
                                                            <SelectTrigger id="warehouse_location_id"><SelectValue placeholder="Select a warehouse" /></SelectTrigger>
                                                            <SelectContent>
                                                                <SelectItem value={EMPTY_SENTINEL}>No warehouse yet</SelectItem>
                                                                {availableWarehouses.map((warehouse) => <SelectItem key={warehouse.id} value={warehouse.id.toString()}>{warehouse.name} ({warehouse.code}){warehouse.is_default ? ' • Default' : ''}</SelectItem>)}
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
                                    </>
                                ) : <div className="rounded-2xl border border-dashed border-border/70 bg-muted/20 p-4 text-sm text-muted-foreground">{shipmentCreationMessage}</div>}

                                {order.shipments.length === 0 ? <p className="text-sm text-muted-foreground">No shipments have been created for this order yet.</p> : order.shipments.map((shipment) => (
                                    <div key={shipment.id} className="rounded-2xl border border-border/70 bg-background/80 p-4 text-sm">
                                        <div className="mb-2 flex items-center justify-between">
                                            <Link href={ShipmentController.show.url(shipment.id)} className="font-medium transition hover:text-primary">Shipment #{shipment.id}</Link>
                                            <StatusBadge status={shipment.status} />
                                        </div>
                                        <div className="space-y-1 text-muted-foreground">
                                            <p>Tracking: {shipment.tracking_number ?? 'N/A'}</p>
                                            <p>Carrier: {shipment.carrier_name ?? 'N/A'}</p>
                                            <p>Warehouse: {shipment.warehouse_location ? `${shipment.warehouse_location.name} (${shipment.warehouse_location.code})` : 'N/A'}</p>
                                        </div>
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
