import { Link, usePage } from '@inertiajs/react';
import { ChevronRight } from 'lucide-react';
import {
    Sidebar,
    SidebarContent,
    SidebarGroup,
    SidebarGroupContent,
    SidebarGroupLabel,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarMenuSub,
    SidebarMenuSubButton,
    SidebarMenuSubItem,
} from '@/components/ui/sidebar';
import { usePermission } from '@/hooks/shared/use-permission';
import { adminNavigation } from '@/lib/nav';

interface UrlPageProps {
    url: string;
    [key: string]: unknown;
}

export function AdminSidebar() {
    const { url } = usePage<UrlPageProps>();
    const { can } = usePermission();

    return (
        <Sidebar>
            <SidebarHeader className="border-b px-4 py-5">
                <div className="space-y-1">
                    <p className="text-xs tracking-[0.24em] text-muted-foreground uppercase">
                        Internal
                    </p>
                    <p className="text-lg font-semibold">Admin Console</p>
                </div>
            </SidebarHeader>
            <SidebarContent>
                <SidebarGroup>
                    <SidebarGroupLabel>Navigation</SidebarGroupLabel>
                    <SidebarGroupContent>
                        <SidebarMenu>
                            {adminNavigation.map((item) => {
                                if (item.permission && !can(item.permission)) {
                                    return null;
                                }

                                const isActive = item.href
                                    ? url.startsWith(item.href)
                                    : false;
                                const Icon = item.icon;

                                return (
                                    <SidebarMenuItem key={item.title}>
                                        {item.href ? (
                                            <SidebarMenuButton
                                                asChild
                                                isActive={isActive}
                                            >
                                                <Link href={item.href}>
                                                    {Icon ? (
                                                        <Icon className="size-4" />
                                                    ) : null}
                                                    <span>{item.title}</span>
                                                </Link>
                                            </SidebarMenuButton>
                                        ) : (
                                            <SidebarMenuButton
                                                isActive={isActive}
                                            >
                                                {Icon ? (
                                                    <Icon className="size-4" />
                                                ) : null}
                                                <span>{item.title}</span>
                                                <ChevronRight className="ml-auto size-4 text-muted-foreground" />
                                            </SidebarMenuButton>
                                        )}
                                        {item.children ? (
                                            <SidebarMenuSub>
                                                {item.children.map((child) => {
                                                    if (
                                                        child.permission &&
                                                        !can(child.permission)
                                                    ) {
                                                        return null;
                                                    }

                                                    return (
                                                        <SidebarMenuSubItem
                                                            key={child.title}
                                                        >
                                                            <SidebarMenuSubButton
                                                                asChild
                                                                isActive={url.startsWith(
                                                                    child.href ??
                                                                        '',
                                                                )}
                                                            >
                                                                <Link
                                                                    href={
                                                                        child.href ??
                                                                        '#'
                                                                    }
                                                                >
                                                                    {
                                                                        child.title
                                                                    }
                                                                </Link>
                                                            </SidebarMenuSubButton>
                                                        </SidebarMenuSubItem>
                                                    );
                                                })}
                                            </SidebarMenuSub>
                                        ) : null}
                                    </SidebarMenuItem>
                                );
                            })}
                        </SidebarMenu>
                    </SidebarGroupContent>
                </SidebarGroup>
            </SidebarContent>
        </Sidebar>
    );
}
