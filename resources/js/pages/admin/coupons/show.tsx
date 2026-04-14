import { Link } from '@inertiajs/react';
import * as CouponController from '@/actions/App/Http/Controllers/Admin/Coupons/CouponController';
import { PageHeader } from '@/components/shared/page-header/page-header';
import { StatusBadge } from '@/components/shared/status-badge/status-badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { AdminLayout } from '@/layouts/app/admin-layout';
import { formatDate } from '@/lib/formatters/date';
import { formatMoney } from '@/lib/formatters/money';
import type { AdminCouponDetail } from '@/types/admin/coupon';

interface Props {
    coupon: AdminCouponDetail;
}

function formatCouponValue(coupon: AdminCouponDetail): string {
    return coupon.type === 'percentage'
        ? `${coupon.value}%`
        : formatMoney(coupon.value);
}

export default function CouponShowPage({ coupon }: Props) {
    const remainingRedemptions =
        coupon.usage_limit !== null
            ? Math.max(coupon.usage_limit - coupon.used_count, 0)
            : null;

    return (
        <AdminLayout title="Coupon">
            <div className="mx-auto w-full max-w-7xl space-y-8">
                <PageHeader
                    title={coupon.code}
                    description="Review campaign economics, validity windows, and redemption pressure before you touch the rules."
                    actions={
                        <div className="flex items-center gap-2">
                            <Button variant="outline" asChild>
                                <Link href={CouponController.index.url()}>
                                    Back to list
                                </Link>
                            </Button>
                            <Button asChild>
                                <Link href={CouponController.edit.url(coupon)}>
                                    Edit coupon
                                </Link>
                            </Button>
                        </div>
                    }
                />

                <div className="grid gap-4 lg:grid-cols-3">
                    <Card className="border-border/70 bg-muted/30 lg:col-span-2">
                        <CardHeader className="space-y-3">
                            <div className="flex items-center justify-between gap-3">
                                <p className="text-xs font-semibold uppercase tracking-[0.24em] text-muted-foreground">
                                    Campaign profile
                                </p>
                                <StatusBadge
                                    status={
                                        coupon.is_active ? 'active' : 'inactive'
                                    }
                                />
                            </div>
                            <div className="space-y-2">
                                <CardTitle className="font-mono text-2xl uppercase">
                                    {coupon.code}
                                </CardTitle>
                                <p className="max-w-2xl text-sm leading-6 text-muted-foreground">
                                    {coupon.is_currently_valid
                                        ? 'This coupon is currently valid and eligible for redemption if cart conditions pass.'
                                        : 'This coupon is outside its live window or disabled, so redemptions should fail cleanly.'}
                                </p>
                            </div>
                        </CardHeader>
                        <CardContent className="grid gap-4 border-t border-border/70 pt-6 md:grid-cols-3">
                            <div className="space-y-1">
                                <p className="text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">
                                    Discount value
                                </p>
                                <p className="text-2xl font-semibold">
                                    {formatCouponValue(coupon)}
                                </p>
                                <p className="text-xs capitalize text-muted-foreground">
                                    {coupon.type === 'percentage'
                                        ? 'Percentage-based promotion'
                                        : 'Fixed-amount promotion'}
                                </p>
                            </div>
                            <div className="space-y-1">
                                <p className="text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">
                                    Used count
                                </p>
                                <p className="text-2xl font-semibold">
                                    {coupon.used_count}
                                </p>
                                <p className="text-xs text-muted-foreground">
                                    Completed redemptions recorded so far
                                </p>
                            </div>
                            <div className="space-y-1">
                                <p className="text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">
                                    Remaining capacity
                                </p>
                                <p className="text-2xl font-semibold">
                                    {remainingRedemptions ?? 'Unlimited'}
                                </p>
                                <p className="text-xs text-muted-foreground">
                                    {coupon.usage_limit !== null
                                        ? `Against a limit of ${coupon.usage_limit}`
                                        : 'No usage cap configured'}
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <Card className="border-border/70 bg-primary/5">
                        <CardHeader className="space-y-2">
                            <p className="text-xs font-semibold uppercase tracking-[0.24em] text-primary">
                                Redemption state
                            </p>
                            <CardTitle className="text-xl">
                                {coupon.is_currently_valid
                                    ? 'Currently redeemable'
                                    : 'Not redeemable now'}
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-3 text-sm">
                            <div className="flex items-center justify-between">
                                <span className="text-muted-foreground">
                                    Active flag
                                </span>
                                <span className="font-medium">
                                    {coupon.is_active ? 'Enabled' : 'Disabled'}
                                </span>
                            </div>
                            <div className="flex items-center justify-between">
                                <span className="text-muted-foreground">
                                    Starts
                                </span>
                                <span className="font-medium">
                                    {coupon.starts_at
                                        ? formatDate(coupon.starts_at)
                                        : 'Immediately'}
                                </span>
                            </div>
                            <div className="flex items-center justify-between">
                                <span className="text-muted-foreground">
                                    Expires
                                </span>
                                <span className="font-medium">
                                    {coupon.expires_at
                                        ? formatDate(coupon.expires_at)
                                        : 'No expiry'}
                                </span>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <div className="grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
                    <div className="space-y-6">
                        <Card className="overflow-hidden border-border/70 pt-0">
                            <CardHeader className="border-b border-border/70 bg-muted/30 py-6">
                                <CardTitle>Discount mechanics</CardTitle>
                            </CardHeader>
                            <CardContent className="grid gap-4 p-6 md:grid-cols-2">
                                <div className="rounded-2xl border border-border/70 bg-background/80 p-4">
                                    <p className="text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">
                                        Base value
                                    </p>
                                    <p className="mt-2 text-2xl font-semibold">
                                        {formatCouponValue(coupon)}
                                    </p>
                                </div>
                                <div className="rounded-2xl border border-border/70 bg-background/80 p-4">
                                    <p className="text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">
                                        Maximum discount
                                    </p>
                                    <p className="mt-2 text-2xl font-semibold">
                                        {coupon.maximum_discount_amount !== null
                                            ? formatMoney(
                                                  coupon.maximum_discount_amount,
                                              )
                                            : 'None'}
                                    </p>
                                </div>
                            </CardContent>
                        </Card>

                        <Card className="overflow-hidden border-border/70 pt-0">
                            <CardHeader className="border-b border-border/70 bg-muted/30 py-6">
                                <CardTitle>Eligibility and limits</CardTitle>
                            </CardHeader>
                            <CardContent className="grid gap-4 p-6 md:grid-cols-2">
                                <div className="rounded-2xl border border-border/70 bg-background/80 p-4">
                                    <p className="text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">
                                        Minimum order amount
                                    </p>
                                    <p className="mt-2 text-lg font-semibold">
                                        {coupon.minimum_order_amount !== null
                                            ? formatMoney(
                                                  coupon.minimum_order_amount,
                                              )
                                            : 'None'}
                                    </p>
                                    <p className="mt-2 text-xs text-muted-foreground">
                                        Orders below this threshold should not
                                        receive the discount.
                                    </p>
                                </div>

                                <div className="rounded-2xl border border-border/70 bg-background/80 p-4">
                                    <p className="text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">
                                        Usage limit
                                    </p>
                                    <p className="mt-2 text-lg font-semibold">
                                        {coupon.usage_limit ?? 'Unlimited'}
                                    </p>
                                    <p className="mt-2 text-xs text-muted-foreground">
                                        {coupon.usage_limit !== null
                                            ? `${coupon.used_count} used, ${remainingRedemptions} left.`
                                            : 'No global redemption cap is configured.'}
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <div className="space-y-6">
                        <Card className="overflow-hidden border-border/70 pt-0">
                            <CardHeader className="border-b border-border/70 bg-muted/30 py-6">
                                <CardTitle>Timeline</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4 p-6">
                                <div className="rounded-2xl border border-border/70 bg-background/80 p-4">
                                    <p className="text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">
                                        Starts at
                                    </p>
                                    <p className="mt-2 font-medium">
                                        {formatDate(coupon.starts_at)}
                                    </p>
                                </div>
                                <div className="rounded-2xl border border-border/70 bg-background/80 p-4">
                                    <p className="text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">
                                        Expires at
                                    </p>
                                    <p className="mt-2 font-medium">
                                        {formatDate(coupon.expires_at)}
                                    </p>
                                </div>
                                <div className="rounded-2xl border border-border/70 bg-background/80 p-4">
                                    <p className="text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">
                                        Created
                                    </p>
                                    <p className="mt-2 font-medium">
                                        {formatDate(coupon.created_at)}
                                    </p>
                                </div>
                                <div className="rounded-2xl border border-border/70 bg-background/80 p-4">
                                    <p className="text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">
                                        Last updated
                                    </p>
                                    <p className="mt-2 font-medium">
                                        {formatDate(coupon.updated_at)}
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
