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
    products: AdminTagProduct[];
    created_at: string;
}

export interface AdminTagProduct {
    id: number;
    name: string;
    sku: string;
    status: string;
    primary_image_url: string | null;
    category_name: string | null;
    brand_name: string | null;
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

export interface AdminStockMovementRecord {
    id: number;
    type: string;
    quantity: number;
    stock_delta: number;
    note: string | null;
    creator_name: string | null;
    created_at: string | null;
}

export interface AdminStockItemDetail {
    id: number;
    quantity_on_hand: number;
    quantity_reserved: number;
    available_quantity: number;
    reorder_level: number | null;
    movements: AdminStockMovementRecord[];
}

export interface AdminProductInventorySummary {
    stock_item_count: number;
    primary_stock_item_id: number | null;
    quantity_on_hand: number;
    quantity_reserved: number;
    available_quantity: number;
    reorder_level: number;
    status: string;
    is_backorderable: boolean;
    /** Only present on the product show page (withMovements=true). Null elsewhere. */
    stock_items: AdminStockItemDetail[] | null;
}

export interface AdminProductOptionValue {
    id: number;
    value: string;
}

export interface AdminProductOptionType {
    id: number;
    name: string;
    values: AdminProductOptionValue[];
}

export interface AdminProductVariantOptionValue {
    id: number;
    value: string;
    option_type_id: number;
    option_type_name: string | null;
}

export interface AdminProductVariant {
    id: number;
    name: string;
    sku: string;
    price: number | null;
    compare_at_price: number | null;
    cost_price: number | null;
    barcode: string | null;
    weight: string | null;
    is_active: boolean;
    option_value_ids: number[];
    option_values: AdminProductVariantOptionValue[];
    inventory: AdminProductInventorySummary;
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
    inventory: AdminProductInventorySummary;
    variants_count: number;
    option_types: AdminProductOptionType[];
    variants: AdminProductVariant[];
    images: ProductImage[];
    created_at: string;
}

export interface AdminBulkEditableProduct {
    id: number;
    name: string;
    sku: string;
    status: string;
    product_type: string;
    category_id: number | null;
    category_name: string | null;
    brand_id: number | null;
    brand_name: string | null;
    base_price: number;
    compare_at_price: number | null;
    cost_price: number | null;
    track_inventory: boolean;
    allow_backorders: boolean;
    is_featured: boolean;
    quantity_on_hand: number | null;
    reorder_level: number | null;
    reserved_quantity: number;
    stock_item_count: number;
    variants_count: number;
    image_count: number;
    primary_image_thumb_url: string | null;
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
