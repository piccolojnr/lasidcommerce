import { ChevronLeft, ChevronRight } from 'lucide-react';
import { Button } from '@/components/ui/button';
import type { PaginationMeta } from '@/types/shared/pagination';

interface DataTablePaginationProps {
    meta?: PaginationMeta;
}

export function DataTablePagination({ meta }: DataTablePaginationProps) {
    if (!meta) {
        return null;
    }

    return (
        <div className="flex flex-col gap-3 border-t px-4 py-3 text-sm sm:flex-row sm:items-center sm:justify-between">
            <p className="text-muted-foreground">
                Showing {meta.from ?? 0} to {meta.to ?? 0} of {meta.total}
            </p>
            <div className="flex items-center gap-2">
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    disabled={meta.current_page <= 1}
                >
                    <ChevronLeft className="mr-1 size-4" />
                    Previous
                </Button>
                <span className="text-muted-foreground">
                    Page {meta.current_page} of {meta.last_page}
                </span>
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    disabled={meta.current_page >= meta.last_page}
                >
                    Next
                    <ChevronRight className="ml-1 size-4" />
                </Button>
            </div>
        </div>
    );
}
