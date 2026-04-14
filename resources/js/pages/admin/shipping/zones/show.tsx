import { Link, router } from '@inertiajs/react';
import { PageHeader } from '@/components/shared/page-header/page-header';
import { StatusBadge } from '@/components/shared/status-badge/status-badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { AdminLayout } from '@/layouts/app/admin-layout';
import { formatMoney } from '@/lib/formatters/money';
import type { AdminShippingZoneDetail } from '@/types/admin/shipping';

export default function ShippingZoneShowPage({ zone }: { zone: AdminShippingZoneDetail }) {
    return (
        <AdminLayout title="Shipping Zone">
            <div className="mx-auto w-full max-w-7xl space-y-8">
                <PageHeader
                    title={zone.name}
                    description="Review coverage rules, nested areas, and linked shipping methods for this zone."
                    actions={
                        <div className="flex items-center gap-2">
                            <Button variant="outline" asChild><Link href="/admin/shipping/zones">Back to zones</Link></Button>
                            <Button variant="outline" asChild><Link href={`/admin/shipping/zones/${zone.id}/edit`}>Edit zone</Link></Button>
                            <Button
                                variant="outline"
                                className="text-destructive hover:text-destructive"
                                onClick={() => {
                                    if (window.confirm(`Delete zone "${zone.name}"?`)) {
                                        router.delete(`/admin/shipping/zones/${zone.id}`);
                                    }
                                }}
                            >
                                Delete
                            </Button>
                        </div>
                    }
                />
                <div className="grid gap-4 lg:grid-cols-3">
                    <Card className="border-border/70 bg-muted/30 lg:col-span-2">
                        <CardHeader className="space-y-3">
                            <div className="flex items-center justify-between gap-3">
                                <p className="text-xs font-semibold uppercase tracking-[0.24em] text-muted-foreground">Zone profile</p>
                                <StatusBadge status={zone.is_active ? 'active' : 'inactive'} />
                            </div>
                            <CardTitle className="text-2xl">{zone.name}</CardTitle>
                            <p className="max-w-2xl text-sm leading-6 text-muted-foreground">{zone.description ?? 'No description recorded for this zone.'}</p>
                        </CardHeader>
                        <CardContent className="grid gap-4 border-t border-border/70 pt-6 md:grid-cols-4">
                            <div><p className="text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">Code</p><p className="mt-2 font-mono font-semibold">{zone.code}</p></div>
                            <div><p className="text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">Country</p><p className="mt-2 font-semibold">{zone.country_code}</p></div>
                            <div><p className="text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">Areas</p><p className="mt-2 font-semibold">{zone.areas_count}</p></div>
                            <div><p className="text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">Methods</p><p className="mt-2 font-semibold">{zone.shipping_methods_count}</p></div>
                        </CardContent>
                    </Card>
                    <Card className="border-border/70 bg-primary/5">
                        <CardHeader><CardTitle className="text-xl">Operational use</CardTitle></CardHeader>
                        <CardContent className="space-y-3 text-sm">
                            <div className="flex items-center justify-between"><span className="text-muted-foreground">Orders linked</span><span className="font-medium">{zone.orders_count}</span></div>
                            <div className="flex items-center justify-between"><span className="text-muted-foreground">Area rules</span><span className="font-medium">{zone.areas.length}</span></div>
                            <div className="flex items-center justify-between"><span className="text-muted-foreground">Method rules</span><span className="font-medium">{zone.shipping_methods.length}</span></div>
                        </CardContent>
                    </Card>
                </div>
                <div className="grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
                    <Card className="overflow-hidden border-border/70 pt-0">
                        <CardHeader className="border-b border-border/70 bg-muted/30 py-6">
                            <div className="flex items-center justify-between gap-3">
                                <CardTitle>Zone areas</CardTitle>
                                <Button size="sm" asChild><Link href={`/admin/shipping/zones/${zone.id}/areas/create`}>Add area</Link></Button>
                            </div>
                        </CardHeader>
                        <CardContent className="space-y-3 p-6">
                            {zone.areas.length > 0 ? zone.areas.map((area) => (
                                <div key={area.id} className="flex items-center justify-between rounded-2xl border border-border/70 bg-background/80 p-4 text-sm">
                                    <div>
                                        <Link href={`/admin/shipping/areas/${area.id}`} className="font-medium transition hover:text-primary">{area.area_name}</Link>
                                        <p className="text-xs uppercase tracking-[0.18em] text-muted-foreground">{area.area_type}</p>
                                    </div>
                                    <Button variant="outline" size="sm" asChild><Link href={`/admin/shipping/areas/${area.id}/edit`}>Edit</Link></Button>
                                </div>
                            )) : <p className="text-sm text-muted-foreground">No area rules defined yet.</p>}
                        </CardContent>
                    </Card>
                    <Card className="overflow-hidden border-border/70 pt-0">
                        <CardHeader className="border-b border-border/70 bg-muted/30 py-6">
                            <div className="flex items-center justify-between gap-3">
                                <CardTitle>Shipping methods</CardTitle>
                                <Button size="sm" asChild><Link href={`/admin/shipping/zones/${zone.id}/methods/create`}>Add method</Link></Button>
                            </div>
                        </CardHeader>
                        <CardContent className="space-y-3 p-6">
                            {zone.shipping_methods.length > 0 ? zone.shipping_methods.map((method) => (
                                <div key={method.id} className="rounded-2xl border border-border/70 bg-background/80 p-4 text-sm">
                                    <div className="flex items-center justify-between gap-3">
                                        <div>
                                            <Link href={`/admin/shipping/methods/${method.id}`} className="font-medium transition hover:text-primary">{method.name}</Link>
                                            <p className="text-xs text-muted-foreground">{method.code} • {method.method_type} • {method.price_type}</p>
                                        </div>
                                        <StatusBadge status={method.is_active ? 'active' : 'inactive'} />
                                    </div>
                                    <p className="mt-2 text-muted-foreground">Price: {method.flat_rate_amount !== null ? formatMoney(method.flat_rate_amount) : 'N/A'}</p>
                                    <div className="mt-3">
                                        <Button variant="outline" size="sm" asChild><Link href={`/admin/shipping/methods/${method.id}/edit`}>Edit</Link></Button>
                                    </div>
                                </div>
                            )) : <p className="text-sm text-muted-foreground">No shipping methods linked yet.</p>}
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AdminLayout>
    );
}
