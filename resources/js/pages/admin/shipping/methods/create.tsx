import { Link } from '@inertiajs/react';
import { PageHeader } from '@/components/shared/page-header/page-header';
import { Button } from '@/components/ui/button';
import { AdminLayout } from '@/layouts/app/admin-layout';
import { adminRoutes } from '@/lib/routes';
import { MethodForm } from '@/pages/admin/shipping/methods/_components/method-form';

export default function ShippingMethodCreatePage() {
    return (
        <AdminLayout title="Create Shipping Method">
            <div className="mx-auto w-full max-w-6xl space-y-8">
                <PageHeader
                    title="Create shipping method"
                    description="Create a reusable shipping method definition that zones can attach when needed."
                    actions={
                        <Button variant="outline" asChild>
                            <Link href={adminRoutes.shipping.methods}>
                                Back to methods
                            </Link>
                        </Button>
                    }
                />
                <MethodForm />
            </div>
        </AdminLayout>
    );
}
