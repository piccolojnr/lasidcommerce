import type { PaginationMeta } from '@/types/shared/pagination';

export interface ApiEnvelope<T> {
    success: boolean;
    message: string | null;
    data: T;
    errors: Record<string, string[]> | null;
}

export interface PaginatedApiEnvelope<T> extends ApiEnvelope<T[]> {
    meta: PaginationMeta;
}
