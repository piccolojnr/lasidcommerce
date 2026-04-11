import { Link } from '@inertiajs/react';
import * as ProductController from '@/actions/App/Http/Controllers/Admin/Catalog/ProductController';
import { PageHeader } from '@/components/shared/page-header/page-header';
import { Button } from '@/components/ui/button';
import { AdminLayout } from '@/layouts/app/admin-layout';
import { ProductTable } from '@/pages/admin/catalog/products/_components/product-table';
import type { AdminCatalogListPage, AdminProduct } from '@/types/admin/catalog';
import type { PaginationLink } from '@/types/shared/pagination';

interface Props {
    products: AdminCatalogListPage<AdminProduct>;
}

export default function ProductIndexPage({ products }: Props) {
    return (
        <AdminLayout title="Products">
            <div className="mx-auto w-full max-w-6xl space-y-6">
                <PageHeader
                    title="Products"
                    description="Manage product catalog entries and publishing state."
                    actions={
                        <Button asChild>
                            <Link href={ProductController.create.url()}>Create product</Link>
                        </Button>
                    }
                />
                <ProductTable products={products.data} />
                {products.last_page > 1 && (
                    <div className="flex items-center justify-center gap-1">
                        {products.links.map((link: PaginationLink, i: number) =>
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
