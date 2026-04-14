import { Link } from '@inertiajs/react';
import * as OrderController from '@/actions/App/Http/Controllers/Admin/Orders/OrderController';
import { EmptyState } from '@/components/shared/empty-state/empty-state';
import { PageHeader } from '@/components/shared/page-header/page-header';
import { StatusBadge } from '@/components/shared/status-badge/status-badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { AdminLayout } from '@/layouts/app/admin-layout';
import { formatDate } from '@/lib/formatters/date';
import { formatMoney } from '@/lib/formatters/money';
import type { AdminCustomerDetail } from '@/types/admin/user';

interface Props {
    customer: AdminCustomerDetail;
}

export default function CustomerShowPage({ customer }: Props) {
    return (
        <AdminLayout title="Customer Details" description="Inspect customer profile and activity.">
            <div className="mx-auto w-full max-w-7xl space-y-8">
                <PageHeader
                    title={customer.name}
                    description={`Customer account created ${formatDate(customer.created_at)}.`}
                    actions={
                        <Button variant="outline" asChild>
                            <Link href="/admin/customers">Back to customers</Link>
                        </Button>
                    }
                />

                <div className="grid gap-4 lg:grid-cols-3">
                    <Card className="border-border/70 bg-muted/30 lg:col-span-2">
                        <CardHeader className="space-y-3">
                            <div className="flex items-center justify-between gap-3">
                                <p className="text-xs font-semibold uppercase tracking-[0.24em] text-muted-foreground">
                                    Customer profile
                                </p>
                                <StatusBadge status={customer.status} />
                            </div>
                            <CardTitle className="text-2xl">{customer.name}</CardTitle>
                            <p className="max-w-2xl text-sm leading-6 text-muted-foreground">
                                {customer.email} {customer.phone ? `• ${customer.phone}` : '• no phone number on record'}.
                            </p>
                        </CardHeader>
                        <CardContent className="grid gap-4 border-t border-border/70 pt-6 md:grid-cols-3">
                            <div>
                                <p className="text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">Email verified</p>
                                <p className="mt-2 font-semibold">{formatDate(customer.email_verified_at)}</p>
                            </div>
                            <div>
                                <p className="text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">Two-factor</p>
                                <p className="mt-2 font-semibold">
                                    {customer.two_factor_confirmed_at
                                        ? `Enabled • ${formatDate(customer.two_factor_confirmed_at)}`
                                        : 'Disabled'}
                                </p>
                            </div>
                            <div>
                                <p className="text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">Addresses</p>
                                <p className="mt-2 font-semibold">{customer.addresses_count}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <Card className="border-border/70 bg-primary/5">
                        <CardHeader className="space-y-2">
                            <p className="text-xs font-semibold uppercase tracking-[0.24em] text-primary">
                                Activity footprint
                            </p>
                            <CardTitle className="text-xl">Commercial context</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-3 text-sm">
                            <div className="flex items-center justify-between">
                                <span className="text-muted-foreground">Orders</span>
                                <span className="font-medium">{customer.orders_count}</span>
                            </div>
                            <div className="flex items-center justify-between">
                                <span className="text-muted-foreground">Payments</span>
                                <span className="font-medium">{customer.payments_count}</span>
                            </div>
                            <div className="flex items-center justify-between">
                                <span className="text-muted-foreground">Addresses</span>
                                <span className="font-medium">{customer.addresses_count}</span>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <div className="grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
                    <div className="space-y-6">
                        <Card className="overflow-hidden border-border/70 pt-0">
                            <CardHeader className="border-b border-border/70 bg-muted/30 py-6">
                                <CardTitle>Recent orders</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-3 p-6">
                                {customer.recent_orders.length === 0 ? (
                                    <EmptyState title="No orders yet" description="This customer has not placed any orders." />
                                ) : (
                                    customer.recent_orders.map((order) => (
                                        <div key={order.id} className="rounded-2xl border border-border/70 bg-background/80 p-4 text-sm">
                                            <div className="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                                                <div className="space-y-1">
                                                    <Link
                                                        href={OrderController.show.url(order.id)}
                                                        className="font-medium transition hover:text-primary"
                                                    >
                                                        {order.order_number}
                                                    </Link>
                                                    <p className="text-muted-foreground">{formatDate(order.placed_at)}</p>
                                                </div>
                                                <div className="text-right">
                                                    <StatusBadge status={order.status} />
                                                    <p className="mt-1 font-medium">
                                                        {formatMoney(order.total_amount, order.currency_code)}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    ))
                                )}
                            </CardContent>
                        </Card>

                        <Card className="overflow-hidden border-border/70 pt-0">
                            <CardHeader className="border-b border-border/70 bg-muted/30 py-6">
                                <CardTitle>Recent payments</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-3 p-6">
                                {customer.recent_payments.length === 0 ? (
                                    <EmptyState title="No payments yet" description="This customer has no payment attempts on record." />
                                ) : (
                                    customer.recent_payments.map((payment) => (
                                        <div key={payment.id} className="rounded-2xl border border-border/70 bg-background/80 p-4 text-sm">
                                            <div className="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                                                <div className="space-y-1">
                                                    <p className="font-medium">{payment.reference}</p>
                                                    <p className="text-muted-foreground">
                                                        {payment.provider} • {formatDate(payment.paid_at)}
                                                    </p>
                                                </div>
                                                <div className="text-right">
                                                    <StatusBadge status={payment.status} />
                                                    <p className="mt-1 font-medium">
                                                        {formatMoney(payment.amount, payment.currency_code)}
                                                    </p>
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
                                <CardTitle>Account snapshot</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4 p-6">
                                <div className="rounded-2xl border border-border/70 bg-background/80 p-4">
                                    <p className="text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">Orders</p>
                                    <p className="mt-2 text-2xl font-semibold">{customer.orders_count}</p>
                                </div>
                                <div className="rounded-2xl border border-border/70 bg-background/80 p-4">
                                    <p className="text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">Payments</p>
                                    <p className="mt-2 text-2xl font-semibold">{customer.payments_count}</p>
                                </div>
                                <div className="rounded-2xl border border-border/70 bg-background/80 p-4">
                                    <p className="text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">Addresses</p>
                                    <p className="mt-2 text-2xl font-semibold">{customer.addresses_count}</p>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
