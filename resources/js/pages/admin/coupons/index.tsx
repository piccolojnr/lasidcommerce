import { Link } from '@inertiajs/react';
import * as CouponController from '@/actions/App/Http/Controllers/Admin/Coupons/CouponController';
import { PageHeader } from '@/components/shared/page-header/page-header';
import { Button } from '@/components/ui/button';
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
import { CouponTable } from '@/pages/admin/coupons/_components/coupon-table';
import type { AdminCouponListPage } from '@/types/admin/coupon';
import type { PaginationLink } from '@/types/shared/pagination';

interface CouponFilters {
    [key: string]: string | boolean | null;
    search: string | null;
    is_active: boolean | null;
}

interface Props {
    coupons: AdminCouponListPage;
    filters: CouponFilters;
}

export default function CouponIndexPage({ coupons, filters }: Props) {
    const { search, setSearch, setFilter } = useFilters(
        CouponController.index.url(),
        filters,
    );
    const activeValue =
        filters.is_active === true
            ? '1'
            : filters.is_active === false
              ? '0'
              : EMPTY_SENTINEL;
    const activeCoupons = coupons.data.filter((coupon) => coupon.is_active).length;
    const validCoupons = coupons.data.filter(
        (coupon) => coupon.is_currently_valid,
    ).length;
    const cappedCoupons = coupons.data.filter(
        (coupon) => coupon.usage_limit !== null,
    ).length;

    return (
        <AdminLayout title="Coupons">
            <div className="mx-auto w-full max-w-7xl space-y-8">
                <PageHeader
                    title="Coupons"
                    description="Manage live promotions, redemption guardrails, and campaign timing without losing the operational picture."
                    actions={
                        <Button asChild>
                            <Link href={CouponController.create.url()}>
                                Create coupon
                            </Link>
                        </Button>
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
                                {coupons.data.length}
                            </div>
                            <p className="text-sm text-muted-foreground">
                                Campaigns on the current page after filters.
                            </p>
                        </CardContent>
                    </Card>

                    <Card className="border-border/70">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                Active and enabled
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-3xl font-semibold">
                                {activeCoupons}
                            </div>
                            <p className="text-sm text-muted-foreground">
                                Coupons still enabled for redemption logic.
                            </p>
                        </CardContent>
                    </Card>

                    <Card className="border-border/70">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                Currently valid
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-3xl font-semibold">
                                {validCoupons}
                            </div>
                            <p className="text-sm text-muted-foreground">
                                Coupons that are active and inside their validity
                                window.
                            </p>
                        </CardContent>
                    </Card>
                </div>

                <div className="rounded-[2rem] border border-border/70 bg-muted/25 p-5">
                    <div className="flex flex-wrap items-end justify-between gap-4">
                        <div className="space-y-1">
                            <p className="text-xs font-semibold uppercase tracking-[0.24em] text-muted-foreground">
                                Campaign filter
                            </p>
                            <p className="text-sm text-muted-foreground">
                                Search by code, then narrow the result set by active
                                state.
                            </p>
                        </div>
                        <div className="rounded-full border border-border/70 bg-background/80 px-3 py-1 text-xs font-medium uppercase tracking-[0.18em] text-muted-foreground">
                            {cappedCoupons} capped campaigns on this page
                        </div>
                    </div>

                    <div className="mt-4 flex flex-wrap items-center gap-3">
                        <Input
                            placeholder="Search coupons..."
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            className="h-11 w-72 bg-background"
                        />
                        <Select
                            value={activeValue}
                            onValueChange={(value) => {
                                if (value === EMPTY_SENTINEL) {
                                    setFilter('is_active', null);
                                } else {
                                    setFilter('is_active', value);
                                }
                            }}
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

                <CouponTable coupons={coupons.data} />

                {coupons.last_page > 1 && (
                    <div className="flex items-center justify-center gap-1">
                        {coupons.links.map(
                            (link: PaginationLink, index: number) =>
                                link.url ? (
                                    <Link
                                        key={index}
                                        href={link.url}
                                        className={`rounded border px-3 py-1 text-sm ${
                                            link.active
                                                ? 'bg-primary text-primary-foreground'
                                                : 'hover:bg-muted'
                                        }`}
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
