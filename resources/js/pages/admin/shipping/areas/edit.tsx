import { Link } from '@inertiajs/react';
import { PageHeader } from '@/components/shared/page-header/page-header';
import { Button } from '@/components/ui/button';
import { AdminLayout } from '@/layouts/app/admin-layout';
import { AreaForm } from '@/pages/admin/shipping/areas/_components/area-form';
import type { AdminShippingZoneAreaDetail } from '@/types/admin/shipping';

export default function ShippingAreaEditPage({
    area,
}: {
    area: AdminShippingZoneAreaDetail;
}) {
    return (
        <AdminLayout title="Edit Zone Area">
            <div className="mx-auto w-full max-w-5xl space-y-8">
                <PageHeader
                    title={`Edit ${area.area_name}`}
                    description="Adjust the area rule attached to this shipping zone."
                    actions={
                        <Button variant="outline" asChild>
                            <Link href={`/admin/shipping/areas/${area.id}`}>
                                Back to area
                            </Link>
                        </Button>
                    }
                />
                <AreaForm zone={area.zone} area={area} />
            </div>
        </AdminLayout>
    );
}
