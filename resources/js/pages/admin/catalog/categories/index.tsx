import { Link } from '@inertiajs/react';
import * as CategoryController from '@/actions/App/Http/Controllers/Admin/Catalog/CategoryController';
import { PageHeader } from '@/components/shared/page-header/page-header';
import { Button } from '@/components/ui/button';
import { AdminLayout } from '@/layouts/app/admin-layout';
import { CategoryTable } from '@/pages/admin/catalog/categories/_components/category-table';
import type { AdminCategory } from '@/types/admin/catalog';

interface Props {
    categories: AdminCategory[];
}

export default function CategoryIndexPage({ categories }: Props) {
    return (
        <AdminLayout title="Categories">
            <div className="mx-auto w-full max-w-6xl space-y-6">
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
