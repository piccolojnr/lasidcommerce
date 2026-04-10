import { SidebarTrigger } from '@/components/ui/sidebar';

interface AdminHeaderProps {
    title?: string;
    description?: string;
}

export function AdminHeader({ title = 'Admin', description }: AdminHeaderProps) {
    return (
        <header className="sticky top-0 z-20 border-b bg-background/95 backdrop-blur">
            <div className="flex items-center gap-3 px-4 py-3 sm:px-6">
                <SidebarTrigger />
                <div className="min-w-0">
                    <p className="truncate text-sm font-semibold">{title}</p>
                    {description ? <p className="truncate text-xs text-muted-foreground">{description}</p> : null}
                </div>
            </div>
        </header>
    );
}
