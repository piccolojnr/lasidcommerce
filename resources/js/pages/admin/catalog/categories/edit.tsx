import { Link } from '@inertiajs/react';
import * as CategoryController from '@/actions/App/Http/Controllers/Admin/Catalog/CategoryController';
import { PageHeader } from '@/components/shared/page-header/page-header';
import { Button } from '@/components/ui/button';
import { AdminLayout } from '@/layouts/app/admin-layout';
import { CategoryForm } from '@/pages/admin/catalog/categories/_components/category-form';
import type { AdminCategory } from '@/types/admin/catalog';

interface ParentOption {
    id: number;
    name: string;
    parent_id: number | null;
}

interface Props {
    category: AdminCategory;
    categories: ParentOption[];
}

export default function CategoryEditPage({ category, categories }: Props) {
    return (
        <AdminLayout title="Edit Category">
            <div className="mx-auto w-full max-w-7xl space-y-6">
                <PageHeader
                    title={`Edit: ${category.name}`}
                    description="Adjust the hierarchy, image, and storefront visibility without wrestling a cramped form."
                    actions={
                        <Button variant="outline" asChild>
                            <Link href={CategoryController.show.url(category)}>
                                View category
                            </Link>
                        </Button>
                    }
                />
                <CategoryForm category={category} categories={categories} />
            </div>
        </AdminLayout>
    );
}
