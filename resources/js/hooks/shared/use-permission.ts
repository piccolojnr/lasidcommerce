import { usePage } from '@inertiajs/react';
import { useMemo } from 'react';
import { hasPermission  } from '@/lib/permissions';
import type {AdminPermission} from '@/lib/permissions';
import type { AuthState } from '@/types/shared/auth';

interface PermissionPageProps {
    auth?: AuthState;
    [key: string]: unknown;
}

export function usePermission(explicitPermissions?: string[]) {
    const page = usePage<PermissionPageProps>();
    const pagePermissions = page.props.auth?.permissions ?? page.props.auth?.user?.permissions ?? [];
    const permissions = explicitPermissions ?? pagePermissions;

    const can = useMemo(() => {
        return (permission: AdminPermission) => hasPermission(permissions, permission);
    }, [permissions]);

    return {
        permissions,
        can,
    };
}
