import { Form, Link } from '@inertiajs/react';
import * as ProductController from '@/actions/App/Http/Controllers/Admin/Catalog/ProductController';
import { StatusBadge } from '@/components/shared/status-badge/status-badge';
import { Button } from '@/components/ui/button';
import { formatMoney } from '@/lib/formatters/money';
import type { AdminProduct } from '@/types/admin/catalog';

interface ProductTableProps {
    products: AdminProduct[];
}

export function ProductTable({ products }: ProductTableProps) {
    if (products.length === 0) {
        return (
            <div className="rounded-lg border bg-background px-6 py-12 text-center">
                <p className="text-sm text-muted-foreground">No products yet. Create one to get started.</p>
            </div>
        );
    }

    return (
        <div className="overflow-hidden rounded-lg border bg-background">
            <div className="overflow-x-auto">
                <table className="min-w-full text-sm">
                    <thead className="bg-muted/40">
                        <tr>
                            <th className="px-4 py-3 text-left font-medium text-muted-foreground">Product</th>
                            <th className="px-4 py-3 text-left font-medium text-muted-foreground">SKU</th>
                            <th className="px-4 py-3 text-left font-medium text-muted-foreground">Status</th>
                            <th className="px-4 py-3 text-left font-medium text-muted-foreground">Type</th>
                            <th className="px-4 py-3 text-left font-medium text-muted-foreground">Price</th>
                            <th className="px-4 py-3 text-left font-medium text-muted-foreground">Category</th>
                            <th className="px-4 py-3 text-left font-medium text-muted-foreground">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        {products.map((product) => (
                            <tr key={product.id} className="border-t align-top">
                                <td className="px-4 py-4">
                                    <div className="flex items-center gap-3">
                                        <div className="h-14 w-14 shrink-0 overflow-hidden rounded-xl border bg-muted/30">
                                            {product.images[0] ? (
                                                <img src={product.images[0].url} alt="" className="h-full w-full object-cover" />
                                            ) : null}
                                        </div>
                                        <div className="space-y-1">
                                            <div className="font-medium">{product.name}</div>
                                            <div className="text-xs text-muted-foreground">
                                                {product.brand_name ?? 'No brand'} • {product.variants_count} variant{product.variants_count === 1 ? '' : 's'}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td className="px-4 py-3 align-middle font-mono text-xs text-muted-foreground">{product.sku}</td>
                                <td className="px-4 py-3 align-middle">
                                    <StatusBadge status={product.status} />
                                </td>
                                <td className="px-4 py-3 align-middle text-muted-foreground capitalize">{product.product_type}</td>
                                <td className="px-4 py-3 align-middle">
                                    <div className="space-y-1">
                                        <div>{formatMoney(product.base_price)}</div>
                                        {product.compare_at_price != null ? (
                                            <div className="text-xs text-muted-foreground line-through">{formatMoney(product.compare_at_price)}</div>
                                        ) : null}
                                    </div>
                                </td>
                                <td className="px-4 py-3 align-middle text-muted-foreground">
                                    {product.category_name ?? '—'}
                                </td>
                                <td className="px-4 py-3 align-middle">
                                    <div className="flex items-center gap-2">
                                        <Button variant="outline" size="sm" asChild>
                                            <Link href={ProductController.show.url(product)}>View</Link>
                                        </Button>
                                        <Button variant="outline" size="sm" asChild>
                                            <Link href={ProductController.edit.url(product)}>Edit</Link>
                                        </Button>
                                        <Form
                                            {...ProductController.destroy.form.delete(product)}
                                            onSubmit={(e) => {
                                                if (!window.confirm(`Are you sure you want to delete "${product.name}"?`)) {
                                                    e.preventDefault();
                                                }
                                            }}
                                        >
                                            {() => (
                                                <Button
                                                    type="submit"
                                                    variant="outline"
                                                    size="sm"
                                                    className="text-destructive hover:text-destructive"
                                                >
                                                    Delete
                                                </Button>
                                            )}
                                        </Form>
                                    </div>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </div>
    );
}
