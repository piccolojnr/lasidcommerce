import { Badge } from '@/components/ui/badge';
import { formatStatus } from '@/lib/formatters/status';

type StatusTone = 'default' | 'secondary' | 'destructive' | 'outline';

const toneMap: Record<string, StatusTone> = {
    active: 'default',
    published: 'default',
    confirmed: 'default',
    paid: 'default',
    fulfilled: 'default',
    completed: 'default',
    pending: 'secondary',
    packed: 'secondary',
    partially_fulfilled: 'secondary',
    shipment_pending: 'secondary',
    partially_shipped: 'secondary',
    partially_delivered: 'secondary',
    processing: 'secondary',
    shipped: 'secondary',
    in_transit: 'secondary',
    delivered: 'secondary',
    in_stock: 'default',
    low_stock: 'secondary',
    unpaid: 'outline',
    out_of_stock: 'destructive',
    unfulfilled: 'outline',
    no_shipment: 'outline',
    draft: 'outline',
    inactive: 'outline',
    attention_required: 'destructive',
    cancelled: 'destructive',
    failed: 'destructive',
    returned: 'destructive',
};

interface StatusBadgeProps {
    status: string;
}

export function StatusBadge({ status }: StatusBadgeProps) {
    const normalized = status.toLowerCase();

    return (
        <Badge variant={toneMap[normalized] ?? 'outline'}>
            {formatStatus(status)}
        </Badge>
    );
}
