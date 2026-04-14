export const adminRoutes = {
    dashboard: '/admin/dashboard',
    catalog: {
        categories: '/admin/catalog/categories',
        brands: '/admin/catalog/brands',
        products: '/admin/catalog/products',
    },
    orders: '/admin/orders',
    shipments: '/admin/shipments',
    shipping: {
        zones: '/admin/shipping/zones',
        warehouses: '/admin/shipping/warehouse-locations',
    },
    coupons: '/admin/coupons',
    users: '/admin/users',
    customers: '/admin/customers',
    settings: '/admin/settings',
} as const;
