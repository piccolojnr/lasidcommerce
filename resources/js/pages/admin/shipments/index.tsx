import { Link } from '@inertiajs/react';
import * as ShipmentController from '@/actions/App/Http/Controllers/Admin/Shipments/ShipmentController';
import { PageHeader } from '@/components/shared/page-header/page-header';
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

    return (
        <AdminLayout title="Shipments" description="Track outbound delivery activity and status changes.">
            <div className="mx-auto w-full max-w-6xl space-y-6">
                <PageHeader
                    title="Shipments"
                    description="Manage parcel progress, carriers, and delivery state."
                    actions={
                        <Link
                            href={ShipmentController.index.url()}
                            className="text-sm text-muted-foreground hover:text-foreground"
                        >
                            Reset filters
                        </Link>
                    }
                />
                <div className="flex flex-wrap items-center gap-3">
                    <Input
                        placeholder="Search tracking, carrier, order number, or email…"
                        value={search}
                        onChange={(event) => setSearch(event.target.value)}
                        className="w-80"
                    />
                    <Select
                        value={filters.status ?? EMPTY_SENTINEL}
                        onValueChange={(value) =>
                            setFilter('status', value === EMPTY_SENTINEL ? null : value)
                        }
                    >
                        <SelectTrigger className="w-44">
                            <SelectValue placeholder="All shipment states" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value={EMPTY_SENTINEL}>All shipment states</SelectItem>
                            {['pending', 'packed', 'shipped', 'in_transit', 'delivered', 'failed', 'returned', 'cancelled'].map((status) => (
                                <SelectItem key={status} value={status}>
                                    {status.replace(/\b\w/g, (character) =>
                                        character.toUpperCase(),
                                    )}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
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
