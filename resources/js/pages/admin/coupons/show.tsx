import { Link } from '@inertiajs/react';
import * as CouponController from '@/actions/App/Http/Controllers/Admin/Coupons/CouponController';
import { PageHeader } from '@/components/shared/page-header/page-header';
import { StatusBadge } from '@/components/shared/status-badge/status-badge';
import { Button } from '@/components/ui/button';
import { AdminLayout } from '@/layouts/app/admin-layout';
import { formatDate } from '@/lib/formatters/date';
import { formatMoney } from '@/lib/formatters/money';
import type { AdminCouponDetail } from '@/types/admin/coupon';

interface Props {
    coupon: AdminCouponDetail;
}

export default function CouponShowPage({ coupon }: Props) {
    return (
        <AdminLayout title="Coupon">
            <div className="mx-auto w-full max-w-5xl space-y-6">
                <PageHeader
                    title={coupon.code}
                    description="Review coupon settings, validity, and usage before making changes."
                    actions={
                        <div className="flex items-center gap-2">
                            <Button variant="outline" asChild>
                                <Link href={CouponController.index.url()}>Back to list</Link>
                            </Button>
                            <Button asChild>
                                <Link href={CouponController.edit.url(coupon)}>Edit coupon</Link>
                            </Button>
                        </div>
                    }
                />

                <div className="grid gap-4 md:grid-cols-3">
                    <section className="rounded-lg border bg-background p-5">
                        <p className="text-sm text-muted-foreground">Status</p>
                        <div className="mt-2 flex items-center gap-2">
                            <StatusBadge status={coupon.is_active ? 'active' : 'inactive'} />
                            <span className="text-sm text-muted-foreground">
                                {coupon.is_currently_valid ? 'Currently valid' : 'Not currently valid'}
                            </span>
                        </div>
                    </section>
                    <section className="rounded-lg border bg-background p-5">
                        <p className="text-sm text-muted-foreground">Discount value</p>
                        <p className="mt-2 text-2xl font-semibold">{formatMoney(coupon.value)}</p>
                        <p className="text-sm capitalize text-muted-foreground">{coupon.type}</p>
                    </section>
                    <section className="rounded-lg border bg-background p-5">
                        <p className="text-sm text-muted-foreground">Usage</p>
                        <p className="mt-2 text-2xl font-semibold">
                            {coupon.used_count}
                            {coupon.usage_limit !== null ? ` / ${coupon.usage_limit}` : ''}
                        </p>
                        <p className="text-sm text-muted-foreground">Redemptions used</p>
                    </section>
                </div>

                <div className="grid gap-6 lg:grid-cols-[minmax(0,2fr)_minmax(320px,1fr)]">
                    <section className="rounded-lg border bg-background p-5">
                        <h2 className="text-base font-semibold">Eligibility</h2>
                        <dl className="mt-4 grid gap-4 md:grid-cols-2">
                            <div>
                                <dt className="text-sm text-muted-foreground">Minimum order amount</dt>
                                <dd className="mt-1 font-medium">
                                    {coupon.minimum_order_amount !== null ? formatMoney(coupon.minimum_order_amount) : 'None'}
                                </dd>
                            </div>
                            <div>
                                <dt className="text-sm text-muted-foreground">Maximum discount</dt>
                                <dd className="mt-1 font-medium">
                                    {coupon.maximum_discount_amount !== null ? formatMoney(coupon.maximum_discount_amount) : 'None'}
                                </dd>
                            </div>
                        </dl>
                    </section>

                    <section className="rounded-lg border bg-background p-5">
                        <h2 className="text-base font-semibold">Schedule</h2>
                        <dl className="mt-4 space-y-4">
                            <div>
                                <dt className="text-sm text-muted-foreground">Starts at</dt>
                                <dd className="mt-1 font-medium">{formatDate(coupon.starts_at)}</dd>
                            </div>
                            <div>
                                <dt className="text-sm text-muted-foreground">Expires at</dt>
                                <dd className="mt-1 font-medium">{formatDate(coupon.expires_at)}</dd>
                            </div>
                            <div>
                                <dt className="text-sm text-muted-foreground">Created</dt>
                                <dd className="mt-1 font-medium">{formatDate(coupon.created_at)}</dd>
                            </div>
                            <div>
                                <dt className="text-sm text-muted-foreground">Last updated</dt>
                                <dd className="mt-1 font-medium">{formatDate(coupon.updated_at)}</dd>
                            </div>
                        </dl>
                    </section>
                </div>
            </div>
        </AdminLayout>
    );
}
