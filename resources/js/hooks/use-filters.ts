import { router } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';

type FilterValue = string | number | boolean | null | undefined;

export function useFilters<T extends Record<string, FilterValue>>(route: string, serverFilters: T) {
    const [search, setSearch] = useState<string>((serverFilters.search as string) ?? '');
    const isFirstRender = useRef(true);
    const latestFilters = useRef<Record<string, FilterValue>>(serverFilters);
    latestFilters.current = serverFilters;

    // Sync search draft when Inertia navigates (e.g. back button)
    useEffect(() => {
        setSearch((serverFilters.search as string) ?? '');
    }, [serverFilters.search]);

    // Debounce search → navigate
    useEffect(() => {
        if (isFirstRender.current) {
            isFirstRender.current = false;

            return;
        }

        const timer = setTimeout(
            () => navigate({ ...latestFilters.current, search: search || null }),
            300,
        );

        return () => clearTimeout(timer);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [search]);

    function navigate(filters: Record<string, FilterValue>) {
        const params = Object.fromEntries(
            Object.entries(filters).filter(([, v]) => v !== null && v !== undefined && v !== ''),
        ) as Record<string, string>;
        router.get(route, params, { preserveState: true, preserveScroll: true, replace: true });
    }

    function setFilter(key: string, value: FilterValue) {
        navigate({ ...latestFilters.current, search: search || null, [key]: value });
    }

    return { search, setSearch, setFilter };
}
