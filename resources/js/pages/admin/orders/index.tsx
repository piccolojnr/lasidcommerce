import { Link } from '@inertiajs/react';
import * as OrderController from '@/actions/App/Http/Controllers/Admin/Orders/OrderController';
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
import { OrderTable } from '@/pages/admin/orders/_components/order-table';
import type { AdminOrderListPage } from '@/types/admin/order';
import type { PaginationLink } from '@/types/shared/pagination';

interface OrderFilters {
    [key: string]: string | null;
    search: string | null;
    status: string | null;
    payment_status: string | null;
    fulfillment_status: string | null;
}

interface Props {
    orders: AdminOrderListPage;
    filters: OrderFilters;
}

export default function OrderIndexPage({ orders, filters }: Props) {
    const { search, setSearch, setFilter } = useFilters(OrderController.index.url(), filters);

    return (
        <AdminLayout title="Orders" description="Track order intake and fulfillment progress.">
            <div className="mx-auto w-full max-w-6xl space-y-6">
                <PageHeader
                    title="Orders"
                    description="Monitor incoming orders, payment state, and fulfillment progress."
                    actions={
                        <Link
                            href={OrderController.index.url()}
                            className="text-sm text-muted-foreground hover:text-foreground"
                        >
                            Reset filters
                        </Link>
                    }
                />
                <div className="flex flex-wrap items-center gap-3">
                    <Input
                        placeholder="Search order number or email…"
                        value={search}
                        onChange={(event) => setSearch(event.target.value)}
                        className="w-72"
                    />
                    <Select
                        value={filters.status ?? EMPTY_SENTINEL}
                        onValueChange={(value) => setFilter('status', value === EMPTY_SENTINEL ? null : value)}
                    >
                        <SelectTrigger className="w-44">
                            <SelectValue placeholder="All order states" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value={EMPTY_SENTINEL}>All order states</SelectItem>
                            {['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'completed', 'cancelled'].map((status) => (
                                <SelectItem key={status} value={status}>
                                    {status.replace(/\b\w/g, (character) => character.toUpperCase())}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                    <Select
                        value={filters.payment_status ?? EMPTY_SENTINEL}
                        onValueChange={(value) => setFilter('payment_status', value === EMPTY_SENTINEL ? null : value)}
                    >
                        <SelectTrigger className="w-44">
                            <SelectValue placeholder="All payment states" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value={EMPTY_SENTINEL}>All payment states</SelectItem>
                            {['unpaid', 'paid', 'failed'].map((status) => (
                                <SelectItem key={status} value={status}>
                                    {status.replace(/\b\w/g, (character) => character.toUpperCase())}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                    <Select
                        value={filters.fulfillment_status ?? EMPTY_SENTINEL}
                        onValueChange={(value) => setFilter('fulfillment_status', value === EMPTY_SENTINEL ? null : value)}
                    >
                        <SelectTrigger className="w-44">
                            <SelectValue placeholder="All fulfillment states" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value={EMPTY_SENTINEL}>All fulfillment states</SelectItem>
                            {['unfulfilled', 'fulfilled'].map((status) => (
                                <SelectItem key={status} value={status}>
                                    {status.replace(/\b\w/g, (character) => character.toUpperCase())}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                </div>
                <OrderTable orders={orders.data} />
                {orders.last_page > 1 && orders.links && (
                    <div className="flex items-center justify-center gap-1">
                        {orders.links.map((link: PaginationLink, index: number) =>
                            link.url ? (
                                <Link
                                    key={index}
                                    href={link.url}
                                    className={`rounded border px-3 py-1 text-sm ${
                                        link.active
                                            ? 'bg-primary text-primary-foreground'
                                            : 'hover:bg-muted'
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
