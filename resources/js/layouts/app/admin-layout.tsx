import type { ReactNode } from 'react';
import { Head } from '@inertiajs/react';
import { AdminShell } from '@/layouts/app/admin-shell';

interface AdminLayoutProps {
    title: string;
    description?: string;
    children: ReactNode;
}

export function AdminLayout({ title, description, children }: AdminLayoutProps) {
    return (
        <>
            <Head title={title} />
            <AdminShell title={title} description={description}>
                {children}
            </AdminShell>
        </>
    );
}
