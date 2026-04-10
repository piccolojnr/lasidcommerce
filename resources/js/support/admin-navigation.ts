import {
    BadgePercent,
    Boxes,
    LayoutGrid,
    Package,
    Settings,
    ShoppingCart,
    Truck,
    Users,
} from 'lucide-react';
import type { NavItem } from '@/types';

export const adminNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/admin/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Products',
        href: '/admin/catalog/products',
        icon: Package,
    },
    {
        title: 'Categories',
        href: '/admin/catalog/categories',
        icon: Boxes,
    },
    {
        title: 'Brands',
        href: '/admin/catalog/brands',
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
        title: 'Coupons',
        href: '/admin/coupons',
        icon: BadgePercent,
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
