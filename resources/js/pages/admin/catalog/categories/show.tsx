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
            <div className="space-y-6">
                <PageHeader
                    title={category.name}
                    description="Review this category's configuration."
                    actions={
                        <div className="flex items-center gap-2">
                            <Button variant="outline" asChild>
                                <Link href={CategoryController.index.url()}>Back to list</Link>
                            </Button>
                            <Button asChild>
                                <Link href={CategoryController.edit.url(category)}>Edit</Link>
                            </Button>
                        </div>
                    }
                />

                <div className="grid gap-6 md:grid-cols-3">
                    <div className="space-y-6 md:col-span-2">
                        <Card>
                            <CardHeader>
                                <CardTitle>Details</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-3 text-sm">
                                <div className="flex items-center justify-between border-b pb-3">
                                    <span className="text-muted-foreground">Name</span>
                                    <span className="font-medium">{category.name}</span>
                                </div>
                                <div className="flex items-center justify-between border-b pb-3">
                                    <span className="text-muted-foreground">Slug</span>
                                    <span className="font-mono text-xs">{category.slug}</span>
                                </div>
                                <div className="flex items-center justify-between border-b pb-3">
                                    <span className="text-muted-foreground">Parent</span>
                                    <span>
                                        {category.parent_name ?? (
                                            <span className="italic text-muted-foreground">Root category</span>
                                        )}
                                    </span>
                                </div>
                                <div className="flex items-center justify-between border-b pb-3">
                                    <span className="text-muted-foreground">Status</span>
                                    <StatusBadge status={category.is_active ? 'active' : 'inactive'} />
                                </div>
                                <div className="flex items-center justify-between">
                                    <span className="text-muted-foreground">Sort order</span>
                                    <span>{category.sort_order}</span>
                                </div>
                            </CardContent>
                        </Card>

                        {category.description && (
                            <Card>
                                <CardHeader>
                                    <CardTitle>Description</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <p className="text-sm text-muted-foreground">{category.description}</p>
                                </CardContent>
                            </Card>
                        )}

                        {category.children.length > 0 && (
                            <Card>
                                <CardHeader>
                                    <CardTitle>Sub-categories ({category.children.length})</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <ul className="space-y-2">
                                        {category.children.map((child) => (
                                            <li key={child.id} className="flex items-center justify-between text-sm">
                                                <Link
                                                    href={CategoryController.show.url(child)}
                                                    className="hover:underline"
                                                >
                                                    {child.name}
                                                </Link>
                                                <StatusBadge status={child.is_active ? 'active' : 'inactive'} />
                                            </li>
                                        ))}
                                    </ul>
                                </CardContent>
                            </Card>
                        )}
                    </div>

                    {category.image_url && (
                        <div>
                            <Card>
                                <CardHeader>
                                    <CardTitle>Image</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <img
                                        src={category.image_url}
                                        alt={category.name}
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
