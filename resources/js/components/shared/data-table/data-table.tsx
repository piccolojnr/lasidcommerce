import type { ReactNode } from 'react';
import { cn } from '@/lib/utils';

export interface DataTableColumn<T> {
    key: keyof T | string;
    title: string;
    className?: string;
    render?: (row: T) => ReactNode;
}

interface DataTableProps<T> {
    columns: DataTableColumn<T>[];
    data: T[];
    emptyState?: ReactNode;
    className?: string;
}

export function DataTable<T extends object>({
    columns,
    data,
    emptyState,
    className,
}: DataTableProps<T>) {
    if (data.length === 0 && emptyState) {
        return <>{emptyState}</>;
    }

    return (
        <div className={cn('overflow-hidden rounded-lg border bg-background', className)}>
            <div className="overflow-x-auto">
                <table className="min-w-full text-sm">
                    <thead className="bg-muted/40">
                        <tr>
                            {columns.map((column) => (
                                <th
                                    key={String(column.key)}
                                    className={cn('px-4 py-3 text-left font-medium text-muted-foreground', column.className)}
                                >
                                    {column.title}
                                </th>
                            ))}
                        </tr>
                    </thead>
                    <tbody>
                        {data.map((row, index) => (
                            <tr key={index} className="border-t">
                                {columns.map((column) => (
                                    <td key={String(column.key)} className={cn('px-4 py-3 align-middle', column.className)}>
                                        {column.render ? column.render(row) : String((row as Record<string, unknown>)[String(column.key)] ?? '')}
                                    </td>
                                ))}
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </div>
    );
}
