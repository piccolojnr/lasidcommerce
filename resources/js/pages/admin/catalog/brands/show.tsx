import { Link } from '@inertiajs/react';
import * as BrandController from '@/actions/App/Http/Controllers/Admin/Catalog/BrandController';
import { PageHeader } from '@/components/shared/page-header/page-header';
import { StatusBadge } from '@/components/shared/status-badge/status-badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { AdminLayout } from '@/layouts/app/admin-layout';
import type { AdminBrand } from '@/types/admin/catalog';

interface Props {
    brand: AdminBrand;
}

export default function BrandShowPage({ brand }: Props) {
    return (
        <AdminLayout title="Brand Details">
            <div className="mx-auto w-full max-w-6xl space-y-6">
                <PageHeader
                    title={brand.name}
                    description="Review this brand's storefront configuration."
                    actions={
                        <div className="flex items-center gap-2">
                            <Button variant="outline" asChild>
                                <Link href={BrandController.index.url()}>Back to list</Link>
                            </Button>
                            <Button asChild>
                                <Link href={BrandController.edit.url(brand)}>Edit</Link>
                            </Button>
                        </div>
                    }
                />

                <div className="grid gap-6 md:grid-cols-2">
                    <div className="space-y-6">
                        <Card>
                            <CardHeader>
                                <CardTitle>Details</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-3 text-sm">
                                <div className="flex items-center justify-between border-b pb-3">
                                    <span className="text-muted-foreground">Name</span>
                                    <span className="font-medium">{brand.name}</span>
                                </div>
                                <div className="flex items-center justify-between border-b pb-3">
                                    <span className="text-muted-foreground">Slug</span>
                                    <span className="font-mono text-xs">{brand.slug}</span>
                                </div>
                                <div className="flex items-center justify-between border-b pb-3">
                                    <span className="text-muted-foreground">Status</span>
                                    <StatusBadge status={brand.is_active ? 'active' : 'inactive'} />
                                </div>
                                <div className="flex items-center justify-between">
                                    <span className="text-muted-foreground">Products</span>
                                    <span>{brand.products_count}</span>
                                </div>
                            </CardContent>
                        </Card>

                        {brand.description && (
                            <Card>
                                <CardHeader>
                                    <CardTitle>Description</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <p className="text-sm text-muted-foreground">{brand.description}</p>
                                </CardContent>
                            </Card>
                        )}
                    </div>

                    {brand.image_url && (
                        <div>
                            <Card>
                                <CardHeader>
                                    <CardTitle>Logo</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <img
                                        src={brand.image_url}
                                        alt={brand.name}
                                        className="w-full rounded-md border object-cover"
                                    />
                                </CardContent>
                            </Card>
                        </div>
                    )}
                </div>
            </div>
        </AdminLayout>
    );
}
