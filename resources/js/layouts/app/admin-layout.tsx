import { Head } from '@inertiajs/react';
import type { ReactNode } from 'react';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/support/app-routes';
import type { BreadcrumbItem } from '@/types';

interface AdminLayoutProps {
    title: string;
    description?: string;
    children: ReactNode;
}

export function AdminLayout({ title, children }: AdminLayoutProps) {
    const breadcrumbs: BreadcrumbItem[] =
        title === 'Dashboard'
            ? [{ title: 'Dashboard', href: dashboard() }]
            : [
                  { title: 'Dashboard', href: dashboard() },
                  { title, href: '#' },
              ];

    return (
        <>
            <Head title={title} />
            <AppLayout breadcrumbs={breadcrumbs}>{children}</AppLayout>
        </>
    );
}
