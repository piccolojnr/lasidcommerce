import type { ReactNode } from 'react';
import { Button } from '@/components/ui/button';

interface EmptyStateProps {
    title: string;
    description?: string;
    actionLabel?: string;
    onAction?: () => void;
    icon?: ReactNode;
}

export function EmptyState({ title, description, actionLabel, onAction, icon }: EmptyStateProps) {
    return (
        <div className="flex min-h-64 flex-col items-center justify-center rounded-lg border border-dashed px-6 py-12 text-center">
            {icon ? <div className="mb-4 text-muted-foreground">{icon}</div> : null}
            <div className="space-y-2">
                <h3 className="text-lg font-semibold">{title}</h3>
                {description ? <p className="text-sm text-muted-foreground">{description}</p> : null}
            </div>
            {actionLabel && onAction ? (
                <Button className="mt-6" onClick={onAction}>
                    {actionLabel}
                </Button>
            ) : null}
        </div>
    );
}
