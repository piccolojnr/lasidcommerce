import { Link, useForm } from '@inertiajs/react';
import * as OrderController from '@/actions/App/Http/Controllers/Admin/Orders/OrderController';
import * as UserController from '@/actions/App/Http/Controllers/Admin/Users/UserController';
import * as UserRoleController from '@/actions/App/Http/Controllers/Admin/Users/UserRoleController';
import { EmptyState } from '@/components/shared/empty-state/empty-state';
import { FieldError } from '@/components/shared/forms/field-error';
import { PageHeader } from '@/components/shared/page-header/page-header';
import { StatusBadge } from '@/components/shared/status-badge/status-badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { AdminLayout } from '@/layouts/app/admin-layout';
import { formatDate } from '@/lib/formatters/date';
import { formatMoney } from '@/lib/formatters/money';
import type { AdminUserDetail } from '@/types/admin/user';

interface RoleOption {
    name: string;
}

interface Props {
    user: AdminUserDetail;
    availableRoles: RoleOption[];
    availableStatuses: string[];
}

export default function UserShowPage({
    user,
    availableRoles,
    availableStatuses,
}: Props) {
    const statusForm = useForm({
        status: user.status,
    });

    const rolesForm = useForm({
        roles: user.roles,
    });

    return (
        <AdminLayout title="Platform User Details" description="Inspect staff account profile and authorization state.">
            <div className="mx-auto w-full max-w-7xl space-y-8">
                <PageHeader
                    title={user.name}
                    description={`Account created ${formatDate(user.created_at)}.`}
                    actions={
                        <Button variant="outline" asChild>
                            <Link href={UserController.index.url()}>Back to platform users</Link>
                        </Button>
                    }
                />

                <div className="grid gap-4 lg:grid-cols-3">
                    <Card className="border-border/70 bg-muted/30 lg:col-span-2">
                        <CardHeader className="space-y-3">
                            <div className="flex items-center justify-between gap-3">
                                <p className="text-xs font-semibold uppercase tracking-[0.24em] text-muted-foreground">
                                    Access profile
                                </p>
                                <StatusBadge status={user.status} />
                            </div>
                            <CardTitle className="text-2xl">{user.name}</CardTitle>
                            <p className="max-w-2xl text-sm leading-6 text-muted-foreground">
                                {user.email} {user.phone ? `• ${user.phone}` : '• no phone number on record'}.
                            </p>
                        </CardHeader>
                        <CardContent className="grid gap-4 border-t border-border/70 pt-6 md:grid-cols-3">
                            <div>
                                <p className="text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">Roles</p>
                                <p className="mt-2 font-semibold">{user.roles.length > 0 ? user.roles.join(', ') : 'No roles'}</p>
                            </div>
                            <div>
                                <p className="text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">Email verified</p>
                                <p className="mt-2 font-semibold">{formatDate(user.email_verified_at)}</p>
                            </div>
                            <div>
                                <p className="text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">Two-factor</p>
                                <p className="mt-2 font-semibold">
                                    {user.two_factor_confirmed_at
                                        ? `Enabled • ${formatDate(user.two_factor_confirmed_at)}`
                                        : 'Disabled'}
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <Card className="border-border/70 bg-primary/5">
                        <CardHeader className="space-y-2">
                            <p className="text-xs font-semibold uppercase tracking-[0.24em] text-primary">
                                Activity footprint
                            </p>
                            <CardTitle className="text-xl">Commercial context</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-3 text-sm">
                            <div className="flex items-center justify-between">
                                <span className="text-muted-foreground">Orders</span>
                                <span className="font-medium">{user.orders_count}</span>
                            </div>
                            <div className="flex items-center justify-between">
                                <span className="text-muted-foreground">Payments</span>
                                <span className="font-medium">{user.payments_count}</span>
                            </div>
                            <div className="flex items-center justify-between">
                                <span className="text-muted-foreground">Addresses</span>
                                <span className="font-medium">{user.addresses_count}</span>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <div className="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
                    <div className="space-y-6">
                        <Card className="overflow-hidden border-border/70 pt-0">
                            <CardHeader className="border-b border-border/70 bg-muted/30 py-6">
                                <CardTitle>Recent orders</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-3 p-6">
                                {user.recent_orders.length === 0 ? (
                                    <EmptyState title="No orders yet" description="This user has not placed any orders." />
                                ) : (
                                    user.recent_orders.map((order) => (
                                        <div key={order.id} className="rounded-2xl border border-border/70 bg-background/80 p-4 text-sm">
                                            <div className="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                                                <div className="space-y-1">
                                                    <Link
                                                        href={OrderController.show.url(order.id)}
                                                        className="font-medium transition hover:text-primary"
                                                    >
                                                        {order.order_number}
                                                    </Link>
                                                    <p className="text-muted-foreground">{formatDate(order.placed_at)}</p>
                                                </div>
                                                <div className="text-right">
                                                    <StatusBadge status={order.status} />
                                                    <p className="mt-1 font-medium">
                                                        {formatMoney(order.total_amount, order.currency_code)}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    ))
                                )}
                            </CardContent>
                        </Card>

                        <Card className="overflow-hidden border-border/70 pt-0">
                            <CardHeader className="border-b border-border/70 bg-muted/30 py-6">
                                <CardTitle>Recent payments</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-3 p-6">
                                {user.recent_payments.length === 0 ? (
                                    <EmptyState title="No payments yet" description="This user has no payment attempts on record." />
                                ) : (
                                    user.recent_payments.map((payment) => (
                                        <div key={payment.id} className="rounded-2xl border border-border/70 bg-background/80 p-4 text-sm">
                                            <div className="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                                                <div className="space-y-1">
                                                    <p className="font-medium">{payment.reference}</p>
                                                    <p className="text-muted-foreground">
                                                        {payment.provider} • {formatDate(payment.paid_at)}
                                                    </p>
                                                </div>
                                                <div className="text-right">
                                                    <StatusBadge status={payment.status} />
                                                    <p className="mt-1 font-medium">
                                                        {formatMoney(payment.amount, payment.currency_code)}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    ))
                                )}
                            </CardContent>
                        </Card>
                    </div>

                    <div className="space-y-6">
                        <Card className="overflow-hidden border-border/70 pt-0">
                            <CardHeader className="border-b border-border/70 bg-muted/30 py-6">
                                <CardTitle>Account status</CardTitle>
                            </CardHeader>
                            <CardContent className="p-6">
                                <form
                                    className="space-y-4"
                                    onSubmit={(event) => {
                                        event.preventDefault();
                                        statusForm.patch(UserController.update.url(user), {
                                            preserveScroll: true,
                                        });
                                    }}
                                >
                                    <div className="space-y-2">
                                        <Label htmlFor="status">Status</Label>
                                        <Select
                                            value={statusForm.data.status}
                                            onValueChange={(value) => statusForm.setData('status', value)}
                                        >
                                            <SelectTrigger id="status" className="w-full">
                                                <SelectValue placeholder="Select a status" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {availableStatuses.map((status) => (
                                                    <SelectItem key={status} value={status}>
                                                        {status.replace(/\b\w/g, (character) => character.toUpperCase())}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        <FieldError message={statusForm.errors.status} />
                                    </div>
                                    <Button type="submit" disabled={statusForm.processing} className="w-full">
                                        {statusForm.processing ? 'Updating…' : 'Update status'}
                                    </Button>
                                </form>
                            </CardContent>
                        </Card>

                        <Card className="overflow-hidden border-border/70 pt-0">
                            <CardHeader className="border-b border-border/70 bg-muted/30 py-6">
                                <CardTitle>Roles</CardTitle>
                            </CardHeader>
                            <CardContent className="p-6">
                                <form
                                    className="space-y-4"
                                    onSubmit={(event) => {
                                        event.preventDefault();
                                        rolesForm.patch(UserRoleController.update.url(user), {
                                            preserveScroll: true,
                                        });
                                    }}
                                >
                                    <div className="space-y-3">
                                        {availableRoles.map((role) => {
                                            const checked = rolesForm.data.roles.includes(role.name);

                                            return (
                                                <div key={role.name} className="flex items-center gap-3 rounded-xl border border-border/60 bg-background/80 p-3">
                                                    <Checkbox
                                                        id={role.name}
                                                        checked={checked}
                                                        onCheckedChange={(value) => {
                                                            const shouldInclude = Boolean(value);
                                                            rolesForm.setData(
                                                                'roles',
                                                                shouldInclude
                                                                    ? [...rolesForm.data.roles, role.name]
                                                                    : rolesForm.data.roles.filter((item) => item !== role.name),
                                                            );
                                                        }}
                                                    />
                                                    <Label htmlFor={role.name} className="cursor-pointer font-normal">
                                                        {role.name}
                                                    </Label>
                                                </div>
                                            );
                                        })}
                                    </div>
                                    <FieldError message={rolesForm.errors.roles} />
                                    <Button type="submit" disabled={rolesForm.processing} className="w-full">
                                        {rolesForm.processing ? 'Updating…' : 'Update roles'}
                                    </Button>
                                </form>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
