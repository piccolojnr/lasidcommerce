import { Link } from '@inertiajs/react';
import * as UserController from '@/actions/App/Http/Controllers/Admin/Users/UserController';
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
    const { search, setSearch, setFilter } = useFilters(UserController.index.url(), filters);

    return (
        <AdminLayout title="Users" description="Review internal and customer-facing user accounts.">
            <div className="mx-auto w-full max-w-6xl space-y-6">
                <PageHeader
                    title="Users"
                    description="Monitor account status and manage role assignment."
                    actions={
                        <Link
                            href={UserController.index.url()}
                            className="text-sm text-muted-foreground hover:text-foreground"
                        >
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
                        onValueChange={(value) =>
                            setFilter('status', value === EMPTY_SENTINEL ? null : value)
                        }
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
                    <Select
                        value={filters.role ?? EMPTY_SENTINEL}
                        onValueChange={(value) =>
                            setFilter('role', value === EMPTY_SENTINEL ? null : value)
                        }
                    >
                        <SelectTrigger className="w-52">
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
