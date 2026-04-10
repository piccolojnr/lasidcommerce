import { Badge } from '@/components/ui/badge';
import { formatStatus } from '@/lib/formatters/status';

type StatusTone = 'default' | 'secondary' | 'destructive' | 'outline';

const toneMap: Record<string, StatusTone> = {
    active: 'default',
    published: 'default',
    paid: 'default',
    completed: 'default',
    pending: 'secondary',
    processing: 'secondary',
    draft: 'outline',
    inactive: 'outline',
    cancelled: 'destructive',
    failed: 'destructive',
};

interface StatusBadgeProps {
    status: string;
}

export function StatusBadge({ status }: StatusBadgeProps) {
    const normalized = status.toLowerCase();

    return <Badge variant={toneMap[normalized] ?? 'outline'}>{formatStatus(status)}</Badge>;
}
