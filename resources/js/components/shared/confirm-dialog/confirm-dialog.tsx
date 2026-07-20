import { useCallback, useRef, useState } from 'react';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

// ─── Props ────────────────────────────────────────────────────────────────────

interface ConfirmDialogProps {
    open: boolean;
    title: string;
    description?: string;
    confirmLabel?: string;
    cancelLabel?: string;
    /** When true the confirm button renders with variant="destructive" */
    destructive?: boolean;
    onConfirm: () => void;
    onOpenChange: (open: boolean) => void;
}

// ─── Component ────────────────────────────────────────────────────────────────

export function ConfirmDialog({
    open,
    title,
    description,
    confirmLabel = 'Confirm',
    cancelLabel = 'Cancel',
    destructive = false,
    onConfirm,
    onOpenChange,
}: ConfirmDialogProps) {
    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="max-w-md">
                <DialogHeader>
                    <DialogTitle>{title}</DialogTitle>
                    {description ? (
                        <DialogDescription>{description}</DialogDescription>
                    ) : null}
                </DialogHeader>
                <DialogFooter className="gap-2 sm:gap-0">
                    <Button
                        variant="outline"
                        onClick={() => onOpenChange(false)}
                    >
                        {cancelLabel}
                    </Button>
                    <Button
                        variant={destructive ? 'destructive' : 'default'}
                        onClick={() => {
                            onConfirm();
                            onOpenChange(false);
                        }}
                    >
                        {confirmLabel}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}

// ─── Hook ─────────────────────────────────────────────────────────────────────

/**
 * Imperative confirm-dialog hook.
 *
 * Usage:
 *   const { confirmDialog, requestConfirm } = useConfirmDialog();
 *
 *   // In JSX:
 *   {confirmDialog}
 *
 *   // To trigger:
 *   requestConfirm({
 *     title: 'Delete product?',
 *     description: 'This action cannot be undone.',
 *     destructive: true,
 *     onConfirm: () => router.delete(url),
 *   });
 */

interface RequestConfirmOptions {
    title: string;
    description?: string;
    confirmLabel?: string;
    cancelLabel?: string;
    destructive?: boolean;
    onConfirm: () => void;
}

interface ConfirmDialogState extends RequestConfirmOptions {
    open: boolean;
}

export function useConfirmDialog() {
    const [state, setState] = useState<ConfirmDialogState>({
        open: false,
        title: '',
        onConfirm: () => {},
    });

    // Keep a stable ref so callers don't need to worry about stale closures.
    const pendingAction = useRef<(() => void) | null>(null);

    const requestConfirm = useCallback((options: RequestConfirmOptions) => {
        pendingAction.current = options.onConfirm;
        setState({ ...options, open: true });
    }, []);

    const handleConfirm = useCallback(() => {
        pendingAction.current?.();
        pendingAction.current = null;
    }, []);

    const handleOpenChange = useCallback((open: boolean) => {
        if (!open) pendingAction.current = null;
        setState((prev) => ({ ...prev, open }));
    }, []);

    const confirmDialog = (
        <ConfirmDialog
            open={state.open}
            title={state.title}
            description={state.description}
            confirmLabel={state.confirmLabel ?? 'Confirm'}
            cancelLabel={state.cancelLabel ?? 'Cancel'}
            destructive={state.destructive ?? false}
            onConfirm={handleConfirm}
            onOpenChange={handleOpenChange}
        />
    );

    return { confirmDialog, requestConfirm };
}
