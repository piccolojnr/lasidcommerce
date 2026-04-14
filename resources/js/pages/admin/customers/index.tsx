import { Link } from '@inertiajs/react';
import { PageHeader } from '@/components/shared/page-header/page-header';
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
    const { search, setSearch, setFilter } = useFilters(adminRoutes.customers, filters);

    return (
        <AdminLayout title="Customers" description="Review customer accounts and buying activity.">
            <div className="mx-auto w-full max-w-6xl space-y-6">
                <PageHeader
                    title="Customers"
                    description="Review customer accounts, order volume, and payment activity."
                    actions={
                        <Link href={adminRoutes.customers} className="text-sm text-muted-foreground hover:text-foreground">
                            Reset filters
                        </Link>
                    }
                />
                <div className="flex flex-wrap items-center gap-3">
                    <Input
                        placeholder="Search name or email…"
                        value={search}
                        onChange={(event) => setSearch(event.target.value)}
                        className="w-72"
                    />
                    <Select
                        value={filters.status ?? EMPTY_SENTINEL}
                        onValueChange={(value) => setFilter('status', value === EMPTY_SENTINEL ? null : value)}
                    >
                        <SelectTrigger className="w-44">
                            <SelectValue placeholder="All statuses" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value={EMPTY_SENTINEL}>All statuses</SelectItem>
                            <SelectItem value="active">Active</SelectItem>
                            <SelectItem value="inactive">Inactive</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <CustomerTable customers={customers.data} />
                {customers.last_page > 1 && customers.links && (
                    <div className="flex items-center justify-center gap-1">
                        {customers.links.map((link: PaginationLink, index: number) =>
                            link.url ? (
                                <Link
                                    key={index}
                                    href={link.url}
                                    className={`rounded border px-3 py-1 text-sm ${
                                        link.active ? 'bg-primary text-primary-foreground' : 'hover:bg-muted'
                                    }`}
                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                />
                            ) : (
                                <span
                                    key={index}
                                    className="rounded border px-3 py-1 text-sm opacity-40"
                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                />
                            ),
                        )}
                    </div>
                )}
            </div>
        </AdminLayout>
    );
}
