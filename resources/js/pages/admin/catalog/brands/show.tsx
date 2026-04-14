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
            <div className="mx-auto w-full max-w-7xl space-y-8">
                <PageHeader
                    title={brand.name}
                    description="Review visibility, presentation quality, and product footprint from one brand profile."
                    actions={
                        <div className="flex items-center gap-2">
                            <Button variant="outline" asChild>
                                <Link href={BrandController.index.url()}>
                                    Back to list
                                </Link>
                            </Button>
                            <Button asChild>
                                <Link href={BrandController.edit.url(brand)}>
                                    Edit
                                </Link>
                            </Button>
                        </div>
                    }
                />

                <div className="grid gap-4 lg:grid-cols-3">
                    <Card className="overflow-hidden border-border/70 bg-muted/30 lg:col-span-2">
                        <CardHeader className="space-y-3">
                            <div className="flex items-center justify-between gap-3">
                                <p className="text-xs font-semibold tracking-[0.24em] text-muted-foreground uppercase">
                                    Brand profile
                                </p>
                                <StatusBadge
                                    status={
                                        brand.is_active ? 'active' : 'inactive'
                                    }
                                />
                            </div>
                            <div className="space-y-2">
                                <CardTitle className="text-2xl">
                                    {brand.name}
                                </CardTitle>
                                <p className="max-w-2xl text-sm leading-6 text-muted-foreground">
                                    {brand.description ??
                                        'No brand story has been written yet. The catalog will still show the mark, but the profile lacks context.'}
                                </p>
                            </div>
                        </CardHeader>
                        <CardContent className="grid gap-4 border-t border-border/70 pt-6 md:grid-cols-3">
                            <div className="space-y-1">
                                <p className="text-xs font-semibold tracking-[0.2em] text-muted-foreground uppercase">
                                    Product footprint
                                </p>
                                <p className="text-2xl font-semibold text-foreground">
                                    {brand.products_count}
                                </p>
                                <p className="text-xs text-muted-foreground">
                                    Products currently attached to this brand
                                </p>
                            </div>
                            <div className="space-y-1">
                                <p className="text-xs font-semibold tracking-[0.2em] text-muted-foreground uppercase">
                                    Visual identity
                                </p>
                                <p className="text-2xl font-semibold text-foreground">
                                    {brand.image_url ? 'Ready' : 'Missing'}
                                </p>
                                <p className="text-xs text-muted-foreground">
                                    {brand.image_url
                                        ? 'Brand imagery is available for listings and forms'
                                        : 'No logo or brand image uploaded'}
                                </p>
                            </div>
                            <div className="space-y-1">
                                <p className="text-xs font-semibold tracking-[0.2em] text-muted-foreground uppercase">
                                    Slug health
                                </p>
                                <p className="font-mono text-sm text-foreground">
                                    {brand.slug}
                                </p>
                                <p className="text-xs text-muted-foreground">
                                    Canonical storefront identifier
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <Card className="overflow-hidden border-border/70 bg-primary/5 pt-0">
                        <CardHeader className="space-y-2 py-6">
                            <p className="text-xs font-semibold tracking-[0.24em] text-primary uppercase">
                                Visibility
                            </p>
                            <CardTitle className="text-xl">
                                {brand.is_active
                                    ? 'Storefront visible'
                                    : 'Hidden from storefront'}
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-3 text-sm">
                            <div className="flex items-center justify-between">
                                <span className="text-muted-foreground">
                                    Status
                                </span>
                                <span className="font-medium">
                                    {brand.is_active ? 'Active' : 'Inactive'}
                                </span>
                            </div>
                            <div className="flex items-center justify-between">
                                <span className="text-muted-foreground">
                                    Products linked
                                </span>
                                <span className="font-medium">
                                    {brand.products_count}
                                </span>
                            </div>
                            <div className="flex items-center justify-between">
                                <span className="text-muted-foreground">
                                    Image asset
                                </span>
                                <span className="font-medium">
                                    {brand.image_url ? 'Present' : 'Missing'}
                                </span>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <div className="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
                    <div className="space-y-6">
                        <Card className="overflow-hidden border-border/70 pt-0">
                            <CardHeader className="border-b border-border/70 bg-muted/30 py-6">
                                <CardTitle>Brand identity</CardTitle>
                            </CardHeader>
                            <CardContent className="grid gap-4 p-6 md:grid-cols-2">
                                <div className="rounded-2xl border border-border/70 bg-background/80 p-4">
                                    <p className="text-xs font-semibold tracking-[0.2em] text-muted-foreground uppercase">
                                        Naming
                                    </p>
                                    <div className="mt-3 space-y-2 text-sm">
                                        <div>
                                            <p className="text-muted-foreground">
                                                Brand name
                                            </p>
                                            <p className="font-medium text-foreground">
                                                {brand.name}
                                            </p>
                                        </div>
                                        <div>
                                            <p className="text-muted-foreground">
                                                Slug
                                            </p>
                                            <p className="font-mono text-xs text-foreground/80">
                                                {brand.slug}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div className="rounded-2xl border border-border/70 bg-background/80 p-4">
                                    <p className="text-xs font-semibold tracking-[0.2em] text-muted-foreground uppercase">
                                        Catalog reach
                                    </p>
                                    <div className="mt-3 space-y-2 text-sm">
                                        <div className="flex items-center justify-between">
                                            <span className="text-muted-foreground">
                                                Product count
                                            </span>
                                            <span className="font-medium">
                                                {brand.products_count}
                                            </span>
                                        </div>
                                        <div className="flex items-center justify-between">
                                            <span className="text-muted-foreground">
                                                Status
                                            </span>
                                            <span className="font-medium">
                                                {brand.is_active
                                                    ? 'Active'
                                                    : 'Inactive'}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <Card className="overflow-hidden border-border/70 pt-0">
                            <CardHeader className="border-b border-border/70 bg-muted/30 py-6">
                                <CardTitle>Brand story</CardTitle>
                            </CardHeader>
                            <CardContent className="p-6">
                                <div className="rounded-2xl border border-border/70 bg-background/80 p-5">
                                    <p className="text-sm leading-6 text-muted-foreground">
                                        {brand.description ??
                                            'No description yet. Add one if this brand needs more context in the admin and future storefront surfaces.'}
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <div className="space-y-6">
                        <Card className="overflow-hidden border-border/70 pt-0">
                            <CardHeader className="border-b border-border/70 bg-muted/30 py-6">
                                <CardTitle>Brand imagery</CardTitle>
                            </CardHeader>
                            <CardContent className="p-6">
                                <div className="overflow-hidden rounded-[1.75rem] border border-border/70 bg-muted/50">
                                    {brand.image_url ? (
                                        <img
                                            src={brand.image_url}
                                            alt={brand.name}
                                            className="aspect-[4/3] w-full object-cover"
                                        />
                                    ) : (
                                        <div className="flex aspect-[4/3] items-center justify-center bg-[radial-gradient(circle_at_top_left,_hsl(var(--primary)/0.12),_transparent_55%),linear-gradient(135deg,_hsl(var(--muted))_0%,_hsl(var(--background))_100%)] px-6 text-center text-sm text-muted-foreground">
                                            No brand image uploaded yet.
                                        </div>
                                    )}
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
