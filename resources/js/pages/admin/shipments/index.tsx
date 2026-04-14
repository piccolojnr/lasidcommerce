import { Link } from '@inertiajs/react';
import * as ShipmentController from '@/actions/App/Http/Controllers/Admin/Shipments/ShipmentController';
import { PageHeader } from '@/components/shared/page-header/page-header';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import {
    EMPTY_SENTINEL,
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useFilters } from '@/hooks/use-filters';
import { AdminLayout } from '@/layouts/app/admin-layout';
import { ShipmentTable } from '@/pages/admin/shipments/_components/shipment-table';
import type { AdminShipmentListPage } from '@/types/admin/shipment';
import type { PaginationLink } from '@/types/shared/pagination';

interface ShipmentFilters {
    [key: string]: string | null;
    search: string | null;
    status: string | null;
}

interface Props {
    shipments: AdminShipmentListPage;
    filters: ShipmentFilters;
}

export default function ShipmentIndexPage({ shipments, filters }: Props) {
    const { search, setSearch, setFilter } = useFilters(
        ShipmentController.index.url(),
        filters,
    );
    const delivered = shipments.data.filter(
        (shipment) => shipment.status === 'delivered',
    ).length;
    const activeFlow = shipments.data.filter(
        (shipment) =>
            !['delivered', 'returned', 'failed', 'cancelled'].includes(
                shipment.status,
            ),
    ).length;

    return (
        <AdminLayout title="Shipments" description="Track outbound delivery activity and status changes.">
            <div className="mx-auto w-full max-w-7xl space-y-8">
                <PageHeader
                    title="Shipments"
                    description="Manage parcel movement, carrier context, and delivery risk from one queue."
                    actions={
                        <Link
                            href={ShipmentController.index.url()}
                            className="text-sm text-muted-foreground transition hover:text-foreground"
                        >
                            Reset filters
                        </Link>
                    }
                />

                <div className="grid gap-4 md:grid-cols-3">
                    <Card className="border-border/70">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">Visible in this result</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-3xl font-semibold">{shipments.data.length}</div>
                            <p className="text-sm text-muted-foreground">Shipments on the current page after filters.</p>
                        </CardContent>
                    </Card>
                    <Card className="border-border/70">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">In active flow</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-3xl font-semibold">{activeFlow}</div>
                            <p className="text-sm text-muted-foreground">Shipments still moving through packing or transit.</p>
                        </CardContent>
                    </Card>
                    <Card className="border-border/70">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">Delivered in this slice</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-3xl font-semibold">{delivered}</div>
                            <p className="text-sm text-muted-foreground">Shipments already closed successfully.</p>
                        </CardContent>
                    </Card>
                </div>

                <div className="rounded-[2rem] border border-border/70 bg-muted/25 p-5">
                    <p className="text-xs font-semibold uppercase tracking-[0.24em] text-muted-foreground">
                        Delivery filter
                    </p>
                    <p className="mt-1 text-sm text-muted-foreground">
                        Search by tracking, carrier, order number, or customer, then narrow by shipment state.
                    </p>
                    <div className="mt-4 flex flex-wrap items-center gap-3">
                        <Input
                            placeholder="Search tracking, carrier, order number, or email..."
                            value={search}
                            onChange={(event) => setSearch(event.target.value)}
                            className="h-11 w-80 bg-background"
                        />
                        <Select
                            value={filters.status ?? EMPTY_SENTINEL}
                            onValueChange={(value) =>
                                setFilter('status', value === EMPTY_SENTINEL ? null : value)
                            }
                        >
                            <SelectTrigger className="h-11 w-48 bg-background">
                                <SelectValue placeholder="All shipment states" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value={EMPTY_SENTINEL}>All shipment states</SelectItem>
                                {['pending', 'packed', 'shipped', 'in_transit', 'delivered', 'failed', 'returned', 'cancelled'].map((status) => (
                                    <SelectItem key={status} value={status}>
                                        {status.replace(/\b\w/g, (character) => character.toUpperCase())}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>
                </div>

                <ShipmentTable shipments={shipments.data} />

                {shipments.last_page > 1 && shipments.links && (
                    <div className="flex items-center justify-center gap-1">
                        {shipments.links.map((link: PaginationLink, index: number) =>
                            link.url ? (
                                <Link
                                    key={index}
                                    href={link.url}
                                    className={`rounded border px-3 py-1 text-sm ${
                                        link.active
                                            ? 'bg-primary text-primary-foreground'
                                            : 'hover:bg-muted'
                                    }`}
                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                />
                            ) : (
                                <span
                                    key={index}
                                    className="rounded border px-3 py-1 text-sm opacity-40"
                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                />
                            ),
                        )}
                    </div>
                )}
            </div>
        </AdminLayout>
    );
}
