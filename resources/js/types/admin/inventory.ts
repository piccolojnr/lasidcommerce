import type { PaginationLink } from '@/types/shared/pagination';

export interface AdminStockItemSummary {
    id: number;
    product_id: number | null;
    product_name: string | null;
    product_sku: string | null;
    product_variant_id: number | null;
    variant_name: string | null;
    variant_sku: string | null;
    quantity_on_hand: number;
    quantity_reserved: number;
    available_quantity: number;
    reorder_level: number | null;
    status: string;
    is_low_stock: boolean;
    updated_at: string | null;
}

export interface AdminStockMovementSummary {
    id: number;
    stock_item_id: number;
    type: string;
    quantity: number;
    stock_delta: number;
    reference_type: string | null;
    reference_id: number | null;
    note: string | null;
    creator_name: string | null;
    product_name: string | null;
    product_sku: string | null;
    variant_name: string | null;
    variant_sku: string | null;
    created_at: string | null;
}

export interface AdminStockItemDetail extends AdminStockItemSummary {
    movements: AdminStockMovementSummary[];
}

export type AdminStockMovementDetail = AdminStockMovementSummary;

export interface AdminInventoryListPage<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    links: PaginationLink[];
}
