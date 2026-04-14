import { Link } from '@inertiajs/react';
import { EmptyState } from '@/components/shared/empty-state/empty-state';
import { StatusBadge } from '@/components/shared/status-badge/status-badge';
import { Button } from '@/components/ui/button';
import { formatMoney } from '@/lib/formatters/money';
import type { AdminShippingMethodSummary } from '@/types/admin/shipping';

export function MethodTable({ methods }: { methods: AdminShippingMethodSummary[] }) {
    if (methods.length === 0) {
        return (
            <div className="rounded-3xl border border-dashed border-border/70 bg-muted/30 px-6 py-14 text-center">
                <EmptyState title="No shipping methods found" description="Create reusable shipping methods before attaching them to zones." />
            </div>
        );
    }

    return (
        <div className="overflow-hidden rounded-3xl border border-border/70 bg-background shadow-sm">
            <div className="overflow-x-auto">
                <table className="min-w-full text-sm">
                    <thead className="bg-muted/35">
                        <tr>
                            <th className="px-5 py-4 text-left font-medium text-muted-foreground">Method</th>
                            <th className="px-5 py-4 text-left font-medium text-muted-foreground">Commercial setup</th>
                            <th className="px-5 py-4 text-left font-medium text-muted-foreground">Usage</th>
                            <th className="px-5 py-4 text-left font-medium text-muted-foreground">Status</th>
                            <th className="px-5 py-4 text-left font-medium text-muted-foreground">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        {methods.map((method) => (
                            <tr key={method.id} className="border-t border-border/60 align-top">
                                <td className="px-5 py-4">
                                    <div className="space-y-1">
                                        <Link href={`/admin/shipping/methods/${method.id}`} className="font-semibold transition hover:text-primary">{method.name}</Link>
                                        <p className="font-mono text-xs text-muted-foreground">{method.code}</p>
                                    </div>
                                </td>
                                <td className="px-5 py-4 text-muted-foreground">
                                    <div className="space-y-1 text-xs">
                                        <p>{method.method_type} • {method.price_type}</p>
                                        <p>{method.flat_rate_amount !== null ? formatMoney(method.flat_rate_amount) : 'No flat rate'}</p>
                                        <p>{method.min_delivery_days ?? 'N/A'}-{method.max_delivery_days ?? 'N/A'} days</p>
                                    </div>
                                </td>
                                <td className="px-5 py-4 text-muted-foreground">
                                    <div className="space-y-1 text-xs">
                                        <p>{method.shipping_zones_count ?? 0} zones</p>
                                        <p>{method.orders_count ?? 0} orders</p>
                                        <p>{method.shipments_count ?? 0} shipments</p>
                                    </div>
                                </td>
                                <td className="px-5 py-4">
                                    <StatusBadge status={method.is_active ? 'active' : 'inactive'} />
                                </td>
                                <td className="px-5 py-4">
                                    <Button variant="outline" size="sm" asChild>
                                        <Link href={`/admin/shipping/methods/${method.id}`}>View method</Link>
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
