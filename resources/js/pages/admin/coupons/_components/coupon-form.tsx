import { FormActions } from '@/components/shared/forms/form-actions';
import { FormSection } from '@/components/shared/forms/form-section';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

export function CouponForm() {
    return (
        <form className="space-y-6">
            <FormSection title="Coupon details" description="Configure the marketing code structure here.">
                <div className="grid gap-4 md:grid-cols-2">
                    <div className="space-y-2">
                        <Label htmlFor="coupon-code">Code</Label>
                        <Input id="coupon-code" placeholder="WELCOME10" />
                    </div>
                    <div className="space-y-2">
                        <Label htmlFor="coupon-type">Type</Label>
                        <Input id="coupon-type" placeholder="fixed" />
                    </div>
                </div>
                <div className="space-y-2">
                    <Label htmlFor="coupon-value">Value</Label>
                    <Input id="coupon-value" placeholder="1000" />
                </div>
            </FormSection>
            <FormActions />
        </form>
    );
}
