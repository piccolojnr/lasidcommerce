import type { PaginationMeta } from '@/types/shared/pagination';

export interface AdminShipment {
    id: number;
    status: string;
    tracking_number?: string | null;
    carrier_name?: string | null;
    shipped_at?: string | null;
    delivered_at?: string | null;
}

export interface AdminShipmentListPage {
    data: AdminShipment[];
    meta?: PaginationMeta;
}
