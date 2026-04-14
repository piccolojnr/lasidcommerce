import type { LucideIcon } from 'lucide-react';
import {
    Boxes,
    ContactRound,
    LayoutDashboard,
    PackageCheck,
    ReceiptText,
    Settings,
    Tag,
    Users,
} from 'lucide-react';
import { ADMIN_PERMISSIONS } from '@/lib/permissions';
import type { AdminPermission } from '@/lib/permissions';
import { adminRoutes } from '@/lib/routes';

export interface AdminNavItem {
    title: string;
    href?: string;
    icon?: LucideIcon;
    permission?: AdminPermission;
    children?: AdminNavItem[];
}

export const adminNavigation: AdminNavItem[] = [
    {
        title: 'Dashboard',
        href: adminRoutes.dashboard,
        icon: LayoutDashboard,
        permission: ADMIN_PERMISSIONS.VIEW_DASHBOARD,
    },
    {
        title: 'Catalog',
        icon: Boxes,
        children: [
            {
                title: 'Categories',
                href: adminRoutes.catalog.categories,
                permission: ADMIN_PERMISSIONS.MANAGE_CATEGORIES,
            },
            {
                title: 'Brands',
                href: adminRoutes.catalog.brands,
                permission: ADMIN_PERMISSIONS.MANAGE_BRANDS,
            },
            {
                title: 'Products',
                href: adminRoutes.catalog.products,
                permission: ADMIN_PERMISSIONS.MANAGE_PRODUCTS,
            },
        ],
    },
    {
        title: 'Orders',
        href: adminRoutes.orders,
        icon: ReceiptText,
        permission: ADMIN_PERMISSIONS.MANAGE_ORDERS,
    },
    {
        title: 'Shipments',
        href: adminRoutes.shipments,
        icon: PackageCheck,
        permission: ADMIN_PERMISSIONS.MANAGE_SHIPMENTS,
    },
    {
        title: 'Coupons',
        href: adminRoutes.coupons,
        icon: Tag,
        permission: ADMIN_PERMISSIONS.MANAGE_COUPONS,
    },
    {
        title: 'Platform Users',
        href: adminRoutes.users,
        icon: Users,
        permission: ADMIN_PERMISSIONS.MANAGE_USERS,
    },
    {
        title: 'Customers',
        href: adminRoutes.customers,
        icon: ContactRound,
        permission: ADMIN_PERMISSIONS.MANAGE_USERS,
    },
    {
        title: 'Settings',
        href: adminRoutes.settings,
        icon: Settings,
        permission: ADMIN_PERMISSIONS.MANAGE_SETTINGS,
    },
];
