import { Link } from '@inertiajs/react';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import { Button } from '@/components/ui/button';
import type { AdminCatalogListPage } from '@/types/admin/catalog';
import type { AdminInventoryListPage } from '@/types/admin/inventory';
import type { PaginationLink, PaginationMeta } from '@/types/shared/pagination';

// Accept either a structured meta object or the paginated list objects
// that Laravel returns from the catalog/inventory controllers.
type AnyPage<T = unknown> = AdminCatalogListPage<T> | AdminInventoryListPage<T>;

interface DataTablePaginationProps<T = unknown> {
    /** Pass either a PaginationMeta or a full paginated page object */
    meta?: PaginationMeta | AnyPage<T>;
    /** Number of link slots to show (excluding prev/next). Default: 5 */
    siblingCount?: number;
}

function isPage<T>(v: PaginationMeta | AnyPage<T>): v is AnyPage<T> {
    return 'links' in v && Array.isArray((v as AnyPage<T>).links);
}

export function DataTablePagination<T = unknown>({
    meta,
}: DataTablePaginationProps<T>) {
    if (!meta) {
        return null;
    }

    const currentPage = meta.current_page;
    const lastPage = meta.last_page;
    const from = meta.from ?? 0;
    const to = meta.to ?? 0;
    const total = meta.total;

    // Derive links from the page object when available.
    const links: PaginationLink[] = isPage(meta) ? meta.links : [];

    const prevLink = links.find((l) => l.label === '&laquo; Previous');
    const nextLink = links.find((l) => l.label === 'Next &raquo;');
    // Numbered page links — exclude the prev/next sentinel entries.
    const pageLinks = links.filter(
        (l) => l.label !== '&laquo; Previous' && l.label !== 'Next &raquo;',
    );

    if (lastPage <= 1) {
        // Still render the "Showing X to Y of Z" bar.
        return (
            <div className="flex items-center justify-between border-t px-4 py-3 text-sm text-muted-foreground">
                <p>
                    Showing {from} – {to} of {total}
                </p>
            </div>
        );
    }

    return (
        <div className="flex flex-col gap-3 border-t px-4 py-3 text-sm sm:flex-row sm:items-center sm:justify-between">
            <p className="text-muted-foreground">
                Showing {from} – {to} of {total}
            </p>

            <div className="flex items-center gap-1">
                {/* Previous */}
                {prevLink?.url ? (
                    <Button variant="outline" size="sm" asChild>
                        <Link href={prevLink.url}>
                            <ChevronLeft className="size-4" />
                            <span className="sr-only">Previous</span>
                        </Link>
                    </Button>
                ) : (
                    <Button variant="outline" size="sm" disabled>
                        <ChevronLeft className="size-4" />
                        <span className="sr-only">Previous</span>
                    </Button>
                )}

                {/* Numbered page links */}
                {pageLinks.length > 0 ? (
                    pageLinks.map((link, i) =>
                        link.url ? (
                            <Button
                                key={i}
                                variant={link.active ? 'default' : 'outline'}
                                size="sm"
                                className="min-w-8"
                                asChild
                            >
                                <Link
                                    href={link.url}
                                    dangerouslySetInnerHTML={{
                                        __html: link.label,
                                    }}
                                />
                            </Button>
                        ) : (
                            // Ellipsis separator
                            <span
                                key={i}
                                className="px-1 text-muted-foreground"
                                dangerouslySetInnerHTML={{
                                    __html: link.label,
                                }}
                            />
                        ),
                    )
                ) : (
                    // Fallback: plain "Page X of Y" when no links array
                    <span className="px-2 text-muted-foreground">
                        Page {currentPage} of {lastPage}
                    </span>
                )}

                {/* Next */}
                {nextLink?.url ? (
                    <Button variant="outline" size="sm" asChild>
                        <Link href={nextLink.url}>
                            <ChevronRight className="size-4" />
                            <span className="sr-only">Next</span>
                        </Link>
                    </Button>
                ) : (
                    <Button variant="outline" size="sm" disabled>
                        <ChevronRight className="size-4" />
                        <span className="sr-only">Next</span>
                    </Button>
                )}
            </div>
        </div>
    );
}
