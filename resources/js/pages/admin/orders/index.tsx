import { Link } from '@inertiajs/react';
import * as OrderController from '@/actions/App/Http/Controllers/Admin/Orders/OrderController';
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
    const { search, setSearch, setFilter } = useFilters(
        OrderController.index.url(),
        filters,
    );
    const paidOrders = orders.data.filter(
        (order) => order.payment_status === 'paid',
    ).length;
    const blockedOrders = orders.data.filter(
        (order) => order.fulfillment_status !== 'fulfilled',
    ).length;

    return (
        <AdminLayout title="Orders" description="Track order intake and fulfillment progress.">
            <div className="mx-auto w-full max-w-7xl space-y-8">
                <PageHeader
                    title="Orders"
                    description="Monitor intake, commercial health, and fulfillment drag from one operational surface."
                    actions={
                        <Link
                            href={OrderController.index.url()}
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
                            <div className="text-3xl font-semibold">{orders.data.length}</div>
                            <p className="text-sm text-muted-foreground">
                                Orders on the current page after filters.
                            </p>
                        </CardContent>
                    </Card>
                    <Card className="border-border/70">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                Paid in this slice
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-3xl font-semibold">{paidOrders}</div>
                            <p className="text-sm text-muted-foreground">
                                Orders with money already captured or confirmed.
                            </p>
                        </CardContent>
                    </Card>
                    <Card className="border-border/70">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                Still not fulfilled
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-3xl font-semibold">{blockedOrders}</div>
                            <p className="text-sm text-muted-foreground">
                                Orders still sitting in the operational queue.
                            </p>
                        </CardContent>
                    </Card>
                </div>

                <div className="rounded-[2rem] border border-border/70 bg-muted/25 p-5">
                    <div className="space-y-1">
                        <p className="text-xs font-semibold uppercase tracking-[0.24em] text-muted-foreground">
                            Queue filter
                        </p>
                        <p className="text-sm text-muted-foreground">
                            Search by order or customer, then narrow by order, payment, or fulfillment state.
                        </p>
                    </div>

                    <div className="mt-4 flex flex-wrap items-center gap-3">
                        <Input
                            placeholder="Search order number or email..."
                            value={search}
                            onChange={(event) => setSearch(event.target.value)}
                            className="h-11 w-72 bg-background"
                        />
                        <Select
                            value={filters.status ?? EMPTY_SENTINEL}
                            onValueChange={(value) =>
                                setFilter('status', value === EMPTY_SENTINEL ? null : value)
                            }
                        >
                            <SelectTrigger className="h-11 w-44 bg-background">
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
                            onValueChange={(value) =>
                                setFilter('payment_status', value === EMPTY_SENTINEL ? null : value)
                            }
                        >
                            <SelectTrigger className="h-11 w-44 bg-background">
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
                            onValueChange={(value) =>
                                setFilter('fulfillment_status', value === EMPTY_SENTINEL ? null : value)
                            }
                        >
                            <SelectTrigger className="h-11 w-44 bg-background">
                                <SelectValue placeholder="All fulfillment states" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value={EMPTY_SENTINEL}>All fulfillment states</SelectItem>
                                {['unfulfilled', 'partially_fulfilled', 'fulfilled'].map((status) => (
                                    <SelectItem key={status} value={status}>
                                        {status.replace(/\b\w/g, (character) => character.toUpperCase())}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>
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
