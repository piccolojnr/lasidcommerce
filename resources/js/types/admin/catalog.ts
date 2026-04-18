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

export interface AdminTag {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    is_active: boolean;
    products_count: number;
    created_at: string;
}

export interface AdminCollectionProduct {
    id: number;
    name: string;
    sku: string;
    status: string;
    sort_order: number;
    primary_image_url: string | null;
    category_name: string | null;
    brand_name: string | null;
}

export interface AdminCollection {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    is_active: boolean;
    sort_order: number;
    products_count: number;
    products: AdminCollectionProduct[];
    created_at: string;
}

export interface AdminMerchandisingBadge {
    key: string;
    label: string;
}

export interface AdminProductTagSummary {
    id: number;
    name: string;
    slug: string;
}

export interface AdminProductCollectionSummary {
    id: number;
    name: string;
    slug: string;
    pivot_sort_order: number;
}

export interface ProductImage {
    id: number;
    url: string;
    thumb_url: string;
    card_url: string;
    gallery_url: string;
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
    tags: AdminProductTagSummary[];
    collections: AdminProductCollectionSummary[];
    badges: AdminMerchandisingBadge[];
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
