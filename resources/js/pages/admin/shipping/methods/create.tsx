import { Link } from '@inertiajs/react';
import { PageHeader } from '@/components/shared/page-header/page-header';
import { Button } from '@/components/ui/button';
import { AdminLayout } from '@/layouts/app/admin-layout';
import { MethodForm } from '@/pages/admin/shipping/methods/_components/method-form';

export default function ShippingMethodCreatePage({
    zone,
}: {
    zone: { id: number; name: string; code: string };
}) {
    return (
        <AdminLayout title="Create Shipping Method">
            <div className="mx-auto w-full max-w-6xl space-y-8">
                <PageHeader
                    title="Create shipping method"
                    description={`Add a new method under ${zone.name}.`}
                    actions={<Button variant="outline" asChild><Link href={`/admin/shipping/zones/${zone.id}`}>Back to zone</Link></Button>}
                />
                <MethodForm zone={zone} />
            </div>
        </AdminLayout>
    );
}
