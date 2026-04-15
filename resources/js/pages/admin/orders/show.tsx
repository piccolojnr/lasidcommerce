import { Link, useForm } from '@inertiajs/react';
import * as OrderController from '@/actions/App/Http/Controllers/Admin/Orders/OrderController';
import * as OrderStatusController from '@/actions/App/Http/Controllers/Admin/Orders/OrderStatusController';
import * as ShipmentController from '@/actions/App/Http/Controllers/Admin/Shipments/ShipmentController';
import { EmptyState } from '@/components/shared/empty-state/empty-state';
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
import { formatMoney } from '@/lib/formatters/money';
import type { AdminOrderDetail, AdminOrderWarehouseOption } from '@/types/admin/order';

interface Props {
    order: AdminOrderDetail;
    allowedStatuses: string[];
    availableWarehouses: AdminOrderWarehouseOption[];
    canCreateShipment: boolean;
    shipmentCreationMessage: string | null;
}

export default function OrderShowPage({ order, allowedStatuses, availableWarehouses, canCreateShipment, shipmentCreationMessage }: Props) {
    const statusForm = useForm({ status: '', note: '' });
    const shipmentForm = useForm({
        order_id: order.id,
        warehouse_location_id: '',
        carrier_name: '',
        tracking_number: '',
        tracking_url: '',
        rider_name: '',
        rider_phone: '',
        notes: '',
        items: order.items.map((item) => ({ order_item_id: item.id, quantity: '' })),
    });

    const setShipmentQuantity = (orderItemId: number, quantity: string) => {
        shipmentForm.setData('items', shipmentForm.data.items.map((item) => (item.order_item_id === orderItemId ? { ...item, quantity } : item)));
    };

    return (
        <AdminLayout title="Order Details" description="Inspect order, payment, and fulfillment state.">
            <div className="mx-auto w-full max-w-7xl space-y-8">
                <PageHeader title={order.order_number} description={`Placed ${formatDate(order.placed_at)} by ${order.email}.`} actions={<Button variant="outline" asChild><Link href={OrderController.index.url()}>Back to orders</Link></Button>} />

                <div className="grid gap-4 lg:grid-cols-3">
                    <Card className="border-border/70 bg-muted/30 lg:col-span-2">
                        <CardHeader className="space-y-3">
                            <div className="flex items-center justify-between gap-3">
                                <p className="text-xs font-semibold uppercase tracking-[0.24em] text-muted-foreground">Order profile</p>
                                <div className="flex flex-wrap gap-2">
                                    <StatusBadge status={order.status} />
                                    <StatusBadge status={order.payment_status} />
                                    <StatusBadge status={order.fulfillment_status} />
                                </div>
                            </div>
                            <CardTitle className="text-2xl">{formatMoney(order.total_amount, order.currency_code)}</CardTitle>
                            <p className="max-w-2xl text-sm leading-6 text-muted-foreground">Shipping via {order.shipping_method_name ?? 'no method selected'} in {order.shipping_zone_name ?? 'no shipping zone'}.</p>
                        </CardHeader>
                        <CardContent className="grid gap-4 border-t border-border/70 pt-6 md:grid-cols-3">
                            <div><p className="text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">Customer</p><p className="mt-2 font-semibold">{order.email}</p></div>
                            <div><p className="text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">Placed</p><p className="mt-2 font-semibold">{formatDate(order.placed_at)}</p></div>
                            <div><p className="text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">Shipment count</p><p className="mt-2 font-semibold">{order.shipments.length}</p></div>
                        </CardContent>
                    </Card>

                    <Card className="border-border/70 bg-primary/5">
                        <CardHeader className="space-y-2">
                            <p className="text-xs font-semibold uppercase tracking-[0.24em] text-primary">Commercial stack</p>
                            <CardTitle className="text-xl">Totals and adjustments</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-3 text-sm">
                            <div className="flex items-center justify-between"><span className="text-muted-foreground">Subtotal</span><span className="font-medium">{formatMoney(order.subtotal_amount, order.currency_code)}</span></div>
                            <div className="flex items-center justify-between"><span className="text-muted-foreground">Discount</span><span className="font-medium">{formatMoney(order.discount_amount, order.currency_code)}</span></div>
                            <div className="flex items-center justify-between"><span className="text-muted-foreground">Tax</span><span className="font-medium">{formatMoney(order.tax_amount, order.currency_code)}</span></div>
                            <div className="flex items-center justify-between"><span className="text-muted-foreground">Shipping</span><span className="font-medium">{formatMoney(order.shipping_amount, order.currency_code)}</span></div>
                        </CardContent>
                    </Card>
                </div>

                <div className="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
                    <div className="space-y-6">
                        <Card className="overflow-hidden border-border/70 pt-0">
                            <CardHeader className="border-b border-border/70 bg-muted/30 py-6"><CardTitle>Line items</CardTitle></CardHeader>
                            <CardContent className="space-y-3 p-6">
                                {order.items.length === 0 ? <EmptyState title="No order items" description="This order has no line items attached, which would be a problem." /> : order.items.map((item) => (
                                    <div key={item.id} className="rounded-2xl border border-border/70 bg-background/80 p-4">
                                        <div className="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                                            <div>
                                                <p className="font-medium">{item.product_name}</p>
                                                <p className="text-sm text-muted-foreground">{item.variant_name ?? 'Base product'}{item.sku ? ` • ${item.sku}` : ''}</p>
                                            </div>
                                            <div className="text-right text-sm"><p>{item.quantity} × {formatMoney(item.unit_price, order.currency_code)}</p><p className="font-medium">{formatMoney(item.line_total, order.currency_code)}</p></div>
                                        </div>
                                        <div className="mt-4 grid gap-3 rounded-2xl border border-border/60 bg-muted/30 p-4 text-sm md:grid-cols-4">
                                            <div><p className="text-xs font-semibold uppercase tracking-[0.18em] text-muted-foreground">Ordered</p><p className="mt-1 font-medium">{item.quantity}</p></div>
                                            <div><p className="text-xs font-semibold uppercase tracking-[0.18em] text-muted-foreground">Allocated</p><p className="mt-1 font-medium">{item.allocated_quantity}</p></div>
                                            <div><p className="text-xs font-semibold uppercase tracking-[0.18em] text-muted-foreground">Delivered</p><p className="mt-1 font-medium">{item.delivered_quantity}</p></div>
                                            <div><p className="text-xs font-semibold uppercase tracking-[0.18em] text-muted-foreground">Remaining</p><p className="mt-1 font-medium">{item.remaining_quantity}</p></div>
                                        </div>
                                    </div>
                                ))}
                            </CardContent>
                        </Card>

                        <Card className="overflow-hidden border-border/70 pt-0">
                            <CardHeader className="border-b border-border/70 bg-muted/30 py-6"><CardTitle>Status history</CardTitle></CardHeader>
                            <CardContent className="space-y-3 p-6">
                                {order.history.length === 0 ? <EmptyState title="No history yet" description="No status transitions have been recorded for this order." /> : order.history.map((entry) => (
                                    <div key={entry.id} className="rounded-2xl border border-border/70 bg-background/80 p-4">
                                        <div className="space-y-2">
                                            <div className="flex items-center gap-2">{entry.from_status ? <StatusBadge status={entry.from_status} /> : <span className="text-sm text-muted-foreground">Start</span>}<span className="text-muted-foreground">→</span><StatusBadge status={entry.to_status} /></div>
                                            <p className="text-sm text-muted-foreground">{entry.changed_by_name ?? 'System'} • {formatDate(entry.created_at)}</p>
                                            {entry.note ? <p className="text-sm">{entry.note}</p> : null}
                                        </div>
                                    </div>
                                ))}
                            </CardContent>
                        </Card>
                    </div>

                    <div className="space-y-6">
                        <Card className="overflow-hidden border-border/70 pt-0">
                            <CardHeader className="border-b border-border/70 bg-muted/30 py-6"><CardTitle>Update status</CardTitle></CardHeader>
                            <CardContent className="p-6">
                                {allowedStatuses.length === 0 ? <p className="text-sm text-muted-foreground">No further status transitions are allowed for this order.</p> : (
                                    <form className="space-y-4" onSubmit={(event) => {
 event.preventDefault(); statusForm.patch(OrderStatusController.update.url(order), { preserveScroll: true }); 
}}>
                                        <div className="space-y-2">
                                            <Label htmlFor="status">Next status</Label>
                                            <Select value={statusForm.data.status} onValueChange={(value) => statusForm.setData('status', value)}>
                                                <SelectTrigger id="status" className="w-full"><SelectValue placeholder="Select a status" /></SelectTrigger>
                                                <SelectContent>{allowedStatuses.map((status) => <SelectItem key={status} value={status}>{status.replace(/\b\w/g, (character) => character.toUpperCase())}</SelectItem>)}</SelectContent>
                                            </Select>
                                            <FieldError message={statusForm.errors.status} />
                                        </div>
                                        <div className="space-y-2">
                                            <Label htmlFor="note">Note</Label>
                                            <Input id="note" name="note" value={statusForm.data.note} onChange={(event) => statusForm.setData('note', event.target.value)} placeholder="Optional audit note" />
                                            <FieldError message={statusForm.errors.note} />
                                        </div>
                                        <Button type="submit" disabled={statusForm.processing || !statusForm.data.status} className="w-full">{statusForm.processing ? 'Updating…' : 'Update status'}</Button>
                                    </form>
                                )}
                            </CardContent>
                        </Card>

                        <Card className="overflow-hidden border-border/70 pt-0">
                            <CardHeader className="border-b border-border/70 bg-muted/30 py-6"><CardTitle>Shipping address</CardTitle></CardHeader>
                            <CardContent className="space-y-2 p-6 text-sm">
                                {order.shipping_address ? (
                                    <>
                                        <p className="font-medium">{order.shipping_address.name}</p>
                                        <p>{order.shipping_address.address_line_1}</p>
                                        {order.shipping_address.address_line_2 ? <p>{order.shipping_address.address_line_2}</p> : null}
                                        <p>{[order.shipping_address.city, order.shipping_address.district, order.shipping_address.region, order.shipping_address.country].filter(Boolean).join(', ')}</p>
                                        {order.shipping_address.phone ? <p className="text-muted-foreground">{order.shipping_address.phone}</p> : null}
                                    </>
                                ) : <p className="text-muted-foreground">No shipping address captured for this order.</p>}
                            </CardContent>
                        </Card>

                        <Card className="overflow-hidden border-border/70 pt-0">
                            <CardHeader className="border-b border-border/70 bg-muted/30 py-6"><CardTitle>Payments</CardTitle></CardHeader>
                            <CardContent className="space-y-3 p-6">
                                {order.payments.length === 0 ? <p className="text-sm text-muted-foreground">No payment attempts recorded.</p> : order.payments.map((payment) => (
                                    <div key={payment.id} className="rounded-2xl border border-border/70 bg-background/80 p-4 text-sm">
                                        <div className="mb-2 flex items-center justify-between"><span className="font-medium">{payment.provider}</span><StatusBadge status={payment.status} /></div>
                                        <div className="space-y-1 text-muted-foreground">
                                            <p>Reference: {payment.reference}</p>
                                            <p>Amount: {formatMoney(payment.amount, payment.currency_code)}</p>
                                            <p>Paid at: {formatDate(payment.paid_at)}</p>
                                        </div>
                                    </div>
                                ))}
                            </CardContent>
                        </Card>

                        <Card className="overflow-hidden border-border/70 pt-0">
                            <CardHeader className="border-b border-border/70 bg-muted/30 py-6"><CardTitle>Fulfillment and shipments</CardTitle></CardHeader>
                            <CardContent className="space-y-4 p-6">
                                <div className="grid gap-3 rounded-2xl border border-border/70 bg-muted/30 p-4 text-sm md:grid-cols-4">
                                    <div><p className="text-xs font-semibold uppercase tracking-[0.18em] text-muted-foreground">Ordered qty</p><p className="mt-1 font-medium">{order.fulfillment_summary.total_ordered_quantity}</p></div>
                                    <div><p className="text-xs font-semibold uppercase tracking-[0.18em] text-muted-foreground">Allocated qty</p><p className="mt-1 font-medium">{order.fulfillment_summary.total_allocated_quantity}</p></div>
                                    <div><p className="text-xs font-semibold uppercase tracking-[0.18em] text-muted-foreground">Delivered qty</p><p className="mt-1 font-medium">{order.fulfillment_summary.total_delivered_quantity}</p></div>
                                    <div><p className="text-xs font-semibold uppercase tracking-[0.18em] text-muted-foreground">Remaining qty</p><p className="mt-1 font-medium">{order.fulfillment_summary.total_remaining_quantity}</p></div>
                                </div>

                                {canCreateShipment ? (
                                    <form className="space-y-4 rounded-2xl border border-border/70 bg-background/80 p-4" onSubmit={(event) => {
                                        event.preventDefault();
                                        shipmentForm.transform((data) => ({
                                            ...data,
                                            warehouse_location_id: data.warehouse_location_id === '' ? null : Number(data.warehouse_location_id),
                                            items: data.items.filter((item) => Number(item.quantity) > 0).map((item) => ({ order_item_id: item.order_item_id, quantity: Number(item.quantity) })),
                                        }));
                                        shipmentForm.post(ShipmentController.store.url(), { preserveScroll: true });
                                    }}>
                                        <div>
                                            <p className="text-sm font-medium">Create shipment</p>
                                            <p className="text-sm text-muted-foreground">Allocate the remaining quantities for this shipment. Warehouse and tracking details are optional at creation time.</p>
                                        </div>
                                        <FieldError message={shipmentForm.errors.order_id} />
                                        <FieldError message={shipmentForm.errors.items} />
                                        <div className="space-y-3">
                                            {order.items.map((item, index) => (
                                                <div key={item.id} className="rounded-2xl border border-border/60 bg-muted/25 p-4">
                                                    <div className="flex items-center justify-between gap-3">
                                                        <div><p className="font-medium">{item.product_name}</p><p className="text-sm text-muted-foreground">{item.variant_name ?? 'Base product'}{item.sku ? ` • ${item.sku}` : ''}</p></div>
                                                        <p className="text-sm text-muted-foreground">Remaining <span className="font-medium text-foreground">{item.remaining_quantity}</span></p>
                                                    </div>
                                                    <div className="mt-3 space-y-2">
                                                        <Label htmlFor={`shipment-quantity-${item.id}`}>Shipment quantity</Label>
                                                        <Input id={`shipment-quantity-${item.id}`} type="number" min="0" max={item.remaining_quantity} value={shipmentForm.data.items[index]?.quantity ?? ''} onChange={(event) => setShipmentQuantity(item.id, event.target.value)} placeholder={item.remaining_quantity > 0 ? `0-${item.remaining_quantity}` : 'Unavailable'} disabled={item.remaining_quantity === 0} />
                                                        <FieldError message={shipmentForm.errors[`items.${index}.quantity`]} />
                                                    </div>
                                                </div>
                                            ))}
                                        </div>
                                        <div className="grid gap-4 md:grid-cols-2">
                                            <div className="space-y-2">
                                                <Label htmlFor="warehouse_location_id">Warehouse</Label>
                                                <Select value={shipmentForm.data.warehouse_location_id || EMPTY_SENTINEL} onValueChange={(value) => shipmentForm.setData('warehouse_location_id', value === EMPTY_SENTINEL ? '' : value)}>
                                                    <SelectTrigger id="warehouse_location_id"><SelectValue placeholder="Select a warehouse" /></SelectTrigger>
                                                    <SelectContent>
                                                        <SelectItem value={EMPTY_SENTINEL}>No warehouse yet</SelectItem>
                                                        {availableWarehouses.map((warehouse) => <SelectItem key={warehouse.id} value={warehouse.id.toString()}>{warehouse.name} ({warehouse.code})</SelectItem>)}
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
                                        <div className="grid gap-4 md:grid-cols-2">
                                            <div className="space-y-2"><Label htmlFor="tracking_number">Tracking number</Label><Input id="tracking_number" value={shipmentForm.data.tracking_number} onChange={(event) => shipmentForm.setData('tracking_number', event.target.value)} placeholder="Optional tracking number" /><FieldError message={shipmentForm.errors.tracking_number} /></div>
                                            <div className="space-y-2"><Label htmlFor="tracking_url">Tracking URL</Label><Input id="tracking_url" value={shipmentForm.data.tracking_url} onChange={(event) => shipmentForm.setData('tracking_url', event.target.value)} placeholder="https://..." /><FieldError message={shipmentForm.errors.tracking_url} /></div>
                                        </div>
                                        <div className="grid gap-4 md:grid-cols-2">
                                            <div className="space-y-2"><Label htmlFor="rider_name">Rider name</Label><Input id="rider_name" value={shipmentForm.data.rider_name} onChange={(event) => shipmentForm.setData('rider_name', event.target.value)} placeholder="Optional rider" /><FieldError message={shipmentForm.errors.rider_name} /></div>
                                            <div className="space-y-2"><Label htmlFor="rider_phone">Rider phone</Label><Input id="rider_phone" value={shipmentForm.data.rider_phone} onChange={(event) => shipmentForm.setData('rider_phone', event.target.value)} placeholder="Optional rider phone" /><FieldError message={shipmentForm.errors.rider_phone} /></div>
                                        </div>
                                        <div className="space-y-2"><Label htmlFor="shipment_notes">Shipment note</Label><textarea id="shipment_notes" value={shipmentForm.data.notes} onChange={(event) => shipmentForm.setData('notes', event.target.value)} className="min-h-28 w-full rounded-md border border-input bg-background px-3 py-2 text-sm" placeholder="Optional warehouse or dispatch note" /><FieldError message={shipmentForm.errors.notes} /></div>
                                        <Button type="submit" disabled={shipmentForm.processing} className="w-full">{shipmentForm.processing ? 'Creating shipment…' : 'Create shipment'}</Button>
                                    </form>
                                ) : shipmentCreationMessage ? <div className="rounded-2xl border border-dashed border-border/70 bg-muted/20 p-4 text-sm text-muted-foreground">{shipmentCreationMessage}</div> : null}

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
                                            <p>Shipped at: {formatDate(shipment.shipped_at)}</p>
                                        </div>
                                        {shipment.items.length > 0 ? <div className="mt-3 space-y-2">{shipment.items.map((item) => <div key={`${shipment.id}-${item.order_item_id}`} className="rounded-xl border border-border/60 bg-muted/25 px-3 py-2 text-xs text-muted-foreground"><span className="font-medium text-foreground">{item.product_name ?? 'Order item'}</span>{item.sku ? ` • ${item.sku}` : ''}{` • Qty ${item.quantity}`}</div>)}</div> : null}
                                    </div>
                                ))}
                            </CardContent>
                        </Card>

                        {(order.notes || order.delivery_notes) ? (
                            <Card className="overflow-hidden border-border/70 pt-0">
                                <CardHeader className="border-b border-border/70 bg-muted/30 py-6"><CardTitle>Notes</CardTitle></CardHeader>
                                <CardContent className="space-y-3 p-6 text-sm">
                                    {order.notes ? <div><p className="font-medium">Internal notes</p><p className="whitespace-pre-wrap text-muted-foreground">{order.notes}</p></div> : null}
                                    {order.delivery_notes ? <div><p className="font-medium">Delivery notes</p><p className="whitespace-pre-wrap text-muted-foreground">{order.delivery_notes}</p></div> : null}
                                </CardContent>
                            </Card>
                        ) : null}
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
