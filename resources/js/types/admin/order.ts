import type { PaginationMeta } from '@/types/shared/pagination';

export interface AdminOrder {
    id: number;
    order_number: string;
    email: string;
    status: string;
    payment_status: string;
    fulfillment_status: string;
    currency_code: string;
    total_amount: number;
    placed_at?: string | null;
}

export interface AdminOrderItem {
    id: number;
    product_name: string;
    variant_name: string | null;
    sku: string | null;
    unit_price: number;
    quantity: number;
    discount_amount: number;
    tax_amount: number;
    line_total: number;
}

export interface AdminOrderAddress {
    type: string;
    name: string;
    phone: string | null;
    country: string;
    region: string | null;
    city: string;
    district: string | null;
    address_line_1: string;
    address_line_2: string | null;
    landmark: string | null;
    postal_code: string | null;
}

export interface AdminOrderPayment {
    id: number;
    provider: string;
    reference: string;
    status: string;
    amount: number;
    currency_code: string;
    paid_at: string | null;
    failed_at: string | null;
}

export interface AdminOrderShipment {
    id: number;
    status: string;
    carrier_name: string | null;
    tracking_number: string | null;
    tracking_url: string | null;
    rider_name: string | null;
    rider_phone: string | null;
    packed_at: string | null;
    shipped_at: string | null;
    delivered_at: string | null;
    failed_at: string | null;
    returned_at: string | null;
}

export interface AdminOrderHistoryEntry {
    id: number;
    from_status: string | null;
    to_status: string;
    note: string | null;
    changed_by_name: string | null;
    created_at: string | null;
}

export interface AdminOrderDetail extends AdminOrder {
    subtotal_amount: number;
    discount_amount: number;
    tax_amount: number;
    shipping_amount: number;
    shipping_zone_name: string | null;
    shipping_method_name: string | null;
    notes: string | null;
    delivery_notes: string | null;
    shipping_address: AdminOrderAddress | null;
    items: AdminOrderItem[];
    payments: AdminOrderPayment[];
    shipments: AdminOrderShipment[];
    history: AdminOrderHistoryEntry[];
}

export interface AdminOrderListPage {
    data: AdminOrder[];
    current_page: number;
    from: number | null;
    last_page: number;
    path: string;
    per_page: number;
    to: number | null;
    total: number;
    links: PaginationMeta['links'];
}
