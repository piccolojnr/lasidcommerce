import { Link } from '@inertiajs/react';
import { EmptyState } from '@/components/shared/empty-state/empty-state';
import { StatusBadge } from '@/components/shared/status-badge/status-badge';
import { Button } from '@/components/ui/button';
import type { AdminWarehouse } from '@/types/admin/shipping';

export function WarehouseTable({ warehouses }: { warehouses: AdminWarehouse[] }) {
    if (warehouses.length === 0) {
        return (
            <div className="rounded-3xl border border-dashed border-border/70 bg-muted/30 px-6 py-14 text-center">
                <EmptyState title="No warehouses found" description="Create a warehouse location when fulfillment should be routed from a managed origin." />
            </div>
        );
    }

    return (
        <div className="overflow-hidden rounded-3xl border border-border/70 bg-background shadow-sm">
            <div className="overflow-x-auto">
                <table className="min-w-full text-sm">
                    <thead className="bg-muted/35">
                        <tr>
                            <th className="px-5 py-4 text-left font-medium text-muted-foreground">Warehouse</th>
                            <th className="px-5 py-4 text-left font-medium text-muted-foreground">Location</th>
                            <th className="px-5 py-4 text-left font-medium text-muted-foreground">Operational state</th>
                            <th className="px-5 py-4 text-left font-medium text-muted-foreground">Shipments</th>
                            <th className="px-5 py-4 text-left font-medium text-muted-foreground">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        {warehouses.map((warehouse) => (
                            <tr key={warehouse.id} className="border-t border-border/60 align-top">
                                <td className="px-5 py-4">
                                    <div className="space-y-1">
                                        <Link href={`/admin/shipping/warehouse-locations/${warehouse.id}`} className="font-semibold transition hover:text-primary">{warehouse.name}</Link>
                                        <p className="font-mono text-xs text-muted-foreground">{warehouse.code}</p>
                                    </div>
                                </td>
                                <td className="px-5 py-4 text-muted-foreground">
                                    <div className="space-y-1 text-xs">
                                        <p>{warehouse.city}{warehouse.region ? `, ${warehouse.region}` : ''}</p>
                                        <p>{warehouse.country}</p>
                                    </div>
                                </td>
                                <td className="px-5 py-4">
                                    <div className="space-y-2">
                                        <StatusBadge status={warehouse.is_active ? 'active' : 'inactive'} />
                                        <p className="text-xs text-muted-foreground">{warehouse.is_default ? 'Default warehouse' : 'Standard warehouse'}</p>
                                    </div>
                                </td>
                                <td className="px-5 py-4 text-muted-foreground">{warehouse.shipments_count}</td>
                                <td className="px-5 py-4"><Button variant="outline" size="sm" asChild><Link href={`/admin/shipping/warehouse-locations/${warehouse.id}`}>View warehouse</Link></Button></td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </div>
    );
}
