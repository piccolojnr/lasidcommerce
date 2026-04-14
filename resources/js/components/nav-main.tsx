import { Link } from '@inertiajs/react';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarMenuSub,
    SidebarMenuSubButton,
    SidebarMenuSubItem,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/hooks/use-current-url';
import { cn } from '@/lib/utils';
import type { SidebarNavGroup } from '@/support/admin-navigation';

export function NavMain({ groups = [] }: { groups: SidebarNavGroup[] }) {
    const { isCurrentOrParentUrl } = useCurrentUrl();

    return (
        <>
            {groups.map((group) => (
                <SidebarGroup key={group.label} className="px-2 py-0">
                    <SidebarGroupLabel>{group.label}</SidebarGroupLabel>
                    <SidebarMenu>
                        {group.items.map((item) => {
                            const hasChildren = Boolean(
                                item.items && item.items.length > 0,
                            );
                            const childIsActive =
                                item.items?.some((subItem) =>
                                    isCurrentOrParentUrl(subItem.href),
                                ) ?? false;
                            const itemIsActive =
                                isCurrentOrParentUrl(item.href) ||
                                childIsActive;

                            return (
                                <SidebarMenuItem key={item.title}>
                                    <SidebarMenuButton
                                        asChild
                                        isActive={itemIsActive}
                                        tooltip={{ children: item.title }}
                                        className={cn(
                                            hasChildren && itemIsActive
                                                ? 'bg-primary/10!'
                                                : '',
                                            hasChildren ? 'mb-4' : '',
                                        )}
                                    >
                                        <Link href={item.href} prefetch>
                                            {item.icon && <item.icon />}
                                            <span>{item.title}</span>
                                        </Link>
                                    </SidebarMenuButton>
                                    {hasChildren && (
                                        <SidebarMenuSub>
                                            {item.items!.map((subItem) => (
                                                <SidebarMenuSubItem
                                                    key={subItem.title}
                                                >
                                                    <SidebarMenuSubButton
                                                        asChild
                                                        isActive={isCurrentOrParentUrl(
                                                            subItem.href,
                                                        )}
                                                    >
                                                        <Link
                                                            href={subItem.href}
                                                            prefetch
                                                        >
                                                            <span>
                                                                {subItem.title}
                                                            </span>
                                                        </Link>
                                                    </SidebarMenuSubButton>
                                                </SidebarMenuSubItem>
                                            ))}
                                        </SidebarMenuSub>
                                    )}
                                </SidebarMenuItem>
                            );
                        })}
                    </SidebarMenu>
                </SidebarGroup>
            ))}
        </>
    );
}
