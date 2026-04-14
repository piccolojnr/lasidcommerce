import { Link, router } from '@inertiajs/react';
import { PageHeader } from '@/components/shared/page-header/page-header';
import { StatusBadge } from '@/components/shared/status-badge/status-badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { AdminLayout } from '@/layouts/app/admin-layout';
import { formatDate } from '@/lib/formatters/date';
import { formatMoney } from '@/lib/formatters/money';
import type { AdminShippingMethodDetail } from '@/types/admin/shipping';

export default function ShippingMethodShowPage({ method }: { method: AdminShippingMethodDetail }) {
    return (
        <AdminLayout title="Shipping Method">
            <div className="mx-auto w-full max-w-6xl space-y-8">
                <PageHeader
                    title={method.name}
                    description={`Shipping method for ${method.zone.name}.`}
                    actions={
                        <div className="flex items-center gap-2">
                            <Button variant="outline" asChild><Link href={`/admin/shipping/zones/${method.zone.id}`}>Back to zone</Link></Button>
                            <Button variant="outline" asChild><Link href={`/admin/shipping/methods/${method.id}/edit`}>Edit method</Link></Button>
                            <Button
                                variant="outline"
                                className="text-destructive hover:text-destructive"
                                onClick={() => {
                                    if (window.confirm(`Delete method "${method.name}"?`)) {
                                        router.delete(`/admin/shipping/methods/${method.id}`);
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
                                <p className="text-xs font-semibold uppercase tracking-[0.24em] text-muted-foreground">Method profile</p>
                                <StatusBadge status={method.is_active ? 'active' : 'inactive'} />
                            </div>
                            <CardTitle className="text-2xl">{method.name}</CardTitle>
                            <p className="max-w-2xl text-sm leading-6 text-muted-foreground">{method.description ?? 'No method description recorded.'}</p>
                        </CardHeader>
                        <CardContent className="grid gap-4 border-t border-border/70 pt-6 md:grid-cols-4">
                            <div><p className="text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">Code</p><p className="mt-2 font-mono font-semibold">{method.code}</p></div>
                            <div><p className="text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">Method type</p><p className="mt-2 font-semibold">{method.method_type}</p></div>
                            <div><p className="text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">Price type</p><p className="mt-2 font-semibold">{method.price_type}</p></div>
                            <div><p className="text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">Zone</p><p className="mt-2 font-semibold">{method.zone.name}</p></div>
                        </CardContent>
                    </Card>
                    <Card className="border-border/70 bg-primary/5">
                        <CardHeader><CardTitle className="text-xl">Commercial settings</CardTitle></CardHeader>
                        <CardContent className="space-y-3 text-sm">
                            <div className="flex items-center justify-between"><span className="text-muted-foreground">Flat rate</span><span className="font-medium">{method.flat_rate_amount !== null ? formatMoney(method.flat_rate_amount) : 'N/A'}</span></div>
                            <div className="flex items-center justify-between"><span className="text-muted-foreground">Min days</span><span className="font-medium">{method.min_delivery_days ?? 'N/A'}</span></div>
                            <div className="flex items-center justify-between"><span className="text-muted-foreground">Max days</span><span className="font-medium">{method.max_delivery_days ?? 'N/A'}</span></div>
                        </CardContent>
                    </Card>
                </div>
                <Card className="overflow-hidden border-border/70 pt-0">
                    <CardHeader className="border-b border-border/70 bg-muted/30 py-6">
                        <CardTitle>Timeline</CardTitle>
                    </CardHeader>
                    <CardContent className="grid gap-4 p-6 md:grid-cols-2">
                        <div className="rounded-2xl border border-border/70 bg-background/80 p-4">
                            <p className="text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">Created</p>
                            <p className="mt-2 font-medium">{formatDate(method.created_at)}</p>
                        </div>
                        <div className="rounded-2xl border border-border/70 bg-background/80 p-4">
                            <p className="text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">Updated</p>
                            <p className="mt-2 font-medium">{formatDate(method.updated_at)}</p>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AdminLayout>
    );
}
