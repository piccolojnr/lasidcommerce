import { Link } from '@inertiajs/react';
import * as OrderController from '@/actions/App/Http/Controllers/Admin/Orders/OrderController';
import { EmptyState } from '@/components/shared/empty-state/empty-state';
import { StatusBadge } from '@/components/shared/status-badge/status-badge';
import { Button } from '@/components/ui/button';
import { formatDate } from '@/lib/formatters/date';
import { formatMoney } from '@/lib/formatters/money';
import type { AdminOrder } from '@/types/admin/order';

interface OrderTableProps {
    orders: AdminOrder[];
}

export function OrderTable({ orders }: OrderTableProps) {
    if (orders.length === 0) {
        return (
            <div className="rounded-3xl border border-dashed border-border/70 bg-muted/30 px-6 py-14 text-center">
                <EmptyState
                    title="No orders found"
                    description="Try a different filter. There is nothing useful in this result set."
                />
            </div>
        );
    }

    return (
        <div className="overflow-hidden rounded-3xl border border-border/70 bg-background shadow-sm">
            <div className="overflow-x-auto">
                <table className="min-w-full text-sm">
                    <thead className="bg-muted/35">
                        <tr>
                            <th className="px-5 py-4 text-left font-medium text-muted-foreground">Order</th>
                            <th className="px-5 py-4 text-left font-medium text-muted-foreground">Commercial state</th>
                            <th className="px-5 py-4 text-left font-medium text-muted-foreground">Total</th>
                            <th className="px-5 py-4 text-left font-medium text-muted-foreground">Placed</th>
                            <th className="px-5 py-4 text-left font-medium text-muted-foreground">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        {orders.map((order) => (
                            <tr key={order.id} className="border-t border-border/60 align-top">
                                <td className="px-5 py-4">
                                    <div className="space-y-1">
                                        <Link
                                            href={OrderController.show.url(order)}
                                            className="font-semibold text-foreground transition hover:text-primary"
                                        >
                                            {order.order_number}
                                        </Link>
                                        <p className="text-xs text-muted-foreground">
                                            {order.email}
                                        </p>
                                    </div>
                                </td>
                                <td className="px-5 py-4">
                                    <div className="flex flex-wrap gap-2">
                                        <StatusBadge status={order.status} />
                                        <StatusBadge status={order.payment_status} />
                                        <StatusBadge status={order.fulfillment_status} />
                                        <StatusBadge status={order.shipping_summary} />
                                    </div>
                                </td>
                                <td className="px-5 py-4">
                                    <div className="space-y-1">
                                        <p className="font-semibold">
                                            {formatMoney(order.total_amount, order.currency_code)}
                                        </p>
                                        <p className="text-xs text-muted-foreground">
                                            Payment: {order.payment_status}
                                        </p>
                                    </div>
                                </td>
                                <td className="px-5 py-4 text-muted-foreground">
                                    {formatDate(order.placed_at)}
                                </td>
                                <td className="px-5 py-4">
                                    <Button variant="outline" size="sm" asChild>
                                        <Link href={OrderController.show.url(order)}>View order</Link>
                                    </Button>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </div>
    );
}
