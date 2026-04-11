import {
    BadgePercent,
    Boxes,
    LayoutGrid,
    Settings,
    ShieldCheck,
    ShoppingCart,
    Truck,
    Users,
} from 'lucide-react';
import type { NavItem } from '@/types';

export type SidebarNavGroup = {
    label: string;
    items: Array<
        NavItem & {
            items?: NavItem[];
        }
    >;
};

export const adminHeaderNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/admin/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Catalog',
        href: '/admin/catalog/products',
        icon: Boxes,
    },
    {
        title: 'Orders',
        href: '/admin/orders',
        icon: ShoppingCart,
    },
    {
        title: 'Shipments',
        href: '/admin/shipments',
        icon: Truck,
    },
    {
        title: 'Users',
        href: '/admin/users',
        icon: Users,
    },
    {
        title: 'Settings',
        href: '/admin/settings',
        icon: Settings,
    },
];

export const adminSidebarGroups: SidebarNavGroup[] = [
    {
        label: 'Overview',
        items: [
            {
                title: 'Dashboard',
                href: '/admin/dashboard',
                icon: LayoutGrid,
            },
        ],
    },
    {
        label: 'Catalog',
        items: [
            {
                title: 'Catalog',
                href: '/admin/catalog/products',
                icon: Boxes,
                items: [
                    {
                        title: 'Products',
                        href: '/admin/catalog/products',
                    },
                    {
                        title: 'Categories',
                        href: '/admin/catalog/categories',
                    },
                    {
                        title: 'Brands',
                        href: '/admin/catalog/brands',
                    },
                ],
            },
        ],
    },
    {
        label: 'Commerce',
        items: [
            {
                title: 'Orders',
                href: '/admin/orders',
                icon: ShoppingCart,
            },
            {
                title: 'Shipments',
                href: '/admin/shipments',
                icon: Truck,
            },
            {
                title: 'Coupons',
                href: '/admin/coupons',
                icon: BadgePercent,
            },
        ],
    },
    {
        label: 'Administration',
        items: [
            {
                title: 'Users',
                href: '/admin/users',
                icon: Users,
            },
            {
                title: 'Settings',
                href: '/admin/settings',
                icon: ShieldCheck,
            },
        ],
    },
];
