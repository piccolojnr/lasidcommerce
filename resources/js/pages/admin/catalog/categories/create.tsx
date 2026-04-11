import { Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { PageHeader } from '@/components/shared/page-header/page-header';
import { AdminLayout } from '@/layouts/app/admin-layout';
import * as CategoryController from '@/actions/App/Http/Controllers/Admin/Catalog/CategoryController';
import { CategoryForm } from '@/pages/admin/catalog/categories/_components/category-form';

interface ParentOption {
    id: number;
    name: string;
    parent_id: number | null;
}

interface Props {
    categories: ParentOption[];
}

export default function CategoryCreatePage({ categories }: Props) {
    return (
        <AdminLayout title="Create Category">
            <div className="space-y-6">
                <PageHeader
                    title="Create category"
                    description="Set up a new category for product organisation."
                    actions={
                        <Button variant="outline" asChild>
                            <Link href={CategoryController.index.url()}>Back to categories</Link>
                        </Button>
                    }
                />
                <CategoryForm categories={categories} />
            </div>
        </AdminLayout>
    );
}
