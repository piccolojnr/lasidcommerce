import { Link } from '@inertiajs/react';
import { PageHeader } from '@/components/shared/page-header/page-header';
import { Button } from '@/components/ui/button';
import { AdminLayout } from '@/layouts/app/admin-layout';
import { adminRoutes } from '@/lib/routes';
import { ZoneForm } from '@/pages/admin/shipping/zones/_components/zone-form';

export default function ShippingZoneCreatePage() {
    return (
        <AdminLayout title="Create Shipping Zone">
            <div className="mx-auto w-full max-w-7xl space-y-8">
                <PageHeader
                    title="Create shipping zone"
                    description="Define a new shipping territory for routing, pricing, and delivery configuration."
                    actions={
                        <Button variant="outline" asChild>
                            <Link href={adminRoutes.shipping.zones}>
                                Back to zones
                            </Link>
                        </Button>
                    }
                />
                <ZoneForm />
            </div>
        </AdminLayout>
    );
}
