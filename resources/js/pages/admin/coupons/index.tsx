import { Link } from '@inertiajs/react';
import * as CouponController from '@/actions/App/Http/Controllers/Admin/Coupons/CouponController';
import { PageHeader } from '@/components/shared/page-header/page-header';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { EMPTY_SENTINEL, Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
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
    const { search, setSearch, setFilter } = useFilters(CouponController.index.url(), filters);
    const activeValue = filters.is_active === true ? '1' : filters.is_active === false ? '0' : EMPTY_SENTINEL;

    return (
        <AdminLayout title="Coupons">
            <div className="mx-auto w-full max-w-6xl space-y-6">
                <PageHeader
                    title="Coupons"
                    description="Manage discount campaigns, redemption windows, and usage caps."
                    actions={
                        <Button asChild>
                            <Link href={CouponController.create.url()}>Create coupon</Link>
                        </Button>
                    }
                />
                <div className="flex flex-wrap items-center gap-3">
                    <Input
                        placeholder="Search coupons…"
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        className="w-64"
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
                        <SelectTrigger className="w-40"><SelectValue placeholder="All" /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value={EMPTY_SENTINEL}>All</SelectItem>
                            <SelectItem value="1">Active</SelectItem>
                            <SelectItem value="0">Inactive</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <CouponTable coupons={coupons.data} />
                {coupons.last_page > 1 && (
                    <div className="flex items-center justify-center gap-1">
                        {coupons.links.map((link: PaginationLink, index: number) =>
                            link.url ? (
                                <Link
                                    key={index}
                                    href={link.url}
                                    className={`rounded border px-3 py-1 text-sm ${
                                        link.active ? 'bg-primary text-primary-foreground' : 'hover:bg-muted'
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
