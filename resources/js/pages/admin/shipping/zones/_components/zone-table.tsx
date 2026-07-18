import { Link } from '@inertiajs/react';
import { EmptyState } from '@/components/shared/empty-state/empty-state';
import { StatusBadge } from '@/components/shared/status-badge/status-badge';
import { Button } from '@/components/ui/button';
import type { AdminShippingZone } from '@/types/admin/shipping';

export function ZoneTable({ zones }: { zones: AdminShippingZone[] }) {
    if (zones.length === 0) {
        return (
            <div className="rounded-3xl border border-dashed border-border/70 bg-muted/30 px-6 py-14 text-center">
                <EmptyState
                    title="No shipping zones found"
                    description="Create a zone when you need routing logic beyond a single flat shipping territory."
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
                                Zone
                            </th>
                            <th className="px-5 py-4 text-left font-medium text-muted-foreground">
                                Coverage
                            </th>
                            <th className="px-5 py-4 text-left font-medium text-muted-foreground">
                                Volume
                            </th>
                            <th className="px-5 py-4 text-left font-medium text-muted-foreground">
                                Status
                            </th>
                            <th className="px-5 py-4 text-left font-medium text-muted-foreground">
                                Action
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        {zones.map((zone) => (
                            <tr
                                key={zone.id}
                                className="border-t border-border/60 align-top"
                            >
                                <td className="px-5 py-4">
                                    <div className="space-y-1">
                                        <Link
                                            href={`/admin/shipping/zones/${zone.id}`}
                                            className="font-semibold transition hover:text-primary"
                                        >
                                            {zone.name}
                                        </Link>
                                        <p className="font-mono text-xs text-muted-foreground">
                                            {zone.code}
                                        </p>
                                    </div>
                                </td>
                                <td className="px-5 py-4 text-muted-foreground">
                                    <div className="space-y-1 text-xs">
                                        <p>Country: {zone.country_code}</p>
                                        <p>
                                            {zone.description ??
                                                'No description'}
                                        </p>
                                    </div>
                                </td>
                                <td className="px-5 py-4 text-muted-foreground">
                                    <div className="space-y-1 text-xs">
                                        <p>{zone.areas_count} areas</p>
                                        <p>
                                            {zone.shipping_methods_count}{' '}
                                            methods
                                        </p>
                                        <p>{zone.orders_count} orders</p>
                                    </div>
                                </td>
                                <td className="px-5 py-4">
                                    <StatusBadge
                                        status={
                                            zone.is_active
                                                ? 'active'
                                                : 'inactive'
                                        }
                                    />
                                </td>
                                <td className="px-5 py-4">
                                    <Button variant="outline" size="sm" asChild>
                                        <Link
                                            href={`/admin/shipping/zones/${zone.id}`}
                                        >
                                            View zone
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
