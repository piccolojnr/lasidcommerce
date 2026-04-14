import { Form } from '@inertiajs/react';
import { useState } from 'react';
import * as CouponController from '@/actions/App/Http/Controllers/Admin/Coupons/CouponController';
import { FieldError } from '@/components/shared/forms/field-error';
import { FormActions } from '@/components/shared/forms/form-actions';
import { FormSection } from '@/components/shared/forms/form-section';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { AdminCouponDetail } from '@/types/admin/coupon';

interface CouponFormProps {
    coupon?: AdminCouponDetail;
}

function toDateTimeLocalValue(value: string | null): string {
    if (!value) {
        return '';
    }

    const date = new Date(value);
    const offset = date.getTimezoneOffset() * 60_000;

    return new Date(date.getTime() - offset).toISOString().slice(0, 16);
}

export function CouponForm({ coupon }: CouponFormProps) {
    const isEdit = coupon !== undefined;
    const [isActive, setIsActive] = useState(coupon?.is_active ?? true);
    const formProps = isEdit
        ? CouponController.update.form.put(coupon)
        : CouponController.store.form.post();

    return (
        <Form
            {...formProps}
            options={{ preserveScroll: true }}
            className="space-y-6"
        >
            {({ errors }) => (
                <>
                    <input type="hidden" name="is_active" value={isActive ? '1' : '0'} />

                    <FormSection
                        title="Coupon details"
                        description="Configure the discount code, amount, and eligibility rules."
                    >
                        <div className="grid gap-4 md:grid-cols-2">
                            <div className="space-y-2">
                                <Label htmlFor="code">Code</Label>
                                <Input
                                    id="code"
                                    name="code"
                                    defaultValue={coupon?.code ?? ''}
                                    placeholder="WELCOME10"
                                    className="font-mono uppercase"
                                />
                                <FieldError message={errors.code} />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="type">Type</Label>
                                <Input
                                    id="type"
                                    name="type"
                                    defaultValue={coupon?.type ?? 'fixed'}
                                    placeholder="fixed"
                                />
                                <FieldError message={errors.type} />
                            </div>
                        </div>

                        <div className="grid gap-4 md:grid-cols-3">
                            <div className="space-y-2">
                                <Label htmlFor="value">Value</Label>
                                <Input
                                    id="value"
                                    name="value"
                                    type="number"
                                    min="0"
                                    defaultValue={coupon?.value ?? ''}
                                    placeholder="1000"
                                />
                                <p className="text-xs text-muted-foreground">Stored in minor units.</p>
                                <FieldError message={errors.value} />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="minimum_order_amount">Minimum order amount</Label>
                                <Input
                                    id="minimum_order_amount"
                                    name="minimum_order_amount"
                                    type="number"
                                    min="0"
                                    defaultValue={coupon?.minimum_order_amount ?? ''}
                                    placeholder="5000"
                                />
                                <FieldError message={errors.minimum_order_amount} />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="maximum_discount_amount">Maximum discount</Label>
                                <Input
                                    id="maximum_discount_amount"
                                    name="maximum_discount_amount"
                                    type="number"
                                    min="0"
                                    defaultValue={coupon?.maximum_discount_amount ?? ''}
                                    placeholder="2500"
                                />
                                <FieldError message={errors.maximum_discount_amount} />
                            </div>
                        </div>
                    </FormSection>

                    <FormSection
                        title="Usage controls"
                        description="Limit how often the coupon can be redeemed and when it becomes valid."
                    >
                        <div className="grid gap-4 md:grid-cols-3">
                            <div className="space-y-2">
                                <Label htmlFor="usage_limit">Usage limit</Label>
                                <Input
                                    id="usage_limit"
                                    name="usage_limit"
                                    type="number"
                                    min="1"
                                    defaultValue={coupon?.usage_limit ?? ''}
                                    placeholder="100"
                                />
                                <FieldError message={errors.usage_limit} />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="starts_at">Starts at</Label>
                                <Input
                                    id="starts_at"
                                    name="starts_at"
                                    type="datetime-local"
                                    defaultValue={toDateTimeLocalValue(coupon?.starts_at ?? null)}
                                />
                                <FieldError message={errors.starts_at} />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="expires_at">Expires at</Label>
                                <Input
                                    id="expires_at"
                                    name="expires_at"
                                    type="datetime-local"
                                    defaultValue={toDateTimeLocalValue(coupon?.expires_at ?? null)}
                                />
                                <FieldError message={errors.expires_at} />
                            </div>
                        </div>

                        <div className="flex items-center gap-3">
                            <Checkbox
                                id="is_active"
                                checked={isActive}
                                onCheckedChange={(checked) => setIsActive(Boolean(checked))}
                            />
                            <Label htmlFor="is_active" className="cursor-pointer font-normal">
                                Active and available for redemption
                            </Label>
                        </div>
                        <FieldError message={errors.is_active} />
                    </FormSection>

                    <FormActions
                        submitLabel={isEdit ? 'Update coupon' : 'Create coupon'}
                        onCancel={() => window.history.back()}
                    />
                </>
            )}
        </Form>
    );
}
