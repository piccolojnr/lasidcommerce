import type { PaginationMeta } from '@/types/shared/pagination';

export interface AdminCategory {
    id: number;
    name: string;
    slug: string;
    is_active: boolean;
    sort_order: number;
    parent_name?: string | null;
}

export interface AdminBrand {
    id: number;
    name: string;
    slug: string;
    is_active: boolean;
}

export interface AdminProduct {
    id: number;
    name: string;
    slug: string;
    sku: string;
    status: string;
    product_type: string;
    base_price: number;
    is_featured: boolean;
}

export interface AdminCatalogListPage<T> {
    data: T[];
    meta?: PaginationMeta;
}
