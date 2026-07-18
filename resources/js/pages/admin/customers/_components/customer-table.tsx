import { Link } from '@inertiajs/react';
import { EmptyState } from '@/components/shared/empty-state/empty-state';
import { StatusBadge } from '@/components/shared/status-badge/status-badge';
import { Button } from '@/components/ui/button';
import { formatDate } from '@/lib/formatters/date';
import type { AdminCustomer } from '@/types/admin/user';

const customerPath = (id: number) => `/admin/customers/${id}`;

export function CustomerTable({ customers }: { customers: AdminCustomer[] }) {
    if (customers.length === 0) {
        return (
            <div className="rounded-3xl border border-dashed border-border/70 bg-muted/30 px-6 py-14 text-center">
                <EmptyState
                    title="No customers found"
                    description="Try a different search or status filter."
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
                            <th className="px-5 py-4 text-left font-medium text-muted-foreground">
                                Customer
                            </th>
                            <th className="px-5 py-4 text-left font-medium text-muted-foreground">
                                Status
                            </th>
                            <th className="px-5 py-4 text-left font-medium text-muted-foreground">
                                Commercial activity
                            </th>
                            <th className="px-5 py-4 text-left font-medium text-muted-foreground">
                                Created
                            </th>
                            <th className="px-5 py-4 text-left font-medium text-muted-foreground">
                                Action
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        {customers.map((customer) => (
                            <tr
                                key={customer.id}
                                className="border-t border-border/60 align-top"
                            >
                                <td className="px-5 py-4">
                                    <div className="space-y-1">
                                        <Link
                                            href={customerPath(customer.id)}
                                            className="font-semibold text-foreground transition hover:text-primary"
                                        >
                                            {customer.name}
                                        </Link>
                                        <p className="text-xs text-muted-foreground">
                                            {customer.email}
                                        </p>
                                    </div>
                                </td>
                                <td className="px-5 py-4">
                                    <StatusBadge status={customer.status} />
                                </td>
                                <td className="px-5 py-4 text-muted-foreground">
                                    <div className="space-y-1 text-xs">
                                        <p>{customer.orders_count} orders</p>
                                        <p>
                                            {customer.payments_count} payments
                                        </p>
                                    </div>
                                </td>
                                <td className="px-5 py-4 text-muted-foreground">
                                    {formatDate(customer.created_at)}
                                </td>
                                <td className="px-5 py-4">
                                    <Button variant="outline" size="sm" asChild>
                                        <Link href={customerPath(customer.id)}>
                                            View customer
                                        </Link>
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
