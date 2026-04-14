import { Link } from '@inertiajs/react';
import { PageHeader } from '@/components/shared/page-header/page-header';
import { Button } from '@/components/ui/button';
import { AdminLayout } from '@/layouts/app/admin-layout';
import { AreaForm } from '@/pages/admin/shipping/areas/_components/area-form';

export default function ShippingAreaCreatePage({
    zone,
}: {
    zone: { id: number; name: string; code: string };
}) {
    return (
        <AdminLayout title="Create Zone Area">
            <div className="mx-auto w-full max-w-5xl space-y-8">
                <PageHeader
                    title="Create zone area"
                    description={`Add a new area rule under ${zone.name}.`}
                    actions={<Button variant="outline" asChild><Link href={`/admin/shipping/zones/${zone.id}`}>Back to zone</Link></Button>}
                />
                <AreaForm zone={zone} />
            </div>
        </AdminLayout>
    );
}
