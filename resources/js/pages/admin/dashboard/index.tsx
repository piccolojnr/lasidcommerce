import { PageHeader } from '@/components/shared/page-header/page-header';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { AdminLayout } from '@/layouts/app/admin-layout';

export default function AdminDashboardPage() {
    return (
        <AdminLayout
            title="Dashboard"
            description="Operational overview for the admin workspace."
        >
            <div className="space-y-6">
                <PageHeader
                    title="Dashboard"
                    description="Track key operational areas from one place."
                />
                <div className="grid gap-4 md:grid-cols-3">
                    {['Revenue', 'Orders', 'Inventory'].map((item) => (
                        <Card key={item}>
                            <CardHeader>
                                <CardDescription>{item}</CardDescription>
                                <CardTitle className="text-2xl">--</CardTitle>
                            </CardHeader>
                            <CardContent className="text-sm text-muted-foreground">
                                Placeholder summary card for{' '}
                                {item.toLowerCase()}.
                            </CardContent>
                        </Card>
                    ))}
                </div>
                <Card>
                    <CardHeader>
                        <CardTitle>Activity</CardTitle>
                        <CardDescription>
                            Recent admin activity and operational alerts can
                            live here later.
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="text-sm text-muted-foreground">
                        Dashboard widgets and charts will be connected once
                        backend reporting endpoints are available.
                    </CardContent>
                </Card>
            </div>
        </AdminLayout>
    );
}
