import { Link } from '@inertiajs/react';
import { PageHeader } from '@/components/shared/page-header/page-header';
import { Button } from '@/components/ui/button';
import { AdminLayout } from '@/layouts/app/admin-layout';
import { MethodForm } from '@/pages/admin/shipping/methods/_components/method-form';
import type { AdminShippingMethodDetail } from '@/types/admin/shipping';

export default function ShippingMethodEditPage({ method }: { method: AdminShippingMethodDetail }) {
    return (
        <AdminLayout title="Edit Shipping Method">
            <div className="mx-auto w-full max-w-6xl space-y-8">
                <PageHeader
                    title={`Edit ${method.name}`}
                    description="Adjust the pricing and delivery expectations for this method."
                    actions={<Button variant="outline" asChild><Link href={`/admin/shipping/methods/${method.id}`}>Back to method</Link></Button>}
                />
                <MethodForm zone={method.zone} method={method} />
            </div>
        </AdminLayout>
    );
}
