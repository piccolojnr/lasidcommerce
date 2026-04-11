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

export interface ProductImage {
    id: number;
    url: string;
    is_primary: boolean;
}

export interface AdminProduct {
    id: number;
    name: string;
    slug: string;
    sku: string;
    status: string;
    product_type: string;
    base_price: number;
    compare_at_price: number | null;
    cost_price: number | null;
    is_featured: boolean;
    track_inventory: boolean;
    allow_backorders: boolean;
    published_at: string | null;
    short_description: string | null;
    description: string | null;
    category_id: number | null;
    category_name: string | null;
    brand_id: number | null;
    brand_name: string | null;
    variants_count: number;
    images: ProductImage[];
    created_at: string;
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
