import { Link } from '@inertiajs/react';
import * as ProductController from '@/actions/App/Http/Controllers/Admin/Catalog/ProductController';
import * as StockItemController from '@/actions/App/Http/Controllers/Admin/Inventory/StockItemController';
import { PageHeader } from '@/components/shared/page-header/page-header';
import { StatusBadge } from '@/components/shared/status-badge/status-badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { AdminLayout } from '@/layouts/app/admin-layout';
import { formatMoney } from '@/lib/formatters/money';
import type { AdminProduct } from '@/types/admin/catalog';

interface Props {
    product: AdminProduct;
}

export default function ProductShowPage({ product }: Props) {
    const primaryImage =
        product.images.find((image) => image.is_primary) ?? product.images[0];
    const margin =
        product.cost_price != null
            ? product.base_price - product.cost_price
            : null;
    const compareAtDelta =
        product.compare_at_price != null
            ? product.compare_at_price - product.base_price
            : null;

    return (
        <AdminLayout title="Product Details">
            <div className="mx-auto w-full max-w-7xl space-y-8">
                <PageHeader
                    title={product.name}
                    description="Review pricing posture, publishing state, imagery, and merchandising context from one screen."
                    actions={
                        <div className="flex items-center gap-2">
                            <Button variant="outline" asChild>
                                <Link href={ProductController.index.url()}>
                                    Back to list
                                </Link>
                            </Button>
                            <Button asChild>
                                <Link
                                    href={ProductController.edit.url(product)}
                                >
                                    Edit
                                </Link>
                            </Button>
                            <Button variant="outline" asChild>
                                <Link
                                    href={
                                        product.inventory.primary_stock_item_id
                                            ? StockItemController.show.url(
                                                  product.inventory
                                                      .primary_stock_item_id,
                                              )
                                            : `${StockItemController.index.url()}?search=${encodeURIComponent(product.sku)}`
                                    }
                                >
                                    Inventory
                                </Link>
                            </Button>
                        </div>
                    }
                />

                <div className="grid gap-4 md:grid-cols-3 xl:grid-cols-4">
                    <Card className="border-border/70 bg-muted/30 md:col-span-2 xl:col-span-2">
                        <CardHeader className="space-y-3">
                            <div className="flex items-center justify-between gap-3">
                                <p className="text-xs font-semibold tracking-[0.24em] text-muted-foreground uppercase">
                                    Catalog posture
                                </p>
                                <StatusBadge status={product.status} />
                            </div>
                            <div className="space-y-2">
                                <CardTitle className="text-2xl">
                                    {formatMoney(product.base_price)}
                                </CardTitle>
                                <p className="max-w-2xl text-sm leading-6 text-muted-foreground">
                                    {product.short_description ??
                                        'No short description has been written for this product yet.'}
                                </p>
                            </div>
                        </CardHeader>
                        <CardContent className="grid gap-4 border-t border-border/70 pt-6 md:grid-cols-3">
                            <div className="space-y-1">
                                <p className="text-xs font-semibold tracking-[0.2em] text-muted-foreground uppercase">
                                    Compare-at gap
                                </p>
                                <p className="text-lg font-semibold text-foreground">
                                    {compareAtDelta != null &&
                                    compareAtDelta > 0
                                        ? formatMoney(compareAtDelta)
                                        : '—'}
                                </p>
                                <p className="text-xs text-muted-foreground">
                                    {product.compare_at_price != null
                                        ? `Against ${formatMoney(product.compare_at_price)}`
                                        : 'No reference price set'}
                                </p>
                            </div>
                            <div className="space-y-1">
                                <p className="text-xs font-semibold tracking-[0.2em] text-muted-foreground uppercase">
                                    Unit margin
                                </p>
                                <p className="text-lg font-semibold text-foreground">
                                    {margin != null ? formatMoney(margin) : '—'}
                                </p>
                                <p className="text-xs text-muted-foreground">
                                    {product.cost_price != null
                                        ? `Cost basis ${formatMoney(product.cost_price)}`
                                        : 'Cost price missing'}
                                </p>
                            </div>
                            <div className="space-y-1">
                                <p className="text-xs font-semibold tracking-[0.2em] text-muted-foreground uppercase">
                                    Variant count
                                </p>
                                <p className="text-lg font-semibold text-foreground">
                                    {product.variants_count}
                                </p>
                                <p className="text-xs text-muted-foreground">
                                    {product.product_type === 'variable'
                                        ? 'Variant-driven merchandising'
                                        : 'Simple product setup'}
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <Card className="overflow-hidden border-border/70 bg-primary/5">
                        <CardHeader className="space-y-2">
                            <p className="text-xs font-semibold tracking-[0.24em] text-primary uppercase">
                                Merchandising
                            </p>
                            <CardTitle className="text-xl">
                                {product.is_featured
                                    ? 'Featured placement'
                                    : 'Standard placement'}
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-3 text-sm">
                            <div className="flex items-center justify-between">
                                <span className="text-muted-foreground">
                                    Brand
                                </span>
                                <span className="font-medium">
                                    {product.brand_name ?? 'Unassigned'}
                                </span>
                            </div>
                            <div className="flex items-center justify-between">
                                <span className="text-muted-foreground">
                                    Category
                                </span>
                                <span className="font-medium">
                                    {product.category_name ?? 'Unassigned'}
                                </span>
                            </div>
                            <div className="flex items-center justify-between">
                                <span className="text-muted-foreground">
                                    Type
                                </span>
                                <span className="font-medium capitalize">
                                    {product.product_type}
                                </span>
                            </div>
                            <div className="space-y-2 pt-2">
                                <span className="text-muted-foreground">
                                    Computed badges
                                </span>
                                <div className="flex flex-wrap gap-2">
                                    {product.badges.length > 0 ? (
                                        product.badges.map((badge) => (
                                            <span
                                                key={badge.key}
                                                className="rounded-full bg-primary/10 px-2.5 py-1 text-xs font-medium text-primary"
                                            >
                                                {badge.label}
                                            </span>
                                        ))
                                    ) : (
                                        <span className="text-sm font-medium">
                                            None
                                        </span>
                                    )}
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card className="overflow-hidden border-border/70 bg-secondary/50">
                        <CardHeader className="space-y-2">
                            <p className="text-xs font-semibold tracking-[0.24em] text-foreground/70 uppercase">
                                Publishing
                            </p>
                            <CardTitle className="text-xl">
                                {product.published_at
                                    ? new Date(
                                          product.published_at,
                                      ).toLocaleDateString()
                                    : 'Not scheduled'}
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-3 text-sm">
                            <div className="flex items-center justify-between">
                                <span className="text-muted-foreground">
                                    Inventory tracking
                                </span>
                                <span className="font-medium">
                                    {product.track_inventory
                                        ? 'Enabled'
                                        : 'Disabled'}
                                </span>
                            </div>
                            <div className="flex items-center justify-between">
                                <span className="text-muted-foreground">
                                    Backorders
                                </span>
                                <span className="font-medium">
                                    {product.allow_backorders
                                        ? 'Allowed'
                                        : 'Blocked'}
                                </span>
                            </div>
                            <div className="flex items-center justify-between">
                                <span className="text-muted-foreground">
                                    Stock status
                                </span>
                                <StatusBadge
                                    status={product.inventory.status}
                                />
                            </div>
                            <div className="flex items-center justify-between">
                                <span className="text-muted-foreground">
                                    Available now
                                </span>
                                <span className="font-medium">
                                    {product.inventory.available_quantity}
                                </span>
                            </div>
                            <div className="flex items-center justify-between">
                                <span className="text-muted-foreground">
                                    Image count
                                </span>
                                <span className="font-medium">
                                    {product.images.length}
                                </span>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <div className="grid gap-6 xl:grid-cols-[1.3fr_0.9fr]">
                    <div className="space-y-6">
                        <Card className="overflow-hidden border-border/70 pt-0">
                            <CardHeader className="border-b border-border/70 bg-muted/30 py-6">
                                <CardTitle>Catalog identity</CardTitle>
                            </CardHeader>
                            <CardContent className="grid gap-4 p-6 md:grid-cols-2">
                                <div className="space-y-3 rounded-2xl border border-border/70 bg-background/80 p-4">
                                    <p className="text-xs font-semibold tracking-[0.2em] text-muted-foreground uppercase">
                                        Naming
                                    </p>
                                    <div className="space-y-2 text-sm">
                                        <div>
                                            <p className="text-muted-foreground">
                                                Product name
                                            </p>
                                            <p className="font-medium text-foreground">
                                                {product.name}
                                            </p>
                                        </div>
                                        <div>
                                            <p className="text-muted-foreground">
                                                Slug
                                            </p>
                                            <p className="font-mono text-xs text-foreground/80">
                                                {product.slug}
                                            </p>
                                        </div>
                                        <div>
                                            <p className="text-muted-foreground">
                                                SKU
                                            </p>
                                            <p className="font-mono text-xs text-foreground/80">
                                                {product.sku}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div className="space-y-3 rounded-2xl border border-border/70 bg-background/80 p-4">
                                    <p className="text-xs font-semibold tracking-[0.2em] text-muted-foreground uppercase">
                                        Catalog placement
                                    </p>
                                    <div className="space-y-2 text-sm">
                                        <div className="flex items-center justify-between">
                                            <span className="text-muted-foreground">
                                                Brand
                                            </span>
                                            <span className="font-medium">
                                                {product.brand_name ??
                                                    'Unassigned'}
                                            </span>
                                        </div>
                                        <div className="flex items-center justify-between">
                                            <span className="text-muted-foreground">
                                                Category
                                            </span>
                                            <span className="font-medium">
                                                {product.category_name ??
                                                    'Unassigned'}
                                            </span>
                                        </div>
                                        <div className="flex items-center justify-between">
                                            <span className="text-muted-foreground">
                                                Product type
                                            </span>
                                            <span className="font-medium capitalize">
                                                {product.product_type}
                                            </span>
                                        </div>
                                        <div className="flex items-center justify-between">
                                            <span className="text-muted-foreground">
                                                Featured
                                            </span>
                                            <span className="font-medium">
                                                {product.is_featured
                                                    ? 'Yes'
                                                    : 'No'}
                                            </span>
                                        </div>
                                        <div>
                                            <p className="text-muted-foreground">
                                                Tags
                                            </p>
                                            <div className="mt-2 flex flex-wrap gap-2">
                                                {product.tags.length > 0 ? (
                                                    product.tags.map((tag) => (
                                                        <span
                                                            key={tag.id}
                                                            className="rounded-full bg-muted px-2.5 py-1 text-xs text-foreground"
                                                        >
                                                            {tag.name}
                                                        </span>
                                                    ))
                                                ) : (
                                                    <span className="text-sm font-medium">
                                                        No tags
                                                    </span>
                                                )}
                                            </div>
                                        </div>
                                        <div>
                                            <p className="text-muted-foreground">
                                                Collections
                                            </p>
                                            <div className="mt-2 flex flex-wrap gap-2">
                                                {product.collections.length >
                                                0 ? (
                                                    product.collections.map(
                                                        (collection) => (
                                                            <span
                                                                key={
                                                                    collection.id
                                                                }
                                                                className="rounded-full bg-muted px-2.5 py-1 text-xs text-foreground"
                                                            >
                                                                {
                                                                    collection.name
                                                                }
                                                            </span>
                                                        ),
                                                    )
                                                ) : (
                                                    <span className="text-sm font-medium">
                                                        No collections
                                                    </span>
                                                )}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <Card className="overflow-hidden pt-0">
                            <CardHeader className="border-b border-border/70 bg-muted/30 py-6">
                                <CardTitle>Pricing stack</CardTitle>
                            </CardHeader>
                            <CardContent className="grid gap-4 p-6 md:grid-cols-3">
                                <div className="rounded-2xl border border-border/70 bg-background/80 p-4">
                                    <p className="text-xs font-semibold tracking-[0.2em] text-muted-foreground uppercase">
                                        Base price
                                    </p>
                                    <p className="mt-2 text-2xl font-semibold text-foreground">
                                        {formatMoney(product.base_price)}
                                    </p>
                                </div>
                                <div className="rounded-2xl border border-border/70 bg-background/80 p-4">
                                    <p className="text-xs font-semibold tracking-[0.2em] text-muted-foreground uppercase">
                                        Compare-at price
                                    </p>
                                    <p className="mt-2 text-2xl font-semibold text-foreground">
                                        {product.compare_at_price != null
                                            ? formatMoney(
                                                  product.compare_at_price,
                                              )
                                            : '—'}
                                    </p>
                                </div>
                                <div className="rounded-2xl border border-border/70 bg-background/80 p-4">
                                    <p className="text-xs font-semibold tracking-[0.2em] text-muted-foreground uppercase">
                                        Cost price
                                    </p>
                                    <p className="mt-2 text-2xl font-semibold text-foreground">
                                        {product.cost_price != null
                                            ? formatMoney(product.cost_price)
                                            : '—'}
                                    </p>
                                </div>
                            </CardContent>
                        </Card>

                        {(product.short_description || product.description) && (
                            <Card className="overflow-hidden border-border/70 pt-0">
                                <CardHeader className="border-b border-border/70 bg-muted/30 py-6">
                                    <CardTitle>Story and description</CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-4 p-6 text-sm">
                                    {product.short_description && (
                                        <div className="rounded-2xl border border-border/70 bg-background/80 p-4">
                                            <p className="text-xs font-semibold tracking-[0.2em] text-muted-foreground uppercase">
                                                Short description
                                            </p>
                                            <p className="mt-3 text-sm leading-6 font-medium text-foreground">
                                                {product.short_description}
                                            </p>
                                        </div>
                                    )}
                                    {product.description && (
                                        <div className="rounded-2xl border border-border/70 bg-background/80 p-4">
                                            <p className="text-xs font-semibold tracking-[0.2em] text-muted-foreground uppercase">
                                                Long description
                                            </p>
                                            <p className="mt-3 leading-6 whitespace-pre-wrap text-muted-foreground">
                                                {product.description}
                                            </p>
                                        </div>
                                    )}
                                </CardContent>
                            </Card>
                        )}
                    </div>

                    <div className="space-y-6">
                        <Card className="overflow-hidden border-border/70 pt-0">
                            <CardHeader className="border-b border-border/70 bg-muted/30 py-6">
                                <CardTitle>Imagery</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4 p-6">
                                <div className="overflow-hidden rounded-[1.75rem] border border-border/70 bg-muted/50">
                                    {primaryImage ? (
                                        <img
                                            src={primaryImage.card_url}
                                            alt={product.name}
                                            className="aspect-[4/3] w-full object-cover"
                                        />
                                    ) : (
                                        <div className="flex aspect-[4/3] items-center justify-center bg-[radial-gradient(circle_at_top_left,_hsl(var(--primary)/0.12),_transparent_55%),linear-gradient(135deg,_hsl(var(--muted))_0%,_hsl(var(--background))_100%)] px-6 text-center text-sm text-muted-foreground">
                                            No primary image has been assigned
                                            yet.
                                        </div>
                                    )}
                                </div>

                                {product.images.length > 1 && (
                                    <div className="grid grid-cols-3 gap-3">
                                        {product.images
                                            .slice(0, 6)
                                            .map((image) => (
                                                <div
                                                    key={image.id}
                                                    className="overflow-hidden rounded-2xl border border-border/70 bg-background/80"
                                                >
                                                    <img
                                                        src={image.thumb_url}
                                                        alt={product.name}
                                                        className="aspect-square w-full object-cover"
                                                    />
                                                </div>
                                            ))}
                                    </div>
                                )}
                            </CardContent>
                        </Card>

                        <Card className="overflow-hidden border-border/70 pt-0">
                            <CardHeader className="border-b border-border/70 bg-muted/30 py-6">
                                <CardTitle>
                                    Operational flags and inventory
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4 p-6 text-sm">
                                <div className="flex items-center justify-between">
                                    <span className="text-muted-foreground">
                                        Featured
                                    </span>
                                    <span className="font-medium">
                                        {product.is_featured ? 'Yes' : 'No'}
                                    </span>
                                </div>
                                <div className="flex items-center justify-between">
                                    <span className="text-muted-foreground">
                                        Track inventory
                                    </span>
                                    <span className="font-medium">
                                        {product.track_inventory ? 'Yes' : 'No'}
                                    </span>
                                </div>
                                <div className="flex items-center justify-between">
                                    <span className="text-muted-foreground">
                                        Allow backorders
                                    </span>
                                    <span className="font-medium">
                                        {product.allow_backorders
                                            ? 'Yes'
                                            : 'No'}
                                    </span>
                                </div>
                                <div className="flex items-center justify-between">
                                    <span className="text-muted-foreground">
                                        On hand
                                    </span>
                                    <span className="font-medium">
                                        {product.inventory.quantity_on_hand}
                                    </span>
                                </div>
                                <div className="flex items-center justify-between">
                                    <span className="text-muted-foreground">
                                        Reserved
                                    </span>
                                    <span className="font-medium">
                                        {product.inventory.quantity_reserved}
                                    </span>
                                </div>
                                <div className="flex items-center justify-between">
                                    <span className="text-muted-foreground">
                                        Available
                                    </span>
                                    <span className="font-medium">
                                        {product.inventory.available_quantity}
                                    </span>
                                </div>
                                <div className="flex items-center justify-between">
                                    <span className="text-muted-foreground">
                                        Stock items
                                    </span>
                                    <span className="font-medium">
                                        {product.inventory.stock_item_count}
                                    </span>
                                </div>
                                <div className="flex items-center justify-between">
                                    <span className="text-muted-foreground">
                                        Reorder threshold
                                    </span>
                                    <span className="font-medium">
                                        {product.inventory.reorder_level}
                                    </span>
                                </div>
                                <div className="flex items-center justify-between">
                                    <span className="text-muted-foreground">
                                        Published at
                                    </span>
                                    <span className="font-medium">
                                        {product.published_at
                                            ? new Date(
                                                  product.published_at,
                                              ).toLocaleDateString()
                                            : 'Not published'}
                                    </span>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
