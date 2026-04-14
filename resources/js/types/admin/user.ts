import type { PaginationMeta } from '@/types/shared/pagination';

export interface AdminUser {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    status: string;
    roles: string[];
    orders_count: number;
    payments_count: number;
    created_at: string | null;
}

export interface AdminUserOrderSummary {
    id: number;
    order_number: string;
    status: string;
    total_amount: number;
    currency_code: string;
    placed_at: string | null;
}

export interface AdminUserPaymentSummary {
    id: number;
    reference: string;
    provider: string;
    status: string;
    amount: number;
    currency_code: string;
    paid_at: string | null;
}

export interface AdminUserDetail extends AdminUser {
    email_verified_at: string | null;
    two_factor_confirmed_at: string | null;
    addresses_count: number;
    recent_orders: AdminUserOrderSummary[];
    recent_payments: AdminUserPaymentSummary[];
}

export interface AdminUserListPage {
    data: AdminUser[];
    current_page: number;
    from: number | null;
    last_page: number;
    path: string;
    per_page: number;
    to: number | null;
    total: number;
    links: PaginationMeta['links'];
}
