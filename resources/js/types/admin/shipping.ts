import type { PaginationMeta } from '@/types/shared/pagination';

export interface AdminShippingZone {
    id: number;
    name: string;
    code: string;
    description: string | null;
    country_code: string;
    is_active: boolean;
    areas_count: number;
    shipping_methods_count: number;
    orders_count: number;
    created_at: string | null;
}

export interface AdminShippingZoneArea {
    id: number;
    area_type: string;
    area_name: string;
    created_at: string | null;
}

export interface AdminShippingMethodSummary {
    id: number;
    name: string;
    code: string;
    method_type: string;
    price_type: string;
    flat_rate_amount: number | null;
    min_delivery_days: number | null;
    max_delivery_days: number | null;
    description?: string | null;
    is_active: boolean;
}

export interface AdminShippingMethodDetail extends AdminShippingMethodSummary {
    created_at: string | null;
    updated_at: string | null;
    zone: Pick<AdminShippingZone, 'id' | 'name' | 'code'>;
}

export interface AdminShippingZoneDetail extends AdminShippingZone {
    updated_at: string | null;
    areas: AdminShippingZoneArea[];
    shipping_methods: AdminShippingMethodSummary[];
}

export interface AdminShippingZoneAreaDetail extends AdminShippingZoneArea {
    updated_at: string | null;
    zone: Pick<AdminShippingZone, 'id' | 'name' | 'code'>;
}

export interface AdminWarehouse {
    id: number;
    name: string;
    code: string;
    country: string;
    region: string | null;
    city: string;
    address_line_1: string;
    address_line_2: string | null;
    phone: string | null;
    email: string | null;
    is_active: boolean;
    is_default: boolean;
    shipments_count: number;
    created_at: string | null;
}

export interface AdminWarehouseDetail extends AdminWarehouse {
    updated_at: string | null;
}

export interface AdminShippingListPage<T> {
    data: T[];
    current_page: number;
    from: number | null;
    last_page: number;
    path: string;
    per_page: number;
    to: number | null;
    total: number;
    links: NonNullable<PaginationMeta['links']>;
}
