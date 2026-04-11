import type { PaginationMeta } from '@/types/shared/pagination';

export interface AdminUser {
    id: number;
    name: string;
    email: string;
    status: string;
    created_at?: string;
}

export interface AdminUserListPage {
    data: AdminUser[];
    meta?: PaginationMeta;
}
