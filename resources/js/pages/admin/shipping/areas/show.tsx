import { Link, router } from '@inertiajs/react';
import { PageHeader } from '@/components/shared/page-header/page-header';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { AdminLayout } from '@/layouts/app/admin-layout';
import { formatDate } from '@/lib/formatters/date';
import type { AdminShippingZoneAreaDetail } from '@/types/admin/shipping';

export default function ShippingAreaShowPage({ area }: { area: AdminShippingZoneAreaDetail }) {
    return (
        <AdminLayout title="Zone Area">
            <div className="mx-auto w-full max-w-5xl space-y-8">
                <PageHeader
                    title={area.area_name}
                    description={`Area rule for ${area.zone.name}.`}
                    actions={
                        <div className="flex items-center gap-2">
                            <Button variant="outline" asChild><Link href={`/admin/shipping/zones/${area.zone.id}`}>Back to zone</Link></Button>
                            <Button variant="outline" asChild><Link href={`/admin/shipping/areas/${area.id}/edit`}>Edit area</Link></Button>
                            <Button
                                variant="outline"
                                className="text-destructive hover:text-destructive"
                                onClick={() => {
                                    if (window.confirm(`Delete area "${area.area_name}"?`)) {
                                        router.delete(`/admin/shipping/areas/${area.id}`);
                                    }
                                }}
                            >
                                Delete
                            </Button>
                        </div>
                    }
                />
                <Card className="overflow-hidden border-border/70 pt-0">
                    <CardHeader className="border-b border-border/70 bg-muted/30 py-6">
                        <CardTitle>Area details</CardTitle>
                    </CardHeader>
                    <CardContent className="grid gap-4 p-6 md:grid-cols-2 text-sm">
                        <div className="rounded-2xl border border-border/70 bg-background/80 p-4">
                            <p className="text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">Zone</p>
                            <p className="mt-2 font-medium">{area.zone.name}</p>
                            <p className="font-mono text-xs text-muted-foreground">{area.zone.code}</p>
                        </div>
                        <div className="rounded-2xl border border-border/70 bg-background/80 p-4">
                            <p className="text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">Rule</p>
                            <p className="mt-2 font-medium">{area.area_name}</p>
                            <p className="text-xs uppercase tracking-[0.18em] text-muted-foreground">{area.area_type}</p>
                        </div>
                        <div className="rounded-2xl border border-border/70 bg-background/80 p-4">
                            <p className="text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">Created</p>
                            <p className="mt-2 font-medium">{formatDate(area.created_at)}</p>
                        </div>
                        <div className="rounded-2xl border border-border/70 bg-background/80 p-4">
                            <p className="text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">Updated</p>
                            <p className="mt-2 font-medium">{formatDate(area.updated_at)}</p>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AdminLayout>
    );
}
