import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';

interface FulfillmentGuideDialogProps {
    triggerLabel?: string;
}

export function FulfillmentGuideDialog({
    triggerLabel = 'How this workflow works',
}: FulfillmentGuideDialogProps) {
    return (
        <Dialog>
            <DialogTrigger asChild>
                <Button type="button" variant="ghost" size="sm">
                    {triggerLabel}
                </Button>
            </DialogTrigger>
            <DialogContent className="max-w-2xl">
                <DialogHeader>
                    <DialogTitle>Order fulfillment guide</DialogTitle>
                    <DialogDescription>
                        Use order status for business handling, fulfillment status for quantity coverage, and shipment
                        status for physical delivery attempts.
                    </DialogDescription>
                </DialogHeader>

                <div className="space-y-5 text-sm text-muted-foreground">
                    <section className="space-y-2">
                        <h3 className="font-medium text-foreground">1. Order status</h3>
                        <p>
                            Order status is the business workflow only: review, confirmation, processing, cancellation,
                            and final completion.
                        </p>
                        <p>
                            It should not be used to describe package movement. That belongs to shipments.
                        </p>
                    </section>

                    <section className="space-y-2">
                        <h3 className="font-medium text-foreground">2. Fulfillment status</h3>
                        <p>
                            Fulfillment status tells you whether item quantities have been covered: none, partial, or
                            complete.
                        </p>
                        <p>
                            It changes from shipment quantities and outcomes, not from manual business-stage buttons.
                        </p>
                    </section>

                    <section className="space-y-2">
                        <h3 className="font-medium text-foreground">3. Shipment status</h3>
                        <p>
                            Shipment status tracks real movement: pending, packed, shipped, in transit, delivered,
                            failed, returned, or cancelled.
                        </p>
                        <p>
                            A returned or failed shipment is a closed attempt. If quantities still remain, create a new
                            shipment from the order page instead of reusing the old one.
                        </p>
                    </section>

                    <section className="space-y-2">
                        <h3 className="font-medium text-foreground">4. Common path</h3>
                        <p>
                            Move the order to <span className="font-medium text-foreground">processing</span>, create a
                            shipment, update the shipment as it moves, and mark the order{' '}
                            <span className="font-medium text-foreground">completed</span> only after the business
                            considers the case done.
                        </p>
                    </section>
                </div>
            </DialogContent>
        </Dialog>
    );
}
