import { Link } from '@inertiajs/react';
import { PageHeader } from '@/components/shared/page-header/page-header';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import {
    EMPTY_SENTINEL,
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useFilters } from '@/hooks/use-filters';
import { AdminLayout } from '@/layouts/app/admin-layout';
import { adminRoutes } from '@/lib/routes';
import { CustomerTable } from '@/pages/admin/customers/_components/customer-table';
import type { AdminCustomerListPage } from '@/types/admin/user';
import type { PaginationLink } from '@/types/shared/pagination';

interface CustomerFilters {
    [key: string]: string | null;
    search: string | null;
    status: string | null;
}

interface Props {
    customers: AdminCustomerListPage;
    filters: CustomerFilters;
}

export default function CustomerIndexPage({ customers, filters }: Props) {
    const { search, setSearch, setFilter } = useFilters(
        adminRoutes.customers,
        filters,
    );
    const activeCustomers = customers.data.filter(
        (customer) => customer.status === 'active',
    ).length;
    const buyingCustomers = customers.data.filter(
        (customer) => customer.orders_count > 0,
    ).length;

    return (
        <AdminLayout
            title="Customers"
            description="Review customer accounts and buying activity."
        >
            <div className="mx-auto w-full max-w-7xl space-y-8">
                <PageHeader
                    title="Customers"
                    description="Review customer health, purchase activity, and account state without exposing staff management controls."
                    actions={
                        <Link
                            href={adminRoutes.customers}
                            className="text-sm text-muted-foreground transition hover:text-foreground"
                        >
                            Reset filters
                        </Link>
                    }
                />

                <div className="grid gap-4 md:grid-cols-3">
                    <Card className="border-border/70">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                Visible in this result
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-3xl font-semibold">
                                {customers.data.length}
                            </div>
                            <p className="text-sm text-muted-foreground">
                                Customer accounts on the current page after
                                filters.
                            </p>
                        </CardContent>
                    </Card>
                    <Card className="border-border/70">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                Active customers
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-3xl font-semibold">
                                {activeCustomers}
                            </div>
                            <p className="text-sm text-muted-foreground">
                                Accounts still active and usable.
                            </p>
                        </CardContent>
                    </Card>
                    <Card className="border-border/70">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                With order history
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-3xl font-semibold">
                                {buyingCustomers}
                            </div>
                            <p className="text-sm text-muted-foreground">
                                Customers who have already placed at least one
                                order.
                            </p>
                        </CardContent>
                    </Card>
                </div>

                <div className="rounded-[2rem] border border-border/70 bg-muted/25 p-5">
                    <p className="text-xs font-semibold tracking-[0.24em] text-muted-foreground uppercase">
                        Customer filter
                    </p>
                    <p className="mt-1 text-sm text-muted-foreground">
                        Search by customer account, then narrow by status.
                    </p>
                    <div className="mt-4 flex flex-wrap items-center gap-3">
                        <Input
                            placeholder="Search name or email..."
                            value={search}
                            onChange={(event) => setSearch(event.target.value)}
                            className="h-11 w-72 bg-background"
                        />
                        <Select
                            value={filters.status ?? EMPTY_SENTINEL}
                            onValueChange={(value) =>
                                setFilter(
                                    'status',
                                    value === EMPTY_SENTINEL ? null : value,
                                )
                            }
                        >
                            <SelectTrigger className="h-11 w-44 bg-background">
                                <SelectValue placeholder="All statuses" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value={EMPTY_SENTINEL}>
                                    All statuses
                                </SelectItem>
                                <SelectItem value="active">Active</SelectItem>
                                <SelectItem value="inactive">
                                    Inactive
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>

                <CustomerTable customers={customers.data} />

                {customers.last_page > 1 && customers.links && (
                    <div className="flex items-center justify-center gap-1">
                        {customers.links.map(
                            (link: PaginationLink, index: number) =>
                                link.url ? (
                                    <Link
                                        key={index}
                                        href={link.url}
                                        className={`rounded border px-3 py-1 text-sm ${
                                            link.active
                                                ? 'bg-primary text-primary-foreground'
                                                : 'hover:bg-muted'
                                        }`}
                                        dangerouslySetInnerHTML={{
                                            __html: link.label,
                                        }}
                                    />
                                ) : (
                                    <span
                                        key={index}
                                        className="rounded border px-3 py-1 text-sm opacity-40"
                                        dangerouslySetInnerHTML={{
                                            __html: link.label,
                                        }}
                                    />
                                ),
                        )}
                    </div>
                )}
            </div>
        </AdminLayout>
    );
}
