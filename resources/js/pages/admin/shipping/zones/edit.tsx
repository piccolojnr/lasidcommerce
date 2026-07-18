import { Link } from '@inertiajs/react';
import { PageHeader } from '@/components/shared/page-header/page-header';
import { Button } from '@/components/ui/button';
import { AdminLayout } from '@/layouts/app/admin-layout';
import { ZoneForm } from '@/pages/admin/shipping/zones/_components/zone-form';
import type { AdminShippingZoneDetail } from '@/types/admin/shipping';

export default function ShippingZoneEditPage({
    zone,
}: {
    zone: AdminShippingZoneDetail;
}) {
    return (
        <AdminLayout title="Edit Shipping Zone">
            <div className="mx-auto w-full max-w-7xl space-y-8">
                <PageHeader
                    title={`Edit ${zone.name}`}
                    description="Adjust the coverage metadata and active state for this shipping zone."
                    actions={
                        <Button variant="outline" asChild>
                            <Link href={`/admin/shipping/zones/${zone.id}`}>
                                Back to zone
                            </Link>
                        </Button>
                    }
                />
                <ZoneForm zone={zone} />
            </div>
        </AdminLayout>
    );
}
