import { Form, Link } from '@inertiajs/react';
import * as CouponController from '@/actions/App/Http/Controllers/Admin/Coupons/CouponController';
import { StatusBadge } from '@/components/shared/status-badge/status-badge';
import { Button } from '@/components/ui/button';
import { formatDate } from '@/lib/formatters/date';
import { formatMoney } from '@/lib/formatters/money';
import type { AdminCoupon } from '@/types/admin/coupon';

interface CouponTableProps {
    coupons: AdminCoupon[];
}

function formatCouponValue(coupon: AdminCoupon): string {
    return coupon.type === 'percentage'
        ? `${coupon.value}%`
        : formatMoney(coupon.value);
}

export function CouponTable({ coupons }: CouponTableProps) {
    if (coupons.length === 0) {
        return (
            <div className="rounded-3xl border border-dashed border-border/70 bg-muted/30 px-6 py-14 text-center">
                <p className="text-sm text-muted-foreground">
                    No coupons yet. Create one when you are ready to run an
                    actual campaign instead of pretending marketing is handled.
                </p>
            </div>
        );
    }

    return (
        <div className="overflow-hidden rounded-3xl border border-border/70 bg-background shadow-sm">
            <div className="overflow-x-auto">
                <table className="min-w-full text-sm">
                    <thead className="bg-muted/35">
                        <tr>
                            <th className="px-5 py-4 text-left font-medium text-muted-foreground">
                                Campaign
                            </th>
                            <th className="px-5 py-4 text-left font-medium text-muted-foreground">
                                Value
                            </th>
                            <th className="px-5 py-4 text-left font-medium text-muted-foreground">
                                Guardrails
                            </th>
                            <th className="px-5 py-4 text-left font-medium text-muted-foreground">
                                Redemption
                            </th>
                            <th className="px-5 py-4 text-left font-medium text-muted-foreground">
                                Window
                            </th>
                            <th className="px-5 py-4 text-left font-medium text-muted-foreground">
                                Status
                            </th>
                            <th className="px-5 py-4 text-left font-medium text-muted-foreground">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        {coupons.map((coupon) => (
                            <tr
                                key={coupon.id}
                                className="border-t border-border/60 align-top"
                            >
                                <td className="px-5 py-4">
                                    <div className="space-y-1">
                                        <Link
                                            href={CouponController.show.url(
                                                coupon,
                                            )}
                                            className="font-mono text-xs font-semibold tracking-[0.18em] text-foreground uppercase transition hover:text-primary"
                                        >
                                            {coupon.code}
                                        </Link>
                                        <p className="text-xs text-muted-foreground capitalize">
                                            {coupon.type === 'percentage'
                                                ? 'Percentage discount'
                                                : 'Fixed discount'}
                                        </p>
                                    </div>
                                </td>
                                <td className="px-5 py-4">
                                    <div className="space-y-1">
                                        <p className="font-semibold">
                                            {formatCouponValue(coupon)}
                                        </p>
                                        {coupon.maximum_discount_amount !==
                                        null ? (
                                            <p className="text-xs text-muted-foreground">
                                                Cap:{' '}
                                                {formatMoney(
                                                    coupon.maximum_discount_amount,
                                                )}
                                            </p>
                                        ) : (
                                            <p className="text-xs text-muted-foreground">
                                                No max discount cap
                                            </p>
                                        )}
                                    </div>
                                </td>
                                <td className="px-5 py-4">
                                    <div className="space-y-1 text-muted-foreground">
                                        <p className="text-xs">
                                            Minimum:{' '}
                                            {coupon.minimum_order_amount !==
                                            null
                                                ? formatMoney(
                                                      coupon.minimum_order_amount,
                                                  )
                                                : 'None'}
                                        </p>
                                        <p className="text-xs">
                                            Limit:{' '}
                                            {coupon.usage_limit ?? 'Unlimited'}
                                        </p>
                                    </div>
                                </td>
                                <td className="px-5 py-4">
                                    <div className="space-y-1">
                                        <p className="font-medium">
                                            {coupon.used_count}
                                            {coupon.usage_limit !== null
                                                ? ` / ${coupon.usage_limit}`
                                                : ''}
                                        </p>
                                        <p className="text-xs text-muted-foreground">
                                            {coupon.usage_limit !== null
                                                ? `${Math.max(coupon.usage_limit - coupon.used_count, 0)} left`
                                                : 'Unlimited redemptions'}
                                        </p>
                                    </div>
                                </td>
                                <td className="px-5 py-4 text-muted-foreground">
                                    <div className="space-y-1 text-xs">
                                        <p>
                                            Starts:{' '}
                                            {formatDate(coupon.starts_at)}
                                        </p>
                                        <p>
                                            Expires:{' '}
                                            {formatDate(coupon.expires_at)}
                                        </p>
                                    </div>
                                </td>
                                <td className="px-5 py-4">
                                    <div className="space-y-2">
                                        <StatusBadge
                                            status={
                                                coupon.is_active
                                                    ? 'active'
                                                    : 'inactive'
                                            }
                                        />
                                        <p className="text-xs text-muted-foreground">
                                            {coupon.is_currently_valid
                                                ? 'Currently valid'
                                                : 'Not currently valid'}
                                        </p>
                                    </div>
                                </td>
                                <td className="px-5 py-4">
                                    <div className="flex flex-wrap items-center gap-2">
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            asChild
                                        >
                                            <Link
                                                href={CouponController.show.url(
                                                    coupon,
                                                )}
                                            >
                                                View
                                            </Link>
                                        </Button>
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            asChild
                                        >
                                            <Link
                                                href={CouponController.edit.url(
                                                    coupon,
                                                )}
                                            >
                                                Edit
                                            </Link>
                                        </Button>
                                        <Form
                                            {...CouponController.destroy.form.delete(
                                                coupon,
                                            )}
                                            onSubmit={(e) => {
                                                if (
                                                    !window.confirm(
                                                        `Delete coupon "${coupon.code}"?`,
                                                    )
                                                ) {
                                                    e.preventDefault();
                                                }
                                            }}
                                        >
                                            {() => (
                                                <Button
                                                    type="submit"
                                                    variant="outline"
                                                    size="sm"
                                                    className="text-destructive hover:text-destructive"
                                                >
                                                    Delete
                                                </Button>
                                            )}
                                        </Form>
                                    </div>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </div>
    );
}
