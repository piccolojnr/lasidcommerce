import { Link } from '@inertiajs/react';
import * as TagController from '@/actions/App/Http/Controllers/Admin/Catalog/TagController';
import { PageHeader } from '@/components/shared/page-header/page-header';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import {
    EMPTY_SENTINEL,
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useFilters } from '@/hooks/use-filters';
import { AdminLayout } from '@/layouts/app/admin-layout';
import { TagTable } from '@/pages/admin/catalog/tags/_components/tag-table';
import type { AdminCatalogListPage, AdminTag } from '@/types/admin/catalog';
import type { PaginationLink } from '@/types/shared/pagination';

interface TagFilters {
    [key: string]: string | boolean | null;
    search: string | null;
    is_active: boolean | null;
}

export default function TagIndexPage({
    tags,
    filters,
}: {
    tags: AdminCatalogListPage<AdminTag>;
    filters: TagFilters;
}) {
    const { search, setSearch, setFilter } = useFilters(
        TagController.index.url(),
        filters,
    );
    const activeValue =
        filters.is_active === true
            ? '1'
            : filters.is_active === false
              ? '0'
              : EMPTY_SENTINEL;

    return (
        <AdminLayout title="Tags">
            <div className="mx-auto w-full max-w-6xl space-y-6">
                <PageHeader
                    title="Tags"
                    description="Manage flexible merchandising labels without distorting categories or pricing rules."
                    actions={
                        <Button asChild>
                            <Link href={TagController.create.url()}>
                                Create tag
                            </Link>
                        </Button>
                    }
                />
                <div className="grid gap-4 md:grid-cols-3">
                    <Card className="border-border/70">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                Visible in this result
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-3xl font-semibold">
                                {tags.data.length}
                            </div>
                        </CardContent>
                    </Card>
                    <Card className="border-border/70">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                Active on this page
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-3xl font-semibold">
                                {
                                    tags.data.filter((tag) => tag.is_active)
                                        .length
                                }
                            </div>
                        </CardContent>
                    </Card>
                    <Card className="border-border/70">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                Assigned products
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-3xl font-semibold">
                                {tags.data.reduce(
                                    (sum, tag) => sum + tag.products_count,
                                    0,
                                )}
                            </div>
                        </CardContent>
                    </Card>
                </div>
                <div className="flex flex-wrap items-center gap-3">
                    <Input
                        placeholder="Search tags…"
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        className="w-64"
                    />
                    <Select
                        value={activeValue}
                        onValueChange={(v) =>
                            setFilter(
                                'is_active',
                                v === EMPTY_SENTINEL ? null : v,
                            )
                        }
                    >
                        <SelectTrigger className="w-40">
                            <SelectValue placeholder="All" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value={EMPTY_SENTINEL}>All</SelectItem>
                            <SelectItem value="1">Active</SelectItem>
                            <SelectItem value="0">Inactive</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <TagTable tags={tags.data} />
                {tags.last_page > 1 && (
                    <div className="flex items-center justify-center gap-1">
                        {tags.links.map((link: PaginationLink, i: number) =>
                            link.url ? (
                                <Link
                                    key={i}
                                    href={link.url}
                                    className={`rounded border px-3 py-1 text-sm ${link.active ? 'bg-primary text-primary-foreground' : 'hover:bg-muted'}`}
                                    dangerouslySetInnerHTML={{
                                        __html: link.label,
                                    }}
                                />
                            ) : (
                                <span
                                    key={i}
                                    className="rounded border px-3 py-1 text-sm opacity-40"
                                    dangerouslySetInnerHTML={{
                                        __html: link.label,
                                    }}
                                />
                            ),
                        )}
                    </div>
                )}
            </div>
        </AdminLayout>
    );
}
