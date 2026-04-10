import type { PaginationMeta } from '@/types/shared/pagination';

export interface AdminCoupon {
    id: number;
    code: string;
    type: string;
    value: number;
    is_active: boolean;
    expires_at?: string | null;
}

export interface AdminCouponListPage {
    data: AdminCoupon[];
    meta?: PaginationMeta;
}
