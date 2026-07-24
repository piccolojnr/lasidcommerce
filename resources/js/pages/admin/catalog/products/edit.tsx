import { Link } from '@inertiajs/react';
import * as ProductController from '@/actions/App/Http/Controllers/Admin/Catalog/ProductController';
import * as ProductVariantMatrixController from '@/actions/App/Http/Controllers/Admin/Catalog/ProductVariantMatrixController';
import { PageHeader } from '@/components/shared/page-header/page-header';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { AdminLayout } from '@/layouts/app/admin-layout';
import { ProductForm } from '@/pages/admin/catalog/products/_components/product-form';
import type { AdminProduct } from '@/types/admin/catalog';

interface SelectOption {
    id: number;
    name: string;
}

interface Props {
    product: AdminProduct;
    categories: SelectOption[];
    brands: SelectOption[];
    tags: SelectOption[];
    collections: SelectOption[];
}

export default function ProductEditPage({
    product,
    categories,
    brands,
    tags,
    collections,
}: Props) {
    const variantUrl = ProductVariantMatrixController.index.url(product);

    return (
        <AdminLayout title="Edit Product">
            <div className="mx-auto w-full max-w-7xl space-y-6">
                <PageHeader
                    title={`Edit ${product.name}`}
                    description="Refine the merchandising, media, and operational setup without digging through a dull form."
                    actions={
                        <div className="flex items-center gap-2">
                            <Button variant="outline" asChild>
                                <Link
                                    href={ProductController.show.url(product)}
                                >
                                    Back to product
                                </Link>
                            </Button>
                            <Button variant="outline" asChild>
                                <Link href={variantUrl}>Manage variants</Link>
                            </Button>
                        </div>
                    }
                />

                <ProductForm
                    product={product}
                    categories={categories}
                    brands={brands}
                    tags={tags}
                    collections={collections}
                />

                {/* Variant summary strip — read-only, links to the matrix page */}
                <Card className="border-border/70">
                    <CardHeader className="flex flex-row items-center justify-between gap-4">
                        <div>
                            <CardTitle>Options &amp; variants</CardTitle>
                            <p className="mt-1 text-sm text-muted-foreground">
                                {product.variants_count > 0
                                    ? `${product.variants_count} variant${product.variants_count === 1 ? '' : 's'} · ${product.option_types.length} option axis${product.option_types.length === 1 ? '' : 'es'}`
                                    : 'No variants yet. Build the matrix on the variants page.'}
                            </p>
                        </div>
                        <Button asChild>
                            <Link href={variantUrl}>Manage variants</Link>
                        </Button>
                    </CardHeader>
                    {product.option_types.length > 0 && (
                        <CardContent className="flex flex-wrap gap-3 pt-0">
                            {product.option_types.map((ot) => (
                                <div
                                    key={ot.id}
                                    className="flex flex-wrap items-center gap-2 rounded-xl border border-border/70 bg-muted/30 px-4 py-2"
                                >
                                    <span className="text-sm font-medium">
                                        {ot.name}:
                                    </span>
                                    {ot.values.map((v) => (
                                        <Badge key={v.id} variant="secondary">
                                            {v.value}
                                        </Badge>
                                    ))}
                                </div>
                            ))}
                        </CardContent>
                    )}
                </Card>
            </div>
        </AdminLayout>
    );
}
