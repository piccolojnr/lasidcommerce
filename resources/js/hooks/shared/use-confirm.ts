import { useCallback, useState } from 'react';

interface UseConfirmOptions {
    title?: string;
    description?: string;
}

interface ConfirmState extends UseConfirmOptions {
    open: boolean;
}

export function useConfirm(initialState: UseConfirmOptions = {}) {
    const [state, setState] = useState<ConfirmState>({
        open: false,
        title: initialState.title,
        description: initialState.description,
    });

    const openConfirm = useCallback(
        (options: UseConfirmOptions = {}) => {
            setState({
                open: true,
                title: options.title ?? initialState.title,
                description: options.description ?? initialState.description,
            });
        },
        [initialState.description, initialState.title],
    );

    const closeConfirm = useCallback(() => {
        setState((current) => ({ ...current, open: false }));
    }, []);

    return {
        ...state,
        openConfirm,
        closeConfirm,
    };
}
