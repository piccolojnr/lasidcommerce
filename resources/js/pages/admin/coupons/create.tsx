import { Link } from '@inertiajs/react';
import * as CouponController from '@/actions/App/Http/Controllers/Admin/Coupons/CouponController';
import { PageHeader } from '@/components/shared/page-header/page-header';
import { Button } from '@/components/ui/button';
import { AdminLayout } from '@/layouts/app/admin-layout';
import { CouponForm } from '@/pages/admin/coupons/_components/coupon-form';

export default function CouponCreatePage() {
    return (
        <AdminLayout title="Create Coupon">
            <div className="mx-auto w-full max-w-3xl space-y-6">
                <PageHeader
                    title="Create coupon"
                    description="Define discount structure, validity windows, and usage controls."
                    actions={
                        <Button variant="outline" asChild>
                            <Link href={CouponController.index.url()}>Back to list</Link>
                        </Button>
                    }
                />
                <CouponForm />
            </div>
        </AdminLayout>
    );
}
