import { Link } from '@inertiajs/react';
import * as UserController from '@/actions/App/Http/Controllers/Admin/Users/UserController';
import { EmptyState } from '@/components/shared/empty-state/empty-state';
import { StatusBadge } from '@/components/shared/status-badge/status-badge';
import { Button } from '@/components/ui/button';
import { formatDate } from '@/lib/formatters/date';
import type { AdminUser } from '@/types/admin/user';

interface UserTableProps {
    users: AdminUser[];
}

export function UserTable({ users }: UserTableProps) {
    if (users.length === 0) {
        return (
            <div className="rounded-3xl border border-dashed border-border/70 bg-muted/30 px-6 py-14 text-center">
                <EmptyState
                    title="No platform users found"
                    description="Try a different search, status, or role filter."
                />
            </div>
        );
    }

    return (
        <div className="overflow-hidden rounded-3xl border border-border/70 bg-background shadow-sm">
            <div className="overflow-x-auto">
                <table className="min-w-full text-sm">
                    <thead className="bg-muted/35">
                        <tr>
                            <th className="px-5 py-4 text-left font-medium text-muted-foreground">
                                Platform user
                            </th>
                            <th className="px-5 py-4 text-left font-medium text-muted-foreground">
                                Access
                            </th>
                            <th className="px-5 py-4 text-left font-medium text-muted-foreground">
                                Activity
                            </th>
                            <th className="px-5 py-4 text-left font-medium text-muted-foreground">
                                Created
                            </th>
                            <th className="px-5 py-4 text-left font-medium text-muted-foreground">
                                Action
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        {users.map((user) => (
                            <tr
                                key={user.id}
                                className="border-t border-border/60 align-top"
                            >
                                <td className="px-5 py-4">
                                    <div className="space-y-1">
                                        <Link
                                            href={UserController.show.url(user)}
                                            className="font-semibold text-foreground transition hover:text-primary"
                                        >
                                            {user.name}
                                        </Link>
                                        <p className="text-xs text-muted-foreground">
                                            {user.email}
                                        </p>
                                    </div>
                                </td>
                                <td className="px-5 py-4">
                                    <div className="space-y-2">
                                        <StatusBadge status={user.status} />
                                        <p className="text-xs text-muted-foreground">
                                            {user.roles.length > 0
                                                ? user.roles.join(', ')
                                                : 'No roles assigned'}
                                        </p>
                                    </div>
                                </td>
                                <td className="px-5 py-4 text-muted-foreground">
                                    <div className="space-y-1 text-xs">
                                        <p>{user.orders_count} orders</p>
                                    </div>
                                </td>
                                <td className="px-5 py-4 text-muted-foreground">
                                    {formatDate(user.created_at)}
                                </td>
                                <td className="px-5 py-4">
                                    <Button variant="outline" size="sm" asChild>
                                        <Link
                                            href={UserController.show.url(user)}
                                        >
                                            View account
                                        </Link>
                                    </Button>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </div>
    );
}
