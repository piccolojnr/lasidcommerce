import { Link } from '@inertiajs/react';
import * as StockItemController from '@/actions/App/Http/Controllers/Admin/Inventory/StockItemController';
import * as StockMovementController from '@/actions/App/Http/Controllers/Admin/Inventory/StockMovementController';
import { PageHeader } from '@/components/shared/page-header/page-header';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { AdminLayout } from '@/layouts/app/admin-layout';
import { formatDate } from '@/lib/formatters/date';
import { formatStatus } from '@/lib/formatters/status';
import type { AdminStockMovementDetail } from '@/types/admin/inventory';

interface Props {
    stockMovement: AdminStockMovementDetail;
}

export default function StockMovementShowPage({ stockMovement }: Props) {
    return (
        <AdminLayout title="Stock Movement Details" description="Inspect ledger metadata for a single inventory movement.">
            <div className="mx-auto w-full max-w-5xl space-y-8">
                <PageHeader
                    title={formatStatus(stockMovement.type)}
                    description={`Recorded ${formatDate(stockMovement.created_at)}.`}
                    actions={
                        <div className="flex items-center gap-2">
                            <Button variant="outline" asChild><Link href={StockMovementController.index.url()}>Back to ledger</Link></Button>
                            <Button asChild><Link href={StockItemController.show.url(stockMovement.stock_item_id)}>View stock item</Link></Button>
                        </div>
                    }
                />

                <div className="grid gap-4 md:grid-cols-3">
                    <Card className="border-border/70"><CardHeader className="pb-2"><CardTitle className="text-sm font-medium text-muted-foreground">Quantity recorded</CardTitle></CardHeader><CardContent><div className="text-3xl font-semibold">{stockMovement.quantity}</div><p className="text-sm text-muted-foreground">Absolute quantity stored on the ledger entry.</p></CardContent></Card>
                    <Card className="border-border/70"><CardHeader className="pb-2"><CardTitle className="text-sm font-medium text-muted-foreground">Effective stock delta</CardTitle></CardHeader><CardContent><div className={`text-3xl font-semibold ${stockMovement.stock_delta >= 0 ? 'text-emerald-600' : 'text-destructive'}`}>{stockMovement.stock_delta >= 0 ? '+' : ''}{stockMovement.stock_delta}</div><p className="text-sm text-muted-foreground">Signed effect applied to quantity on hand.</p></CardContent></Card>
                    <Card className="border-border/70"><CardHeader className="pb-2"><CardTitle className="text-sm font-medium text-muted-foreground">Actor</CardTitle></CardHeader><CardContent><div className="text-3xl font-semibold">{stockMovement.creator_name ?? 'System'}</div><p className="text-sm text-muted-foreground">User recorded against this ledger entry.</p></CardContent></Card>
                </div>

                <div className="grid gap-6 lg:grid-cols-2">
                    <Card className="overflow-hidden border-border/70 pt-0">
                        <CardHeader className="border-b border-border/70 bg-muted/30 py-6"><CardTitle>Movement context</CardTitle></CardHeader>
                        <CardContent className="space-y-4 p-6 text-sm">
                            <div className="flex items-center justify-between"><span className="text-muted-foreground">Type</span><span className="font-medium">{formatStatus(stockMovement.type)}</span></div>
                            <div className="flex items-center justify-between"><span className="text-muted-foreground">Stock item</span><span className="font-medium">#{stockMovement.stock_item_id}</span></div>
                            <div className="flex items-center justify-between"><span className="text-muted-foreground">Reference</span><span className="font-medium">{stockMovement.reference_type && stockMovement.reference_id ? `${stockMovement.reference_type} #${stockMovement.reference_id}` : 'No reference'}</span></div>
                            <div className="flex items-center justify-between"><span className="text-muted-foreground">Created</span><span className="font-medium">{formatDate(stockMovement.created_at)}</span></div>
                        </CardContent>
                    </Card>

                    <Card className="overflow-hidden border-border/70 pt-0">
                        <CardHeader className="border-b border-border/70 bg-muted/30 py-6"><CardTitle>Catalog identity</CardTitle></CardHeader>
                        <CardContent className="space-y-4 p-6 text-sm">
                            <div><p className="text-muted-foreground">Product</p><p className="font-medium">{stockMovement.product_name ?? 'Unknown product'}</p></div>
                            <div><p className="text-muted-foreground">Product SKU</p><p className="font-mono text-xs">{stockMovement.product_sku ?? 'N/A'}</p></div>
                            <div><p className="text-muted-foreground">Variant</p><p className="font-medium">{stockMovement.variant_name ?? 'Base product'}</p></div>
                            {stockMovement.variant_sku ? <div><p className="text-muted-foreground">Variant SKU</p><p className="font-mono text-xs">{stockMovement.variant_sku}</p></div> : null}
                        </CardContent>
                    </Card>
                </div>

                <Card className="overflow-hidden border-border/70 pt-0">
                    <CardHeader className="border-b border-border/70 bg-muted/30 py-6"><CardTitle>Notes</CardTitle></CardHeader>
                    <CardContent className="p-6 text-sm text-muted-foreground whitespace-pre-wrap">
                        {stockMovement.note ?? 'No note recorded for this ledger entry.'}
                    </CardContent>
                </Card>
            </div>
        </AdminLayout>
    );
}
