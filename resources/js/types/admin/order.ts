import type { PaginationMeta } from '@/types/shared/pagination';

export interface AdminOrder {
    id: number;
    order_number: string;
    email: string;
    status: string;
    payment_status: string;
    fulfillment_status: string;
    total_amount: number;
    placed_at?: string | null;
}

export interface AdminOrderListPage {
    data: AdminOrder[];
    meta?: PaginationMeta;
}
