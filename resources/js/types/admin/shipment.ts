import type { PaginationMeta } from '@/types/shared/pagination';

export interface AdminShipment {
    id: number;
    status: string;
    tracking_number: string | null;
    carrier_name: string | null;
    order: {
        id: number;
        order_number: string;
        email: string;
    } | null;
    packed_at: string | null;
    shipped_at: string | null;
    delivered_at: string | null;
    failed_at: string | null;
    returned_at: string | null;
}

export interface AdminShipmentItem {
    id: number;
    quantity: number;
    order_item_id: number;
    product_name: string | null;
    variant_name: string | null;
    sku: string | null;
}

export interface AdminShipmentDetail extends AdminShipment {
    tracking_url: string | null;
    rider_name: string | null;
    rider_phone: string | null;
    notes: string | null;
    order_fulfillment_status: string | null;
    order_shipping_summary: string | null;
    order_remaining_quantity: number;
    can_reship_from_order: boolean;
    warehouse_location: {
        name: string;
        code: string;
        city: string | null;
        region: string | null;
        country: string;
    } | null;
    shipping_method: {
        name: string;
        code: string;
        method_type: string;
    } | null;
    items: AdminShipmentItem[];
}

export interface AdminShipmentListPage {
    data: AdminShipment[];
    current_page: number;
    from: number | null;
    last_page: number;
    path: string;
    per_page: number;
    to: number | null;
    total: number;
    links: PaginationMeta['links'];
}
