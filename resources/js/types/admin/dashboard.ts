export interface AdminDashboardOverview {
    revenue_last_30_days: number;
    orders_last_30_days: number;
    pending_fulfillment_orders: number;
    low_stock_items: number;
    active_coupons: number;
    new_customers_last_30_days: number;
}

export interface AdminDashboardRecentOrder {
    id: number;
    order_number: string;
    email: string | null;
    status: string;
    payment_status: string;
    fulfillment_status: string;
    total_amount: number;
    currency_code: string;
    placed_at: string | null;
}

export interface AdminDashboardRecentPayment {
    id: number;
    reference: string;
    provider: string;
    status: string;
    amount: number;
    currency_code: string;
    order: {
        id: number;
        order_number: string;
    } | null;
    paid_at: string | null;
    created_at: string | null;
}

export interface AdminDashboardRecentShipment {
    id: number;
    tracking_number: string | null;
    carrier_name: string | null;
    status: string;
    order: {
        id: number;
        order_number: string;
    } | null;
    shipped_at: string | null;
    created_at: string | null;
}
