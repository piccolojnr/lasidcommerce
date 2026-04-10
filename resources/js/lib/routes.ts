export const adminRoutes = {
    dashboard: '/admin/dashboard',
    catalog: {
        categories: '/admin/catalog/categories',
        brands: '/admin/catalog/brands',
        products: '/admin/catalog/products',
    },
    orders: '/admin/orders',
    shipments: '/admin/shipments',
    coupons: '/admin/coupons',
    users: '/admin/users',
    settings: '/admin/settings',
} as const;
