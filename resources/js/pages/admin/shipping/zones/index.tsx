import { Link } from '@inertiajs/react';
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
import { adminRoutes } from '@/lib/routes';
import { ZoneTable } from '@/pages/admin/shipping/zones/_components/zone-table';
import type {
    AdminShippingListPage,
    AdminShippingZone,
} from '@/types/admin/shipping';
import type { PaginationLink } from '@/types/shared/pagination';

interface Filters {
    [key: string]: string | boolean | null;
    search: string | null;
    is_active: boolean | null;
}

export default function ShippingZoneIndexPage({
    zones,
    filters,
}: {
    zones: AdminShippingListPage<AdminShippingZone>;
    filters: Filters;
}) {
    const { search, setSearch, setFilter } = useFilters(
        adminRoutes.shipping.zones,
        filters,
    );
    const activeValue =
        filters.is_active === true
            ? '1'
            : filters.is_active === false
              ? '0'
              : EMPTY_SENTINEL;
    const activeZones = zones.data.filter((zone) => zone.is_active).length;
    const totalAreas = zones.data.reduce(
        (sum, zone) => sum + zone.areas_count,
        0,
    );

    return (
        <AdminLayout title="Shipping Zones">
            <div className="mx-auto w-full max-w-7xl space-y-8">
                <PageHeader
                    title="Shipping zones"
                    description="Manage the territories used to resolve shipping coverage and method availability."
                    actions={
                        <Link
                            href={`${adminRoutes.shipping.zones}/create`}
                            className="inline-flex h-10 items-center rounded-md bg-primary px-4 text-sm font-medium text-primary-foreground"
                        >
                            Create zone
                        </Link>
                    }
                />
                <div className="grid gap-4 md:grid-cols-3">
                    <Card className="border-border/70">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                Visible in this result
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-3xl font-semibold">
                                {zones.data.length}
                            </div>
                            <p className="text-sm text-muted-foreground">
                                Zones on the current page after filters.
                            </p>
                        </CardContent>
                    </Card>
                    <Card className="border-border/70">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                Active zones
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-3xl font-semibold">
                                {activeZones}
                            </div>
                            <p className="text-sm text-muted-foreground">
                                Zones currently eligible for resolution logic.
                            </p>
                        </CardContent>
                    </Card>
                    <Card className="border-border/70">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                Areas in this slice
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-3xl font-semibold">
                                {totalAreas}
                            </div>
                            <p className="text-sm text-muted-foreground">
                                Nested area rules attached to the current page.
                            </p>
                        </CardContent>
                    </Card>
                </div>
                <div className="rounded-[2rem] border border-border/70 bg-muted/25 p-5">
                    <div className="mt-4 flex flex-wrap items-center gap-3">
                        <Input
                            placeholder="Search name, code, or country..."
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            className="h-11 w-72 bg-background"
                        />
                        <Select
                            value={activeValue}
                            onValueChange={(value) =>
                                setFilter(
                                    'is_active',
                                    value === EMPTY_SENTINEL ? null : value,
                                )
                            }
                        >
                            <SelectTrigger className="h-11 w-44 bg-background">
                                <SelectValue placeholder="All statuses" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value={EMPTY_SENTINEL}>
                                    All statuses
                                </SelectItem>
                                <SelectItem value="1">Active</SelectItem>
                                <SelectItem value="0">Inactive</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>
                <ZoneTable zones={zones.data} />
                {zones.last_page > 1 && (
                    <div className="flex items-center justify-center gap-1">
                        {zones.links.map(
                            (link: PaginationLink, index: number) =>
                                link.url ? (
                                    <Link
                                        key={index}
                                        href={link.url}
                                        className={`rounded border px-3 py-1 text-sm ${link.active ? 'bg-primary text-primary-foreground' : 'hover:bg-muted'}`}
                                        dangerouslySetInnerHTML={{
                                            __html: link.label,
                                        }}
                                    />
                                ) : (
                                    <span
                                        key={index}
                                        className="rounded border px-3 py-1 text-sm opacity-40"
                                        dangerouslySetInnerHTML={{
                                            __html: link.label,
                                        }}
                                    />
                                ),
                        )}
                    </div>
                )}
            </div>
        </AdminLayout>
    );
}
