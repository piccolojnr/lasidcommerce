import { Link } from '@inertiajs/react';
import * as ProductController from '@/actions/App/Http/Controllers/Admin/Catalog/ProductController';
import { PageHeader } from '@/components/shared/page-header/page-header';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { AdminLayout } from '@/layouts/app/admin-layout';
import type { AdminProductOptionType, AdminProductVariant } from '@/types/admin/catalog';
import { OptionBuilder } from './_components/option-builder';
import { VariantMatrix } from './_components/variant-matrix';

interface VariantProduct {
    id: number;
    name: string;
    sku: string;
    base_price: number;
    track_inventory: boolean;
    option_types: AdminProductOptionType[];
    variants: AdminProductVariant[];
}

interface Props {
    product: VariantProduct;
    movementTypes: string[];
}

export default function ProductVariantsPage({ product, movementTypes }: Props) {
    const hasOptions = product.option_types.length > 0;
    const hasValues = product.option_types.some((ot) => ot.values.length > 0);
    const canGenerate = hasOptions && hasValues;

    return (
        <AdminLayout title="Variant Matrix">
            <div className="mx-auto w-full max-w-7xl space-y-8">
                <PageHeader
                    title={`Variants — ${product.name}`}
                    description="Define option axes, add values in bulk, then generate all combinations in one click."
                    actions={
                        <div className="flex items-center gap-2">
                            <Button variant="outline" asChild>
                                <Link href={ProductController.edit.url(product)}>
                                    Back to edit
                                </Link>
                            </Button>
                            <Button variant="outline" asChild>
                                <Link href={ProductController.show.url(product)}>
                                    View product
                                </Link>
                            </Button>
                        </div>
                    }
                />

                <div className="grid gap-8 xl:grid-cols-[380px_minmax(0,1fr)]">
                    {/* Left: option builder */}
                    <div className="space-y-4">
                        <div>
                            <h2 className="text-lg font-semibold tracking-tight">Option axes</h2>
                            <p className="mt-1 text-sm text-muted-foreground">
                                Each axis (e.g. Size, Color) gets its own set of values. Type
                                multiple values and press Enter or comma to queue them before
                                adding.
                            </p>
                        </div>
                        <OptionBuilder
                            product={product}
                            optionTypes={product.option_types}
                        />

                        {!hasOptions && (
                            <div className="rounded-xl border border-dashed border-border/70 px-4 py-6 text-center text-sm text-muted-foreground">
                                Add your first option axis above to get started.
                            </div>
                        )}
                    </div>

                    {/* Right: variant matrix */}
                    <div className="space-y-4">
                        <div>
                            <h2 className="text-lg font-semibold tracking-tight">Variant matrix</h2>
                            <p className="mt-1 text-sm text-muted-foreground">
                                Each row is one sellable SKU. Edit SKUs and prices inline — changes
                                are saved per-row. Generate all combinations from options, or add
                                variants individually.
                            </p>
                        </div>
                        <VariantMatrix product={product} canGenerate={canGenerate} movementTypes={movementTypes} />
                    </div>
                </div>

                {/* How it works callout */}
                {!hasOptions && product.variants.length === 0 && (
                    <Card className="border-border/70 bg-muted/30">
                        <CardHeader>
                            <CardTitle className="text-base">How variant management works</CardTitle>
                        </CardHeader>
                        <CardContent className="grid gap-4 text-sm text-muted-foreground sm:grid-cols-3">
                            <div className="space-y-1">
                                <p className="font-medium text-foreground">1. Define options</p>
                                <p>Add axes like Size or Color. Type all values at once — "S, M, L, XL" — and press Add.</p>
                            </div>
                            <div className="space-y-1">
                                <p className="font-medium text-foreground">2. Generate matrix</p>
                                <p>Click "Generate matrix" and every valid combination is created automatically with auto-suggested SKUs.</p>
                            </div>
                            <div className="space-y-1">
                                <p className="font-medium text-foreground">3. Refine inline</p>
                                <p>Edit SKUs, prices, and active status directly in the table. Deactivate combinations you don't sell.</p>
                            </div>
                        </CardContent>
                    </Card>
                )}
            </div>
        </AdminLayout>
    );
}
