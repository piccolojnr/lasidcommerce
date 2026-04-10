export const ADMIN_PERMISSIONS = {
    VIEW_DASHBOARD: 'view admin dashboard',
    MANAGE_CATEGORIES: 'manage categories',
    MANAGE_BRANDS: 'manage brands',
    MANAGE_PRODUCTS: 'manage products',
    MANAGE_ORDERS: 'manage orders',
    MANAGE_SHIPMENTS: 'manage shipments',
    MANAGE_COUPONS: 'manage coupons',
    MANAGE_USERS: 'manage users',
    MANAGE_SETTINGS: 'manage settings',
} as const;

export type AdminPermission = (typeof ADMIN_PERMISSIONS)[keyof typeof ADMIN_PERMISSIONS];

export function hasPermission(permissions: string[] | undefined, permission: AdminPermission): boolean {
    return permissions?.includes(permission) ?? false;
}
