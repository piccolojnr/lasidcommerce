import { Link } from '@inertiajs/react';
import * as CouponController from '@/actions/App/Http/Controllers/Admin/Coupons/CouponController';
import { PageHeader } from '@/components/shared/page-header/page-header';
import { Button } from '@/components/ui/button';
import { AdminLayout } from '@/layouts/app/admin-layout';
import { CouponForm } from '@/pages/admin/coupons/_components/coupon-form';
import type { AdminCouponDetail } from '@/types/admin/coupon';

interface Props {
    coupon: AdminCouponDetail;
}

export default function CouponEditPage({ coupon }: Props) {
    return (
        <AdminLayout title="Edit Coupon">
            <div className="mx-auto w-full max-w-3xl space-y-6">
                <PageHeader
                    title={`Edit ${coupon.code}`}
                    description="Adjust discount value, validity windows, and usage limits."
                    actions={
                        <Button variant="outline" asChild>
                            <Link href={CouponController.show.url(coupon)}>Back to coupon</Link>
                        </Button>
                    }
                />
                <CouponForm coupon={coupon} />
            </div>
        </AdminLayout>
    );
}
