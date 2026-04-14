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

export function CouponTable({ coupons }: CouponTableProps) {
    if (coupons.length === 0) {
        return (
            <div className="rounded-lg border bg-background px-6 py-12 text-center">
                <p className="text-sm text-muted-foreground">No coupons yet. Create one to start running campaigns.</p>
            </div>
        );
    }

    return (
        <div className="overflow-hidden rounded-lg border bg-background">
            <div className="overflow-x-auto">
                <table className="min-w-full text-sm">
                    <thead className="bg-muted/40">
                        <tr>
                            <th className="px-4 py-3 text-left font-medium text-muted-foreground">Code</th>
                            <th className="px-4 py-3 text-left font-medium text-muted-foreground">Type</th>
                            <th className="px-4 py-3 text-left font-medium text-muted-foreground">Value</th>
                            <th className="px-4 py-3 text-left font-medium text-muted-foreground">Usage</th>
                            <th className="px-4 py-3 text-left font-medium text-muted-foreground">Expires</th>
                            <th className="px-4 py-3 text-left font-medium text-muted-foreground">Status</th>
                            <th className="px-4 py-3 text-left font-medium text-muted-foreground">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        {coupons.map((coupon) => (
                            <tr key={coupon.id} className="border-t">
                                <td className="px-4 py-3 align-middle font-mono text-xs font-semibold">{coupon.code}</td>
                                <td className="px-4 py-3 align-middle capitalize text-muted-foreground">{coupon.type}</td>
                                <td className="px-4 py-3 align-middle">{formatMoney(coupon.value)}</td>
                                <td className="px-4 py-3 align-middle text-muted-foreground">
                                    {coupon.used_count}
                                    {coupon.usage_limit !== null ? ` / ${coupon.usage_limit}` : ''}
                                </td>
                                <td className="px-4 py-3 align-middle text-muted-foreground">{formatDate(coupon.expires_at)}</td>
                                <td className="px-4 py-3 align-middle">
                                    <StatusBadge status={coupon.is_active ? 'active' : 'inactive'} />
                                </td>
                                <td className="px-4 py-3 align-middle">
                                    <div className="flex items-center gap-2">
                                        <Button variant="outline" size="sm" asChild>
                                            <Link href={CouponController.show.url(coupon)}>View</Link>
                                        </Button>
                                        <Button variant="outline" size="sm" asChild>
                                            <Link href={CouponController.edit.url(coupon)}>Edit</Link>
                                        </Button>
                                        <Form
                                            {...CouponController.destroy.form.delete(coupon)}
                                            onSubmit={(e) => {
                                                if (!window.confirm(`Delete coupon "${coupon.code}"?`)) {
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
