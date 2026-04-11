import type { PaginationLink } from '@/types/shared/pagination';

export interface AdminCategory {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    is_active: boolean;
    sort_order: number;
    parent_id: number | null;
    parent_name: string | null;
    depth: number;
    image_url: string | null;
    children_count: number;
    created_at: string;
}

export interface AdminBrand {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    is_active: boolean;
    image_url: string | null;
    products_count: number;
    created_at: string;
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
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    links: PaginationLink[];
}
