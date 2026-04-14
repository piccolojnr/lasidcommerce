import { Form, Link } from '@inertiajs/react';
import * as BrandController from '@/actions/App/Http/Controllers/Admin/Catalog/BrandController';
import { StatusBadge } from '@/components/shared/status-badge/status-badge';
import { Button } from '@/components/ui/button';
import type { AdminBrand } from '@/types/admin/catalog';

interface BrandTableProps {
    brands: AdminBrand[];
}

export function BrandTable({ brands }: BrandTableProps) {
    if (brands.length === 0) {
        return (
            <div className="rounded-lg border bg-background px-6 py-12 text-center">
                <p className="text-sm text-muted-foreground">No brands yet. Create one to get started.</p>
            </div>
        );
    }

    return (
        <div className="overflow-hidden rounded-lg border bg-background">
            <div className="overflow-x-auto">
                <table className="min-w-full text-sm">
                    <thead className="bg-muted/40">
                        <tr>
                            <th className="px-4 py-3 text-left font-medium text-muted-foreground">Name</th>
                            <th className="px-4 py-3 text-left font-medium text-muted-foreground">Slug</th>
                            <th className="px-4 py-3 text-left font-medium text-muted-foreground">Status</th>
                            <th className="px-4 py-3 text-left font-medium text-muted-foreground">Products</th>
                            <th className="px-4 py-3 text-left font-medium text-muted-foreground">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        {brands.map((brand) => (
                            <tr key={brand.id} className="border-t align-top">
                                <td className="px-4 py-4">
                                    <div className="flex items-center gap-3">
                                        <div className="h-14 w-14 shrink-0 overflow-hidden rounded-xl border bg-muted/30">
                                            {brand.image_url ? (
                                                <img src={brand.image_url} alt={brand.name} className="h-full w-full object-cover" />
                                            ) : null}
                                        </div>
                                        <div className="space-y-1">
                                            <div className="font-medium">{brand.name}</div>
                                            <div className="text-xs text-muted-foreground">
                                                {brand.products_count} linked product{brand.products_count === 1 ? '' : 's'}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td className="px-4 py-3 align-middle font-mono text-xs text-muted-foreground">{brand.slug}</td>
                                <td className="px-4 py-3 align-middle">
                                    <StatusBadge status={brand.is_active ? 'active' : 'inactive'} />
                                </td>
                                <td className="px-4 py-3 align-middle text-muted-foreground">{brand.products_count}</td>
                                <td className="px-4 py-3 align-middle">
                                    <div className="flex items-center gap-2">
                                        <Button variant="outline" size="sm" asChild>
                                            <Link href={BrandController.show.url(brand)}>View</Link>
                                        </Button>
                                        <Button variant="outline" size="sm" asChild>
                                            <Link href={BrandController.edit.url(brand)}>Edit</Link>
                                        </Button>
                                        <Form
                                            {...BrandController.destroy.form.delete(brand)}
                                            onSubmit={(e) => {
                                                if (!window.confirm(`Are you sure you want to delete "${brand.name}"?`)) {
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
