import { Link } from '@inertiajs/react';
import * as CouponController from '@/actions/App/Http/Controllers/Admin/Coupons/CouponController';
import * as OrderController from '@/actions/App/Http/Controllers/Admin/Orders/OrderController';
import * as ShipmentController from '@/actions/App/Http/Controllers/Admin/Shipments/ShipmentController';
import * as UserController from '@/actions/App/Http/Controllers/Admin/Users/UserController';
import { PageHeader } from '@/components/shared/page-header/page-header';
import { StatusBadge } from '@/components/shared/status-badge/status-badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { AdminLayout } from '@/layouts/app/admin-layout';
import { formatDate } from '@/lib/formatters/date';
import { formatMoney } from '@/lib/formatters/money';
import type {
    AdminDashboardOverview,
    AdminDashboardRecentOrder,
    AdminDashboardRecentPayment,
    AdminDashboardRecentShipment,
} from '@/types/admin/dashboard';

interface Props {
    overview: AdminDashboardOverview;
    recent_orders: AdminDashboardRecentOrder[];
    recent_payments: AdminDashboardRecentPayment[];
    recent_shipments: AdminDashboardRecentShipment[];
}

function OverviewCard({
    title,
    value,
    description,
    actionHref,
    actionLabel,
}: {
    title: string;
    value: string;
    description: string;
    actionHref: string;
    actionLabel: string;
}) {
    return (
        <Card className="gap-4">
            <CardHeader className="gap-2">
                <CardDescription>{title}</CardDescription>
                <CardTitle className="text-2xl">{value}</CardTitle>
            </CardHeader>
            <CardContent className="flex items-center justify-between gap-4 text-sm text-muted-foreground">
                <span>{description}</span>
                <Button variant="ghost" size="sm" asChild>
                    <Link href={actionHref}>{actionLabel}</Link>
                </Button>
            </CardContent>
        </Card>
    );
}

export default function AdminDashboardPage({
    overview,
    recent_orders,
    recent_payments,
    recent_shipments,
}: Props) {
    return (
        <AdminLayout title="Dashboard" description="Operational overview for the admin workspace.">
            <div className="space-y-6">
                <PageHeader
                    title="Dashboard"
                    description="Track live operational signals instead of placeholder theater."
                />
                <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    <OverviewCard
                        title="Revenue, last 30 days"
                        value={formatMoney(overview.revenue_last_30_days)}
                        description="Captured from successful and paid payment records."
                        actionHref={OrderController.index.url({ query: { payment_status: 'paid' } })}
                        actionLabel="View paid orders"
                    />
                    <OverviewCard
                        title="Orders, last 30 days"
                        value={overview.orders_last_30_days.toLocaleString()}
                        description="Placed orders over the last rolling 30-day window."
                        actionHref={OrderController.index.url()}
                        actionLabel="View orders"
                    />
                    <OverviewCard
                        title="Pending fulfillment"
                        value={overview.pending_fulfillment_orders.toLocaleString()}
                        description="Open orders that still are not fulfilled."
                        actionHref={OrderController.index.url({ query: { fulfillment_status: 'pending' } })}
                        actionLabel="Review backlog"
                    />
                    <OverviewCard
                        title="Low stock items"
                        value={overview.low_stock_items.toLocaleString()}
                        description="Stock items at or below their reorder level."
                        actionHref={OrderController.index.url({ query: { fulfillment_status: 'unfulfilled' } })}
                        actionLabel="Inspect operations"
                    />
                    <OverviewCard
                        title="Active coupons"
                        value={overview.active_coupons.toLocaleString()}
                        description="Coupons currently redeemable based on schedule and limits."
                        actionHref={CouponController.index.url({ query: { is_active: '1' } })}
                        actionLabel="View coupons"
                    />
                    <OverviewCard
                        title="New customers, last 30 days"
                        value={overview.new_customers_last_30_days.toLocaleString()}
                        description="Recently created customer accounts."
                        actionHref={UserController.index.url()}
                        actionLabel="View users"
                    />
                </div>

                <div className="grid gap-6 xl:grid-cols-3">
                    <Card className="gap-4 xl:col-span-1">
                        <CardHeader>
                            <CardTitle>Recent orders</CardTitle>
                            <CardDescription>Latest placed orders with payment and fulfillment state.</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            {recent_orders.length === 0 ? (
                                <p className="text-sm text-muted-foreground">No orders found.</p>
                            ) : (
                                recent_orders.map((order) => (
                                    <div key={order.id} className="space-y-2 rounded-lg border p-4">
                                        <div className="flex items-start justify-between gap-3">
                                            <div>
                                                <Link href={OrderController.show.url(order)} className="font-medium hover:underline">
                                                    {order.order_number}
                                                </Link>
                                                <p className="text-sm text-muted-foreground">{order.email ?? 'No email'}</p>
                                            </div>
                                            <p className="text-sm font-medium">
                                                {formatMoney(order.total_amount, order.currency_code)}
                                            </p>
                                        </div>
                                        <div className="flex flex-wrap gap-2">
                                            <StatusBadge status={order.status} />
                                            <StatusBadge status={order.payment_status} />
                                            <StatusBadge status={order.fulfillment_status} />
                                        </div>
                                        <p className="text-xs text-muted-foreground">{formatDate(order.placed_at)}</p>
                                    </div>
                                ))
                            )}
                        </CardContent>
                    </Card>

                    <Card className="gap-4 xl:col-span-1">
                        <CardHeader>
                            <CardTitle>Recent payments</CardTitle>
                            <CardDescription>Latest payment records across providers.</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            {recent_payments.length === 0 ? (
                                <p className="text-sm text-muted-foreground">No payments found.</p>
                            ) : (
                                recent_payments.map((payment) => (
                                    <div key={payment.id} className="space-y-2 rounded-lg border p-4">
                                        <div className="flex items-start justify-between gap-3">
                                            <div>
                                                <p className="font-medium">{payment.reference}</p>
                                                <p className="text-sm capitalize text-muted-foreground">{payment.provider}</p>
                                            </div>
                                            <p className="text-sm font-medium">
                                                {formatMoney(payment.amount, payment.currency_code)}
                                            </p>
                                        </div>
                                        <div className="flex flex-wrap items-center gap-2">
                                            <StatusBadge status={payment.status} />
                                            {payment.order ? (
                                                <Button variant="ghost" size="sm" asChild>
                                                    <Link href={OrderController.show.url(payment.order)}>{payment.order.order_number}</Link>
                                                </Button>
                                            ) : null}
                                        </div>
                                        <p className="text-xs text-muted-foreground">
                                            {formatDate(payment.paid_at ?? payment.created_at)}
                                        </p>
                                    </div>
                                ))
                            )}
                        </CardContent>
                    </Card>

                    <Card className="gap-4 xl:col-span-1">
                        <CardHeader>
                            <CardTitle>Recent shipments</CardTitle>
                            <CardDescription>Latest fulfillment records moving through delivery.</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            {recent_shipments.length === 0 ? (
                                <p className="text-sm text-muted-foreground">No shipments found.</p>
                            ) : (
                                recent_shipments.map((shipment) => (
                                    <div key={shipment.id} className="space-y-2 rounded-lg border p-4">
                                        <div className="flex items-start justify-between gap-3">
                                            <div>
                                                <Link href={ShipmentController.show.url(shipment)} className="font-medium hover:underline">
                                                    {shipment.tracking_number ?? `Shipment #${shipment.id}`}
                                                </Link>
                                                <p className="text-sm text-muted-foreground">{shipment.carrier_name ?? 'No carrier assigned'}</p>
                                            </div>
                                            <StatusBadge status={shipment.status} />
                                        </div>
                                        {shipment.order ? (
                                            <Button variant="ghost" size="sm" className="h-auto px-0" asChild>
                                                <Link href={OrderController.show.url(shipment.order)}>{shipment.order.order_number}</Link>
                                            </Button>
                                        ) : null}
                                        <p className="text-xs text-muted-foreground">
                                            {formatDate(shipment.shipped_at ?? shipment.created_at)}
                                        </p>
                                    </div>
                                ))
                            )}
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AdminLayout>
    );
}
