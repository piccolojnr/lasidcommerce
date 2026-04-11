import { Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { PageHeader } from '@/components/shared/page-header/page-header';
import { AdminLayout } from '@/layouts/app/admin-layout';
import * as CategoryController from '@/actions/App/Http/Controllers/Admin/Catalog/CategoryController';
import { CategoryTable } from '@/pages/admin/catalog/categories/_components/category-table';
import type { AdminCategory } from '@/types/admin/catalog';

interface Props {
    categories: AdminCategory[];
}

export default function CategoryIndexPage({ categories }: Props) {
    return (
        <AdminLayout title="Categories">
            <div className="space-y-6">
                <PageHeader
                    title="Categories"
                    description="Manage hierarchy, ordering, and active visibility."
                    actions={
                        <Button asChild>
                            <Link href={CategoryController.create.url()}>Create category</Link>
                        </Button>
                    }
                />
                <CategoryTable categories={categories} />
            </div>
        </AdminLayout>
    );
}
