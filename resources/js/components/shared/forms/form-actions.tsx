import type { ReactNode } from 'react';
import { Button } from '@/components/ui/button';

interface FormActionsProps {
    submitLabel?: string;
    cancelLabel?: string;
    onCancel?: () => void;
    children?: ReactNode;
}

export function FormActions({
    submitLabel = 'Save changes',
    cancelLabel = 'Cancel',
    onCancel,
    children,
}: FormActionsProps) {
    return (
        <div className="flex flex-wrap items-center justify-end gap-3">
            {children}
            {onCancel ? (
                <Button type="button" variant="outline" onClick={onCancel}>
                    {cancelLabel}
                </Button>
            ) : null}
            <Button type="submit">{submitLabel}</Button>
        </div>
    );
}
