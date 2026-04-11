import { Link } from '@inertiajs/react';
import * as BrandController from '@/actions/App/Http/Controllers/Admin/Catalog/BrandController';
import { PageHeader } from '@/components/shared/page-header/page-header';
import { Button } from '@/components/ui/button';
import { AdminLayout } from '@/layouts/app/admin-layout';
import { BrandTable } from '@/pages/admin/catalog/brands/_components/brand-table';
import type { AdminBrand, AdminCatalogListPage } from '@/types/admin/catalog';
import type { PaginationLink } from '@/types/shared/pagination';

interface Props {
    brands: AdminCatalogListPage<AdminBrand>;
}

export default function BrandIndexPage({ brands }: Props) {
    return (
        <AdminLayout title="Brands">
            <div className="mx-auto w-full max-w-6xl space-y-6">
                <PageHeader
                    title="Brands"
                    description="Manage brand logos, slugs, and storefront visibility."
                    actions={
                        <Button asChild>
                            <Link href={BrandController.create.url()}>Create brand</Link>
                        </Button>
                    }
                />
                <BrandTable brands={brands.data} />
                {brands.last_page > 1 && (
                    <div className="flex items-center justify-center gap-1">
                        {brands.links.map((link: PaginationLink, i: number) =>
                            link.url ? (
                                <Link
                                    key={i}
                                    href={link.url}
                                    className={`rounded border px-3 py-1 text-sm ${
                                        link.active
                                            ? 'bg-primary text-primary-foreground'
                                            : 'hover:bg-muted'
                                    }`}
                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                />
                            ) : (
                                <span
                                    key={i}
                                    className="rounded border px-3 py-1 text-sm opacity-40"
                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                />
                            ),
                        )}
                    </div>
                )}
            </div>
        </AdminLayout>
    );
}
