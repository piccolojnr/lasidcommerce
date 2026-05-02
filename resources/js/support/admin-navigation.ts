import {
    BadgePercent,
    Boxes,
    ContactRound,
    LayoutGrid,
    MapPinned,
    Package,
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
        title: 'Inventory',
        href: '/admin/inventory/stock-items',
        icon: Package,
    },
    {
        title: 'Platform Users',
        href: '/admin/users',
        icon: Users,
    },
    {
        title: 'Customers',
        href: '/admin/customers',
        icon: ContactRound,
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
                    {
                        title: 'Tags',
                        href: '/admin/catalog/tags',
                    },
                    {
                        title: 'Collections',
                        href: '/admin/catalog/collections',
                    },
                ],
            },
        ],
    },
    {
        label: 'Inventory',
        items: [
            {
                title: 'Inventory',
                href: '/admin/inventory/stock-items',
                icon: Package,
                items: [
                    {
                        title: 'Stock Items',
                        href: '/admin/inventory/stock-items',
                    },
                    {
                        title: 'Movement Ledger',
                        href: '/admin/inventory/stock-movements',
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
                title: 'Shipping Setup',
                href: '/admin/shipping/zones',
                icon: MapPinned,
                items: [
                    {
                        title: 'Shipping Zones',
                        href: '/admin/shipping/zones',
                    },
                    {
                        title: 'Shipping Methods',
                        href: '/admin/shipping/methods',
                    },
                    {
                        title: 'Warehouses',
                        href: '/admin/shipping/warehouse-locations',
                    },
                ],
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
                title: 'Platform Users',
                href: '/admin/users',
                icon: Users,
            },
            {
                title: 'Customers',
                href: '/admin/customers',
                icon: ContactRound,
            },
            {
                title: 'Settings',
                href: '/admin/settings',
                icon: ShieldCheck,
            },
        ],
    },
];
