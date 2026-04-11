import { Link } from '@inertiajs/react';
import * as ProductController from '@/actions/App/Http/Controllers/Admin/Catalog/ProductController';
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
    return (
        <AdminLayout title="Product Details">
            <div className="mx-auto w-full max-w-6xl space-y-6">
                <PageHeader
                    title={product.name}
                    description="Review this product's catalog configuration."
                    actions={
                        <div className="flex items-center gap-2">
                            <Button variant="outline" asChild>
                                <Link href={ProductController.index.url()}>Back to list</Link>
                            </Button>
                            <Button asChild>
                                <Link href={ProductController.edit.url(product)}>Edit</Link>
                            </Button>
                        </div>
                    }
                />

                <div className="grid gap-6 lg:grid-cols-3">
                    {/* Left — details */}
                    <div className="space-y-6 lg:col-span-2">
                        <Card>
                            <CardHeader><CardTitle>Details</CardTitle></CardHeader>
                            <CardContent className="space-y-3 text-sm">
                                <div className="flex items-center justify-between border-b pb-3">
                                    <span className="text-muted-foreground">Name</span>
                                    <span className="font-medium">{product.name}</span>
                                </div>
                                <div className="flex items-center justify-between border-b pb-3">
                                    <span className="text-muted-foreground">Slug</span>
                                    <span className="font-mono text-xs">{product.slug}</span>
                                </div>
                                <div className="flex items-center justify-between border-b pb-3">
                                    <span className="text-muted-foreground">SKU</span>
                                    <span className="font-mono text-xs">{product.sku}</span>
                                </div>
                                <div className="flex items-center justify-between border-b pb-3">
                                    <span className="text-muted-foreground">Status</span>
                                    <StatusBadge status={product.status} />
                                </div>
                                <div className="flex items-center justify-between border-b pb-3">
                                    <span className="text-muted-foreground">Type</span>
                                    <span className="capitalize">{product.product_type}</span>
                                </div>
                                <div className="flex items-center justify-between border-b pb-3">
                                    <span className="text-muted-foreground">Category</span>
                                    <span>{product.category_name ?? '—'}</span>
                                </div>
                                <div className="flex items-center justify-between border-b pb-3">
                                    <span className="text-muted-foreground">Brand</span>
                                    <span>{product.brand_name ?? '—'}</span>
                                </div>
                                <div className="flex items-center justify-between">
                                    <span className="text-muted-foreground">Variants</span>
                                    <span>{product.variants_count}</span>
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader><CardTitle>Pricing</CardTitle></CardHeader>
                            <CardContent className="space-y-3 text-sm">
                                <div className="flex items-center justify-between border-b pb-3">
                                    <span className="text-muted-foreground">Base price</span>
                                    <span className="font-medium">{formatMoney(product.base_price)}</span>
                                </div>
                                {product.compare_at_price != null && (
                                    <div className="flex items-center justify-between border-b pb-3">
                                        <span className="text-muted-foreground">Compare-at price</span>
                                        <span className="line-through">{formatMoney(product.compare_at_price)}</span>
                                    </div>
                                )}
                                {product.cost_price != null && (
                                    <div className="flex items-center justify-between">
                                        <span className="text-muted-foreground">Cost price</span>
                                        <span>{formatMoney(product.cost_price)}</span>
                                    </div>
                                )}
                            </CardContent>
                        </Card>

                        {(product.short_description || product.description) && (
                            <Card>
                                <CardHeader><CardTitle>Description</CardTitle></CardHeader>
                                <CardContent className="space-y-3 text-sm">
                                    {product.short_description && (
                                        <p className="font-medium">{product.short_description}</p>
                                    )}
                                    {product.description && (
                                        <p className="text-muted-foreground whitespace-pre-wrap">{product.description}</p>
                                    )}
                                </CardContent>
                            </Card>
                        )}
                    </div>

                    {/* Right — images + flags */}
                    <div className="space-y-6">
                        {product.images.length > 0 && (
                            <Card>
                                <CardHeader><CardTitle>Images</CardTitle></CardHeader>
                                <CardContent>
                                    <div className="grid grid-cols-2 gap-2">
                                        {product.images.map((image) => (
                                            <div key={image.id} className="relative">
                                                <img
                                                    src={image.url}
                                                    alt=""
                                                    className="w-full rounded-md border object-cover aspect-square"
                                                />
                                                {image.is_primary && (
                                                    <span className="absolute top-1 left-1 rounded bg-black/60 px-1 text-[10px] text-white">
                                                        Primary
                                                    </span>
                                                )}
                                            </div>
                                        ))}
                                    </div>
                                </CardContent>
                            </Card>
                        )}

                        <Card>
                            <CardHeader><CardTitle>Flags</CardTitle></CardHeader>
                            <CardContent className="space-y-2 text-sm">
                                <div className="flex items-center justify-between">
                                    <span className="text-muted-foreground">Featured</span>
                                    <span>{product.is_featured ? 'Yes' : 'No'}</span>
                                </div>
                                <div className="flex items-center justify-between">
                                    <span className="text-muted-foreground">Track inventory</span>
                                    <span>{product.track_inventory ? 'Yes' : 'No'}</span>
                                </div>
                                <div className="flex items-center justify-between">
                                    <span className="text-muted-foreground">Allow backorders</span>
                                    <span>{product.allow_backorders ? 'Yes' : 'No'}</span>
                                </div>
                                {product.published_at && (
                                    <div className="flex items-center justify-between">
                                        <span className="text-muted-foreground">Published at</span>
                                        <span>{new Date(product.published_at).toLocaleDateString()}</span>
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
