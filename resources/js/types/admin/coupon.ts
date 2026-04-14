import type { PaginationMeta } from '@/types/shared/pagination';

export interface AdminCoupon {
    id: number;
    code: string;
    type: string;
    value: number;
    minimum_order_amount: number | null;
    maximum_discount_amount: number | null;
    usage_limit: number | null;
    used_count: number;
    is_active: boolean;
    is_currently_valid: boolean;
    starts_at: string | null;
    expires_at: string | null;
    created_at: string | null;
}

export interface AdminCouponDetail extends AdminCoupon {
    updated_at: string | null;
}

export interface AdminCouponListPage {
    data: AdminCoupon[];
    current_page: number;
    from: number | null;
    last_page: number;
    path: string;
    per_page: number;
    to: number | null;
    total: number;
    links: NonNullable<PaginationMeta['links']>;
}
