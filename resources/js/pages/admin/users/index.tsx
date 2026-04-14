import { Link } from '@inertiajs/react';
import * as UserController from '@/actions/App/Http/Controllers/Admin/Users/UserController';
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
import { UserTable } from '@/pages/admin/users/_components/user-table';
import type { AdminUserListPage } from '@/types/admin/user';
import type { PaginationLink } from '@/types/shared/pagination';

interface RoleOption {
    name: string;
}

interface UserFilters {
    [key: string]: string | null;
    search: string | null;
    status: string | null;
    role: string | null;
}

interface Props {
    users: AdminUserListPage;
    filters: UserFilters;
    roles: RoleOption[];
}

export default function UserIndexPage({ users, filters, roles }: Props) {
    const { search, setSearch, setFilter } = useFilters(
        UserController.index.url(),
        filters,
    );
    const activeUsers = users.data.filter((user) => user.status === 'active').length;
    const assignedUsers = users.data.filter((user) => user.roles.length > 0).length;

    return (
        <AdminLayout title="Platform Users" description="Manage internal staff accounts and access.">
            <div className="mx-auto w-full max-w-7xl space-y-8">
                <PageHeader
                    title="Platform users"
                    description="Manage internal staff accounts, role assignment, and account state without mixing them with customers."
                    actions={
                        <Link
                            href={UserController.index.url()}
                            className="text-sm text-muted-foreground transition hover:text-foreground"
                        >
                            Reset filters
                        </Link>
                    }
                />

                <div className="grid gap-4 md:grid-cols-3">
                    <Card className="border-border/70">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">Visible in this result</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-3xl font-semibold">{users.data.length}</div>
                            <p className="text-sm text-muted-foreground">Internal accounts on the current page after filters.</p>
                        </CardContent>
                    </Card>
                    <Card className="border-border/70">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">Active staff</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-3xl font-semibold">{activeUsers}</div>
                            <p className="text-sm text-muted-foreground">Accounts still enabled for platform access.</p>
                        </CardContent>
                    </Card>
                    <Card className="border-border/70">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">With role assignments</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-3xl font-semibold">{assignedUsers}</div>
                            <p className="text-sm text-muted-foreground">Accounts already mapped to at least one role.</p>
                        </CardContent>
                    </Card>
                </div>

                <div className="rounded-[2rem] border border-border/70 bg-muted/25 p-5">
                    <p className="text-xs font-semibold uppercase tracking-[0.24em] text-muted-foreground">
                        Access filter
                    </p>
                    <p className="mt-1 text-sm text-muted-foreground">
                        Search by staff member, then narrow by account status or role assignment.
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
                                setFilter('status', value === EMPTY_SENTINEL ? null : value)
                            }
                        >
                            <SelectTrigger className="h-11 w-44 bg-background">
                                <SelectValue placeholder="All statuses" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value={EMPTY_SENTINEL}>All statuses</SelectItem>
                                <SelectItem value="active">Active</SelectItem>
                                <SelectItem value="inactive">Inactive</SelectItem>
                            </SelectContent>
                        </Select>
                        <Select
                            value={filters.role ?? EMPTY_SENTINEL}
                            onValueChange={(value) =>
                                setFilter('role', value === EMPTY_SENTINEL ? null : value)
                            }
                        >
                            <SelectTrigger className="h-11 w-52 bg-background">
                                <SelectValue placeholder="All roles" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value={EMPTY_SENTINEL}>All roles</SelectItem>
                                {roles.map((role) => (
                                    <SelectItem key={role.name} value={role.name}>
                                        {role.name}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>
                </div>

                <UserTable users={users.data} />

                {users.last_page > 1 && users.links && (
                    <div className="flex items-center justify-center gap-1">
                        {users.links.map((link: PaginationLink, index: number) =>
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
