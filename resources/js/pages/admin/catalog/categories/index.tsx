import { Link } from '@inertiajs/react';
import * as CategoryController from '@/actions/App/Http/Controllers/Admin/Catalog/CategoryController';
import { PageHeader } from '@/components/shared/page-header/page-header';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { EMPTY_SENTINEL, Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { useFilters } from '@/hooks/use-filters';
import { AdminLayout } from '@/layouts/app/admin-layout';
import { CategoryTable } from '@/pages/admin/catalog/categories/_components/category-table';
import type { AdminCategory } from '@/types/admin/catalog';

interface CategoryFilters {
    [key: string]: string | boolean | null;
    search: string | null;
    is_active: boolean | null;
}

interface Props {
    categories: AdminCategory[];
    filters: CategoryFilters;
}

export default function CategoryIndexPage({ categories, filters }: Props) {
    const { search, setSearch, setFilter } = useFilters(CategoryController.index.url(), filters);

    const activeValue = filters.is_active === true ? '1' : filters.is_active === false ? '0' : EMPTY_SENTINEL;

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
                <div className="flex flex-wrap items-center gap-3">
                    <Input
                        placeholder="Search categories…"
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        className="w-64"
                    />
                    <Select
                        value={activeValue}
                        onValueChange={(v) => {
                            if (v === EMPTY_SENTINEL) {
                                setFilter('is_active', null);
                            } else {
                                setFilter('is_active', v);
                            }
                        }}
                    >
                        <SelectTrigger className="w-40"><SelectValue placeholder="All" /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value={EMPTY_SENTINEL}>All</SelectItem>
                            <SelectItem value="1">Active</SelectItem>
                            <SelectItem value="0">Inactive</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <CategoryTable categories={categories} />
            </div>
        </AdminLayout>
    );
}
