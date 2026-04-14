import { Link } from '@inertiajs/react';
import * as CategoryController from '@/actions/App/Http/Controllers/Admin/Catalog/CategoryController';
import { PageHeader } from '@/components/shared/page-header/page-header';
import { StatusBadge } from '@/components/shared/status-badge/status-badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { AdminLayout } from '@/layouts/app/admin-layout';
import type { AdminCategory } from '@/types/admin/catalog';

interface ChildSummary {
    id: number;
    name: string;
    slug: string;
    is_active: boolean;
}

interface CategoryDetail extends AdminCategory {
    children: ChildSummary[];
}

interface Props {
    category: CategoryDetail;
}

export default function CategoryShowPage({ category }: Props) {
    return (
        <AdminLayout title="Category Details">
            <div className="mx-auto w-full max-w-7xl space-y-8">
                <PageHeader
                    title={category.name}
                    description="Review hierarchy, discoverability, and child structure without digging through raw fields."
                    actions={
                        <div className="flex items-center gap-2">
                            <Button variant="outline" asChild>
                                <Link href={CategoryController.index.url()}>
                                    Back to list
                                </Link>
                            </Button>
                            <Button asChild>
                                <Link
                                    href={CategoryController.edit.url(category)}
                                >
                                    Edit
                                </Link>
                            </Button>
                        </div>
                    }
                />

                <div className="grid gap-4 lg:grid-cols-3">
                    <Card className="border-border/70 bg-muted/30 lg:col-span-2">
                        <CardHeader className="space-y-3">
                            <div className="flex items-center justify-between gap-3">
                                <p className="text-xs font-semibold tracking-[0.24em] text-muted-foreground uppercase">
                                    Hierarchy profile
                                </p>
                                <StatusBadge
                                    status={
                                        category.is_active
                                            ? 'active'
                                            : 'inactive'
                                    }
                                />
                            </div>
                            <div className="space-y-2">
                                <CardTitle className="text-2xl">
                                    {category.name}
                                </CardTitle>
                                <p className="max-w-2xl text-sm leading-6 text-muted-foreground">
                                    {category.description ??
                                        'No descriptive copy has been added for this category yet.'}
                                </p>
                            </div>
                        </CardHeader>
                        <CardContent className="grid gap-4 border-t border-border/70 pt-6 md:grid-cols-3">
                            <div className="space-y-1">
                                <p className="text-xs font-semibold tracking-[0.2em] text-muted-foreground uppercase">
                                    Position
                                </p>
                                <p className="text-2xl font-semibold text-foreground">
                                    {category.sort_order}
                                </p>
                                <p className="text-xs text-muted-foreground">
                                    Sort order within its current branch
                                </p>
                            </div>
                            <div className="space-y-1">
                                <p className="text-xs font-semibold tracking-[0.2em] text-muted-foreground uppercase">
                                    Child nodes
                                </p>
                                <p className="text-2xl font-semibold text-foreground">
                                    {category.children.length}
                                </p>
                                <p className="text-xs text-muted-foreground">
                                    Immediate sub-categories under this node
                                </p>
                            </div>
                            <div className="space-y-1">
                                <p className="text-xs font-semibold tracking-[0.2em] text-muted-foreground uppercase">
                                    Level
                                </p>
                                <p className="text-lg font-semibold text-foreground">
                                    {category.parent_name
                                        ? 'Nested category'
                                        : 'Root branch'}
                                </p>
                                <p className="text-xs text-muted-foreground">
                                    {category.parent_name
                                        ? `Parented under ${category.parent_name}`
                                        : 'Top-level entry point in the catalog tree'}
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <Card className="border-border/70 bg-secondary/50">
                        <CardHeader className="space-y-2">
                            <p className="text-xs font-semibold tracking-[0.24em] text-foreground/70 uppercase">
                                Discoverability
                            </p>
                            <CardTitle className="text-xl">
                                {category.is_active
                                    ? 'Visible to shoppers'
                                    : 'Hidden from shoppers'}
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-3 text-sm">
                            <div className="flex items-center justify-between">
                                <span className="text-muted-foreground">
                                    Parent branch
                                </span>
                                <span className="font-medium">
                                    {category.parent_name ?? 'Root'}
                                </span>
                            </div>
                            <div className="flex items-center justify-between">
                                <span className="text-muted-foreground">
                                    Image asset
                                </span>
                                <span className="font-medium">
                                    {category.image_url ? 'Present' : 'Missing'}
                                </span>
                            </div>
                            <div className="flex items-center justify-between">
                                <span className="text-muted-foreground">Slug</span>
                                <span className="font-mono text-xs">
                                    {category.slug}
                                </span>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <div className="grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
                    <div className="space-y-6">
                        <Card className="overflow-hidden border-border/70 pt-0">
                            <CardHeader className="border-b border-border/70 bg-muted/30 py-6">
                                <CardTitle>Category identity</CardTitle>
                            </CardHeader>
                            <CardContent className="grid gap-4 p-6 md:grid-cols-2">
                                <div className="rounded-2xl border border-border/70 bg-background/80 p-4">
                                    <p className="text-xs font-semibold tracking-[0.2em] text-muted-foreground uppercase">
                                        Basics
                                    </p>
                                    <div className="mt-3 space-y-2 text-sm">
                                        <div>
                                            <p className="text-muted-foreground">
                                                Category name
                                            </p>
                                            <p className="font-medium text-foreground">
                                                {category.name}
                                            </p>
                                        </div>
                                        <div>
                                            <p className="text-muted-foreground">
                                                Slug
                                            </p>
                                            <p className="font-mono text-xs text-foreground/80">
                                                {category.slug}
                                            </p>
                                        </div>
                                        <div className="flex items-center justify-between">
                                            <span className="text-muted-foreground">
                                                Sort order
                                            </span>
                                            <span className="font-medium">
                                                {category.sort_order}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div className="rounded-2xl border border-border/70 bg-background/80 p-4">
                                    <p className="text-xs font-semibold tracking-[0.2em] text-muted-foreground uppercase">
                                        Hierarchy
                                    </p>
                                    <div className="mt-3 space-y-2 text-sm">
                                        <div className="flex items-center justify-between">
                                            <span className="text-muted-foreground">
                                                Parent
                                            </span>
                                            <span className="font-medium">
                                                {category.parent_name ??
                                                    'Root category'}
                                            </span>
                                        </div>
                                        <div className="flex items-center justify-between">
                                            <span className="text-muted-foreground">
                                                Children
                                            </span>
                                            <span className="font-medium">
                                                {category.children.length}
                                            </span>
                                        </div>
                                        <div className="flex items-center justify-between">
                                            <span className="text-muted-foreground">
                                                Status
                                            </span>
                                            <span className="font-medium">
                                                {category.is_active
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
                                <CardTitle>Category notes</CardTitle>
                            </CardHeader>
                            <CardContent className="p-6">
                                <div className="rounded-2xl border border-border/70 bg-background/80 p-5">
                                    <p className="text-sm leading-6 text-muted-foreground">
                                        {category.description ??
                                            'No description yet. Add one if this category needs internal guidance or storefront context.'}
                                    </p>
                                </div>
                            </CardContent>
                        </Card>

                        <Card className="overflow-hidden border-border/70 pt-0">
                            <CardHeader className="border-b border-border/70 bg-muted/30 py-6">
                                <CardTitle>Sub-categories</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-3 p-6">
                                {category.children.length > 0 ? (
                                    category.children.map((child) => (
                                        <div
                                            key={child.id}
                                            className="flex items-center justify-between rounded-2xl border border-border/70 bg-background/80 px-4 py-3 text-sm"
                                        >
                                            <div className="space-y-1">
                                                <Link
                                                    href={CategoryController.show.url(
                                                        child,
                                                    )}
                                                    className="font-medium text-foreground transition hover:text-foreground/80"
                                                >
                                                    {child.name}
                                                </Link>
                                                <p className="font-mono text-xs text-muted-foreground">
                                                    {child.slug}
                                                </p>
                                            </div>
                                            <StatusBadge
                                                status={
                                                    child.is_active
                                                        ? 'active'
                                                        : 'inactive'
                                                }
                                            />
                                        </div>
                                    ))
                                ) : (
                                    <div className="rounded-2xl border border-dashed border-border/70 bg-muted/30 px-4 py-6 text-sm text-muted-foreground">
                                        No child categories yet.
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    </div>

                    <div className="space-y-6">
                        <Card className="overflow-hidden border-border/70 pt-0">
                            <CardHeader className="border-b border-border/70 bg-muted/30 py-6">
                                <CardTitle>Category image</CardTitle>
                            </CardHeader>
                            <CardContent className="p-6">
                                <div className="overflow-hidden rounded-[1.75rem] border border-border/70 bg-muted/50">
                                    {category.image_url ? (
                                        <img
                                            src={category.image_url}
                                            alt={category.name}
                                            className="aspect-[4/3] w-full object-cover"
                                        />
                                    ) : (
                                        <div className="flex aspect-[4/3] items-center justify-center bg-[radial-gradient(circle_at_top_left,_hsl(var(--primary)/0.12),_transparent_55%),linear-gradient(135deg,_hsl(var(--muted))_0%,_hsl(var(--background))_100%)] px-6 text-center text-sm text-muted-foreground">
                                            No category image uploaded yet.
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
