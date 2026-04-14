import { AppContent } from '@/components/app-content';
import { AppShell } from '@/components/app-shell';
import { AppSidebar } from '@/components/app-sidebar';
import { AppSidebarHeader } from '@/components/app-sidebar-header';
import type { AppLayoutProps } from '@/types';

export default function AppSidebarLayout({
    children,
    breadcrumbs = [],
}: AppLayoutProps) {
    return (
        <AppShell variant="sidebar">
            <AppSidebar />
            <AppContent
                variant="sidebar"
                className="overflow-y-none relative h-[90vh] overflow-x-hidden"
            >
                <AppSidebarHeader breadcrumbs={breadcrumbs} />
                <div className="px-4 py-6 lg:px-8">{children}</div>
            </AppContent>
        </AppShell>
    );
}
