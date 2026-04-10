import { PageHeader } from '@/components/shared/page-header/page-header';
import { AdminLayout } from '@/layouts/app/admin-layout';
import { CouponForm } from '@/pages/admin/coupons/_components/coupon-form';

export default function CouponEditPage() {
    return (
        <AdminLayout title="Edit Coupon" description="Update coupon rules and scheduling.">
            <div className="space-y-6">
                <PageHeader title="Edit coupon" description="Adjust usage limits, active state, and value settings." />
                <CouponForm />
            </div>
        </AdminLayout>
    );
}
