import { Link, useForm } from '@inertiajs/react';
import * as OrderController from '@/actions/App/Http/Controllers/Admin/Orders/OrderController';
import * as ShipmentController from '@/actions/App/Http/Controllers/Admin/Shipments/ShipmentController';
import * as ShipmentStatusController from '@/actions/App/Http/Controllers/Admin/Shipments/ShipmentStatusController';
import { PageHeader } from '@/components/shared/page-header/page-header';
import { StatusBadge } from '@/components/shared/status-badge/status-badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { AdminLayout } from '@/layouts/app/admin-layout';
import { formatDate } from '@/lib/formatters/date';
import type { AdminShipmentDetail } from '@/types/admin/shipment';

interface Props {
    shipment: AdminShipmentDetail;
    allowedStatuses: string[];
}

export default function ShipmentShowPage({ shipment, allowedStatuses }: Props) {
    const statusForm = useForm({ status: '', note: '' });

    const runStatusUpdate = (status: string) => {
        statusForm.transform((data) => ({ ...data, status }));
        statusForm.patch(ShipmentStatusController.update.url(shipment), {
            preserveScroll: true,
        });
    };

    return (
        <AdminLayout title="Shipment Details" description="Inspect shipment routing and delivery events.">
            <div className="mx-auto w-full max-w-7xl space-y-8">
                <PageHeader
                    title={`Shipment #${shipment.id}`}
                    description={`Current state: ${shipment.status.replace(/_/g, ' ')}.`}
                    actions={
                        <div className="flex items-center gap-2">
                            <Button variant="outline" asChild><Link href={ShipmentController.index.url()}>Back to shipments</Link></Button>
                            {shipment.order ? <Button asChild><Link href={OrderController.show.url(shipment.order.id)}>View order</Link></Button> : null}
                        </div>
                    }
                />

                <div className="grid gap-4 lg:grid-cols-[1.25fr_0.75fr]">
                    <Card className="border-border/70 bg-muted/30">
                        <CardHeader className="space-y-3">
                            <div className="flex items-center justify-between gap-3">
                                <p className="text-xs font-semibold uppercase tracking-[0.24em] text-muted-foreground">Fulfillment stage</p>
                                <StatusBadge status={shipment.status} />
                            </div>
                            <CardTitle className="text-2xl">{shipment.tracking_number ?? `Shipment #${shipment.id}`}</CardTitle>
                            <p className="text-sm leading-6 text-muted-foreground">Shipment status tracks real-world movement only. If you need to move the business/order stage, go back to the order page.</p>
                        </CardHeader>
                        <CardContent className="grid gap-4 border-t border-border/70 pt-6 md:grid-cols-3">
                            <div><p className="text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">Order</p><p className="mt-2 font-semibold">{shipment.order?.order_number ?? 'N/A'}</p></div>
                            <div><p className="text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">Carrier</p><p className="mt-2 font-semibold">{shipment.carrier_name ?? 'N/A'}</p></div>
                            <div><p className="text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">Items</p><p className="mt-2 font-semibold">{shipment.items.length}</p></div>
                        </CardContent>
                    </Card>
                    <Card className="border-border/70 bg-primary/5">
                        <CardHeader><CardTitle className="text-xl">Milestones</CardTitle></CardHeader>
                        <CardContent className="space-y-3 text-sm">
                            <div className="flex items-center justify-between"><span className="text-muted-foreground">Packed</span><span className="font-medium">{formatDate(shipment.packed_at)}</span></div>
                            <div className="flex items-center justify-between"><span className="text-muted-foreground">Shipped</span><span className="font-medium">{formatDate(shipment.shipped_at)}</span></div>
                            <div className="flex items-center justify-between"><span className="text-muted-foreground">Delivered</span><span className="font-medium">{formatDate(shipment.delivered_at)}</span></div>
                            <div className="flex items-center justify-between"><span className="text-muted-foreground">Failed</span><span className="font-medium">{formatDate(shipment.failed_at)}</span></div>
                            <div className="flex items-center justify-between"><span className="text-muted-foreground">Returned</span><span className="font-medium">{formatDate(shipment.returned_at)}</span></div>
                        </CardContent>
                    </Card>
                </div>

                <div className="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
                    <div className="space-y-6">
                        <Card className="overflow-hidden border-border/70 pt-0">
                            <CardHeader className="border-b border-border/70 bg-muted/30 py-6"><CardTitle>Shipment controls</CardTitle></CardHeader>
                            <CardContent className="space-y-4 p-6">
                                <div className="rounded-2xl border border-border/70 bg-muted/20 p-4 text-sm text-muted-foreground">
                                    Use these controls only for packing, dispatch, delivery, failure, return, or cancellation of this shipment.
                                </div>
                                {allowedStatuses.length > 0 ? (
                                    <div className="flex flex-wrap gap-2">
                                        {allowedStatuses.map((status) => (
                                            <Button key={status} type="button" variant={allowedStatuses.length === 1 ? 'default' : 'outline'} disabled={statusForm.processing} onClick={() => runStatusUpdate(status)}>
                                                {status.replace(/\b\w/g, (character) => character.toUpperCase())}
                                            </Button>
                                        ))}
                                    </div>
                                ) : <p className="text-sm text-muted-foreground">No further status transitions are allowed for this shipment.</p>}
                                <form className="space-y-4 border-t border-border/70 pt-4" onSubmit={(event) => {
 event.preventDefault(); statusForm.patch(ShipmentStatusController.update.url(shipment), { preserveScroll: true }); 
}}>
                                    <div className="space-y-2">
                                        <Label htmlFor="status">Advanced shipment update</Label>
                                        <Select value={statusForm.data.status} onValueChange={(value) => statusForm.setData('status', value)}>
                                            <SelectTrigger id="status"><SelectValue placeholder="Select a status" /></SelectTrigger>
                                            <SelectContent>{allowedStatuses.map((status) => <SelectItem key={status} value={status}>{status.replace(/\b\w/g, (character) => character.toUpperCase())}</SelectItem>)}</SelectContent>
                                        </Select>
                                    </div>
                                    <div className="space-y-2">
                                        <Label htmlFor="note">Note</Label>
                                        <Input id="note" value={statusForm.data.note} onChange={(event) => statusForm.setData('note', event.target.value)} placeholder="Optional operational note" />
                                    </div>
                                    <Button type="submit" disabled={statusForm.processing || !statusForm.data.status} className="w-full">{statusForm.processing ? 'Updating…' : 'Update shipment status'}</Button>
                                </form>
                            </CardContent>
                        </Card>

                        <Card className="overflow-hidden border-border/70 pt-0">
                            <CardHeader className="border-b border-border/70 bg-muted/30 py-6"><CardTitle>Assignment</CardTitle></CardHeader>
                            <CardContent className="space-y-4 p-6 text-sm">
                                <div>
                                    <p className="font-medium">Warehouse</p>
                                    <p className="text-muted-foreground">{shipment.warehouse_location ? `${shipment.warehouse_location.name} (${shipment.warehouse_location.code})` : 'No warehouse attached'}</p>
                                </div>
                                <div>
                                    <p className="font-medium">Shipping method</p>
                                    <p className="text-muted-foreground">{shipment.shipping_method ? `${shipment.shipping_method.name} • ${shipment.shipping_method.code}` : 'No shipping method attached'}</p>
                                </div>
                                <div>
                                    <p className="font-medium">Rider</p>
                                    <p className="text-muted-foreground">{shipment.rider_name ?? 'No rider assigned'}{shipment.rider_phone ? ` • ${shipment.rider_phone}` : ''}</p>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <div className="space-y-6">
                        <Card className="overflow-hidden border-border/70 pt-0">
                            <CardHeader className="border-b border-border/70 bg-muted/30 py-6"><CardTitle>Shipment items</CardTitle></CardHeader>
                            <CardContent className="space-y-3 p-6">
                                {shipment.items.map((item) => (
                                    <div key={item.id} className="rounded-2xl border border-border/70 bg-background/80 p-4">
                                        <div className="flex items-start justify-between gap-3">
                                            <div>
                                                <p className="font-medium">{item.product_name ?? 'Unknown product'}</p>
                                                <p className="text-sm text-muted-foreground">{item.variant_name ?? 'Base product'}{item.sku ? ` • ${item.sku}` : ''}</p>
                                            </div>
                                            <div className="text-right text-sm"><p>Qty {item.quantity}</p><p className="text-muted-foreground">Order item #{item.order_item_id}</p></div>
                                        </div>
                                    </div>
                                ))}
                            </CardContent>
                        </Card>
                        {shipment.notes ? <Card className="overflow-hidden border-border/70 pt-0"><CardHeader className="border-b border-border/70 bg-muted/30 py-6"><CardTitle>Notes</CardTitle></CardHeader><CardContent className="whitespace-pre-wrap p-6 text-sm text-muted-foreground">{shipment.notes}</CardContent></Card> : null}
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
