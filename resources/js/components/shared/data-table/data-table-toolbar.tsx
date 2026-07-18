import type { ReactNode } from 'react';
import { Input } from '@/components/ui/input';

interface DataTableToolbarProps {
    searchPlaceholder?: string;
    searchValue?: string;
    onSearchChange?: (value: string) => void;
    actions?: ReactNode;
}

export function DataTableToolbar({
    searchPlaceholder = 'Search records...',
    searchValue,
    onSearchChange,
    actions,
}: DataTableToolbarProps) {
    return (
        <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div className="w-full max-w-sm">
                <Input
                    value={searchValue ?? ''}
                    onChange={(event) => onSearchChange?.(event.target.value)}
                    placeholder={searchPlaceholder}
                />
            </div>
            {actions ? (
                <div className="flex items-center gap-2">{actions}</div>
            ) : null}
        </div>
    );
}
