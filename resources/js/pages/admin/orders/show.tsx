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
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { AdminLayout } from '@/layouts/app/admin-layout';
import { formatDate } from '@/lib/formatters/date';
import { formatMoney } from '@/lib/formatters/money';
import type { AdminOrderDetail } from '@/types/admin/order';

interface Props {
    order: AdminOrderDetail;
    allowedStatuses: string[];
}

export default function OrderShowPage({ order, allowedStatuses }: Props) {
    const statusForm = useForm({
        status: '',
        note: '',
    });

    return (
        <AdminLayout
            title="Order Details"
            description="Inspect order, payment, and fulfillment state."
        >
            <div className="mx-auto w-full max-w-6xl space-y-6">
                <PageHeader
                    title={order.order_number}
                    description={`Placed ${formatDate(order.placed_at)} by ${order.email}.`}
                    actions={
                        <Button variant="outline" asChild>
                            <Link href={OrderController.index.url()}>Back to orders</Link>
                        </Button>
                    }
                />

                <div className="grid gap-6 xl:grid-cols-[2fr_1fr]">
                    <div className="space-y-6">
                        <Card>
                            <CardHeader>
                                <CardTitle>Order summary</CardTitle>
                            </CardHeader>
                            <CardContent className="grid gap-3 text-sm md:grid-cols-2">
                                <div className="flex items-center justify-between border-b pb-3">
                                    <span className="text-muted-foreground">Customer</span>
                                    <span>{order.email}</span>
                                </div>
                                <div className="flex items-center justify-between border-b pb-3">
                                    <span className="text-muted-foreground">Placed</span>
                                    <span>{formatDate(order.placed_at)}</span>
                                </div>
                                <div className="flex items-center justify-between border-b pb-3">
                                    <span className="text-muted-foreground">Status</span>
                                    <StatusBadge status={order.status} />
                                </div>
                                <div className="flex items-center justify-between border-b pb-3">
                                    <span className="text-muted-foreground">Payment</span>
                                    <StatusBadge status={order.payment_status} />
                                </div>
                                <div className="flex items-center justify-between border-b pb-3">
                                    <span className="text-muted-foreground">Fulfillment</span>
                                    <StatusBadge status={order.fulfillment_status} />
                                </div>
                                <div className="flex items-center justify-between border-b pb-3">
                                    <span className="text-muted-foreground">Shipping method</span>
                                    <span>{order.shipping_method_name ?? 'N/A'}</span>
                                </div>
                                <div className="flex items-center justify-between border-b pb-3">
                                    <span className="text-muted-foreground">Shipping zone</span>
                                    <span>{order.shipping_zone_name ?? 'N/A'}</span>
                                </div>
                                <div className="flex items-center justify-between border-b pb-3">
                                    <span className="text-muted-foreground">Subtotal</span>
                                    <span>{formatMoney(order.subtotal_amount, order.currency_code)}</span>
                                </div>
                                <div className="flex items-center justify-between border-b pb-3">
                                    <span className="text-muted-foreground">Discount</span>
                                    <span>{formatMoney(order.discount_amount, order.currency_code)}</span>
                                </div>
                                <div className="flex items-center justify-between border-b pb-3">
                                    <span className="text-muted-foreground">Tax</span>
                                    <span>{formatMoney(order.tax_amount, order.currency_code)}</span>
                                </div>
                                <div className="flex items-center justify-between border-b pb-3">
                                    <span className="text-muted-foreground">Shipping</span>
                                    <span>{formatMoney(order.shipping_amount, order.currency_code)}</span>
                                </div>
                                <div className="flex items-center justify-between">
                                    <span className="text-muted-foreground">Total</span>
                                    <span className="font-semibold">
                                        {formatMoney(order.total_amount, order.currency_code)}
                                    </span>
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>Line items</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                {order.items.length === 0 ? (
                                    <EmptyState
                                        title="No order items"
                                        description="This order has no line items attached, which would be a problem."
                                    />
                                ) : (
                                    <div className="space-y-3">
                                        {order.items.map((item) => (
                                            <div key={item.id} className="rounded-lg border p-4">
                                                <div className="flex flex-col gap-2 md:flex-row md:items-start md:justify-between">
                                                    <div>
                                                        <p className="font-medium">{item.product_name}</p>
                                                        <p className="text-sm text-muted-foreground">
                                                            {item.variant_name ?? 'Base product'}
                                                            {item.sku ? ` • ${item.sku}` : ''}
                                                        </p>
                                                    </div>
                                                    <div className="text-right text-sm">
                                                        <p>
                                                            {item.quantity} ×{' '}
                                                            {formatMoney(item.unit_price, order.currency_code)}
                                                        </p>
                                                        <p className="font-medium">
                                                            {formatMoney(item.line_total, order.currency_code)}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                )}
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>Status history</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                {order.history.length === 0 ? (
                                    <EmptyState
                                        title="No history yet"
                                        description="No status transitions have been recorded for this order."
                                    />
                                ) : (
                                    <div className="space-y-4">
                                        {order.history.map((entry) => (
                                            <div key={entry.id} className="rounded-lg border p-4">
                                                <div className="space-y-2">
                                                    <div className="flex items-center gap-2">
                                                        {entry.from_status ? (
                                                            <StatusBadge status={entry.from_status} />
                                                        ) : (
                                                            <span className="text-sm text-muted-foreground">
                                                                Start
                                                            </span>
                                                        )}
                                                        <span className="text-muted-foreground">→</span>
                                                        <StatusBadge status={entry.to_status} />
                                                    </div>
                                                    <p className="text-sm text-muted-foreground">
                                                        {entry.changed_by_name ?? 'System'} •{' '}
                                                        {formatDate(entry.created_at)}
                                                    </p>
                                                    {entry.note ? (
                                                        <p className="text-sm">{entry.note}</p>
                                                    ) : null}
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
                                        No further status transitions are allowed for this order.
                                    </p>
                                ) : (
                                    <form
                                        className="space-y-4"
                                        onSubmit={(event) => {
                                            event.preventDefault();
                                            statusForm.patch(
                                                OrderStatusController.update.url(order),
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
                                                <SelectTrigger
                                                    id="status"
                                                    className="w-full"
                                                >
                                                    <SelectValue placeholder="Select a status" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    {allowedStatuses.map((status) => (
                                                        <SelectItem
                                                            key={status}
                                                            value={status}
                                                        >
                                                            {status.replace(
                                                                /\b\w/g,
                                                                (character) =>
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
                                                placeholder="Optional audit note"
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
                                <CardTitle>Shipping address</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-2 text-sm">
                                {order.shipping_address ? (
                                    <>
                                        <p className="font-medium">{order.shipping_address.name}</p>
                                        <p>{order.shipping_address.address_line_1}</p>
                                        {order.shipping_address.address_line_2 ? (
                                            <p>{order.shipping_address.address_line_2}</p>
                                        ) : null}
                                        <p>
                                            {[
                                                order.shipping_address.city,
                                                order.shipping_address.district,
                                                order.shipping_address.region,
                                                order.shipping_address.country,
                                            ]
                                                .filter(Boolean)
                                                .join(', ')}
                                        </p>
                                        {order.shipping_address.phone ? (
                                            <p className="text-muted-foreground">
                                                {order.shipping_address.phone}
                                            </p>
                                        ) : null}
                                    </>
                                ) : (
                                    <p className="text-muted-foreground">
                                        No shipping address captured for this order.
                                    </p>
                                )}
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>Payments</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-3">
                                {order.payments.length === 0 ? (
                                    <p className="text-sm text-muted-foreground">
                                        No payment attempts recorded.
                                    </p>
                                ) : (
                                    order.payments.map((payment) => (
                                        <div
                                            key={payment.id}
                                            className="rounded-lg border p-3 text-sm"
                                        >
                                            <div className="mb-2 flex items-center justify-between">
                                                <span className="font-medium">
                                                    {payment.provider}
                                                </span>
                                                <StatusBadge status={payment.status} />
                                            </div>
                                            <div className="space-y-1 text-muted-foreground">
                                                <p>Reference: {payment.reference}</p>
                                                <p>
                                                    Amount:{' '}
                                                    {formatMoney(
                                                        payment.amount,
                                                        payment.currency_code,
                                                    )}
                                                </p>
                                                <p>Paid at: {formatDate(payment.paid_at)}</p>
                                            </div>
                                        </div>
                                    ))
                                )}
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>Shipments</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-3">
                                {order.shipments.length === 0 ? (
                                    <p className="text-sm text-muted-foreground">
                                        No shipments have been created for this order yet.
                                    </p>
                                ) : (
                                    order.shipments.map((shipment) => (
                                        <div
                                            key={shipment.id}
                                            className="rounded-lg border p-3 text-sm"
                                        >
                                            <div className="mb-2 flex items-center justify-between">
                                                <Link
                                                    href={ShipmentController.show.url(shipment.id)}
                                                    className="font-medium hover:underline"
                                                >
                                                    Shipment #{shipment.id}
                                                </Link>
                                                <StatusBadge status={shipment.status} />
                                            </div>
                                            <div className="space-y-1 text-muted-foreground">
                                                <p>
                                                    Tracking:{' '}
                                                    {shipment.tracking_number ?? 'N/A'}
                                                </p>
                                                <p>
                                                    Carrier: {shipment.carrier_name ?? 'N/A'}
                                                </p>
                                                <p>
                                                    Shipped at:{' '}
                                                    {formatDate(shipment.shipped_at)}
                                                </p>
                                            </div>
                                        </div>
                                    ))
                                )}
                            </CardContent>
                        </Card>

                        {order.notes || order.delivery_notes ? (
                            <Card>
                                <CardHeader>
                                    <CardTitle>Notes</CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-3 text-sm">
                                    {order.notes ? (
                                        <div>
                                            <p className="font-medium">Internal notes</p>
                                            <p className="text-muted-foreground whitespace-pre-wrap">
                                                {order.notes}
                                            </p>
                                        </div>
                                    ) : null}
                                    {order.delivery_notes ? (
                                        <div>
                                            <p className="font-medium">
                                                Delivery notes
                                            </p>
                                            <p className="text-muted-foreground whitespace-pre-wrap">
                                                {order.delivery_notes}
                                            </p>
                                        </div>
                                    ) : null}
                                </CardContent>
                            </Card>
                        ) : null}
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
