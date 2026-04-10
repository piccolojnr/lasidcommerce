import type { ReactNode } from 'react';
import { SidebarInset, SidebarProvider } from '@/components/ui/sidebar';
import { AdminHeader } from '@/layouts/app/admin-header';
import { AdminSidebar } from '@/layouts/app/admin-sidebar';

interface AdminShellProps {
    title?: string;
    description?: string;
    children: ReactNode;
}

export function AdminShell({ title, description, children }: AdminShellProps) {
    return (
        <SidebarProvider>
            <AdminSidebar />
            <SidebarInset>
                <AdminHeader title={title} description={description} />
                <div className="flex-1 px-4 py-6 sm:px-6">{children}</div>
            </SidebarInset>
        </SidebarProvider>
    );
}
