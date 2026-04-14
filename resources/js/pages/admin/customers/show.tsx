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
            <div className="mx-auto w-full max-w-6xl space-y-6">
                <PageHeader
                    title={customer.name}
                    description={`Customer account created ${formatDate(customer.created_at)}.`}
                    actions={
                        <Button variant="outline" asChild>
                            <Link href="/admin/customers">Back to customers</Link>
                        </Button>
                    }
                />

                <div className="grid gap-6 xl:grid-cols-[2fr_1fr]">
                    <div className="space-y-6">
                        <Card>
                            <CardHeader>
                                <CardTitle>Profile</CardTitle>
                            </CardHeader>
                            <CardContent className="grid gap-3 text-sm md:grid-cols-2">
                                <div className="flex items-center justify-between border-b pb-3">
                                    <span className="text-muted-foreground">Email</span>
                                    <span>{customer.email}</span>
                                </div>
                                <div className="flex items-center justify-between border-b pb-3">
                                    <span className="text-muted-foreground">Phone</span>
                                    <span>{customer.phone ?? 'N/A'}</span>
                                </div>
                                <div className="flex items-center justify-between border-b pb-3">
                                    <span className="text-muted-foreground">Status</span>
                                    <StatusBadge status={customer.status} />
                                </div>
                                <div className="flex items-center justify-between border-b pb-3">
                                    <span className="text-muted-foreground">Email verified</span>
                                    <span>{formatDate(customer.email_verified_at)}</span>
                                </div>
                                <div className="flex items-center justify-between border-b pb-3">
                                    <span className="text-muted-foreground">Two-factor</span>
                                    <span>
                                        {customer.two_factor_confirmed_at
                                            ? `Enabled • ${formatDate(customer.two_factor_confirmed_at)}`
                                            : 'Disabled'}
                                    </span>
                                </div>
                                <div className="flex items-center justify-between">
                                    <span className="text-muted-foreground">Addresses</span>
                                    <span>{customer.addresses_count}</span>
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>Recent orders</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-3">
                                {customer.recent_orders.length === 0 ? (
                                    <EmptyState title="No orders yet" description="This customer has not placed any orders." />
                                ) : (
                                    customer.recent_orders.map((order) => (
                                        <div key={order.id} className="rounded-lg border p-4 text-sm">
                                            <div className="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                                                <div className="space-y-1">
                                                    <Link href={OrderController.show.url(order.id)} className="font-medium hover:underline">
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

                        <Card>
                            <CardHeader>
                                <CardTitle>Recent payments</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-3">
                                {customer.recent_payments.length === 0 ? (
                                    <EmptyState title="No payments yet" description="This customer has no payment attempts on record." />
                                ) : (
                                    customer.recent_payments.map((payment) => (
                                        <div key={payment.id} className="rounded-lg border p-4 text-sm">
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
                        <Card>
                            <CardHeader>
                                <CardTitle>Activity snapshot</CardTitle>
                            </CardHeader>
                            <CardContent className="grid gap-4">
                                <div className="rounded-lg border p-4">
                                    <p className="text-sm text-muted-foreground">Orders</p>
                                    <p className="text-2xl font-semibold">{customer.orders_count}</p>
                                </div>
                                <div className="rounded-lg border p-4">
                                    <p className="text-sm text-muted-foreground">Payments</p>
                                    <p className="text-2xl font-semibold">{customer.payments_count}</p>
                                </div>
                                <div className="rounded-lg border p-4">
                                    <p className="text-sm text-muted-foreground">Addresses</p>
                                    <p className="text-2xl font-semibold">{customer.addresses_count}</p>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
