import { PageHeader } from '@/components/shared/page-header/page-header';
import { AdminLayout } from '@/layouts/app/admin-layout';
import { CouponForm } from '@/pages/admin/coupons/_components/coupon-form';

export default function CouponCreatePage() {
    return (
        <AdminLayout title="Create Coupon" description="Add a new campaign code.">
            <div className="space-y-6">
                <PageHeader title="Create coupon" description="Define discount structure, validity, and usage controls." />
                <CouponForm />
            </div>
        </AdminLayout>
    );
}
