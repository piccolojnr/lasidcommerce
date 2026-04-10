import type { PaginationMeta } from '@/types/shared/pagination';

export interface AdminUser {
    id: number;
    first_name: string;
    last_name: string;
    email: string;
    status: string;
    created_at?: string;
}

export interface AdminUserListPage {
    data: AdminUser[];
    meta?: PaginationMeta;
}
