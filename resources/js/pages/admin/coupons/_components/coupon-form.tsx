import { Form } from '@inertiajs/react';
import { useState } from 'react';
import * as CouponController from '@/actions/App/Http/Controllers/Admin/Coupons/CouponController';
import { FieldError } from '@/components/shared/forms/field-error';
import { FormActions } from '@/components/shared/forms/form-actions';
import { FormSection } from '@/components/shared/forms/form-section';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { cn } from '@/lib/utils';
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

function formatMinorUnits(value: number | null | undefined): string {
    if (value == null) {
        return '—';
    }

    return new Intl.NumberFormat().format(value);
}

export function CouponForm({ coupon }: CouponFormProps) {
    const isEdit = coupon !== undefined;
    const [isActive, setIsActive] = useState(coupon?.is_active ?? true);
    const [couponType, setCouponType] = useState(coupon?.type ?? 'fixed');
    const formProps = isEdit
        ? CouponController.update.form.put(coupon)
        : CouponController.store.form.post();

    const submitLabel = isEdit ? 'Update coupon' : 'Create coupon';
    const validityState = isActive
        ? (coupon?.is_currently_valid ?? true)
            ? 'Ready to redeem'
            : 'Scheduled or expired'
        : 'Disabled';

    return (
        <Form
            {...formProps}
            options={{ preserveScroll: true }}
            className="space-y-8"
        >
            {({ errors }) => (
                <>
                    <input
                        type="hidden"
                        name="is_active"
                        value={isActive ? '1' : '0'}
                    />
                    <input type="hidden" name="type" value={couponType} />

                    <div className="grid gap-4 lg:grid-cols-3">
                        <div className="lg:col-span-2">
                            <div className="rounded-[2rem] border border-border/70 bg-muted/30 p-6 shadow-sm">
                                <div className="flex flex-wrap items-start justify-between gap-4">
                                    <div className="space-y-2">
                                        <p className="text-xs font-semibold tracking-[0.24em] text-muted-foreground uppercase">
                                            Promotion setup
                                        </p>
                                        <h2 className="text-2xl font-semibold tracking-tight">
                                            {isEdit
                                                ? coupon.code
                                                : 'New coupon'}
                                        </h2>
                                        <p className="max-w-2xl text-sm leading-6 text-muted-foreground">
                                            Configure discount mechanics,
                                            guardrails, and validity windows
                                            without guessing what each field
                                            means.
                                        </p>
                                    </div>

                                    <div className="grid min-w-56 gap-3 sm:grid-cols-2">
                                        <div className="rounded-2xl border border-border/70 bg-background/80 p-4">
                                            <p className="text-xs font-semibold tracking-[0.2em] text-muted-foreground uppercase">
                                                Discount mode
                                            </p>
                                            <p className="mt-2 text-lg font-semibold">
                                                {couponType === 'percentage'
                                                    ? 'Percentage'
                                                    : 'Fixed amount'}
                                            </p>
                                        </div>
                                        <div className="rounded-2xl border border-border/70 bg-background/80 p-4">
                                            <p className="text-xs font-semibold tracking-[0.2em] text-muted-foreground uppercase">
                                                Redemption state
                                            </p>
                                            <p className="mt-2 text-lg font-semibold">
                                                {validityState}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div className="rounded-[2rem] border border-border/70 bg-primary/5 p-6 shadow-sm">
                            <div className="space-y-4">
                                <div>
                                    <p className="text-xs font-semibold tracking-[0.24em] text-primary uppercase">
                                        Operational view
                                    </p>
                                    <h3 className="mt-2 text-xl font-semibold tracking-tight">
                                        {isActive
                                            ? 'Coupon is enabled'
                                            : 'Coupon is disabled'}
                                    </h3>
                                </div>

                                <div className="space-y-3 text-sm">
                                    <div className="flex items-center justify-between">
                                        <span className="text-muted-foreground">
                                            Used count
                                        </span>
                                        <span className="font-medium">
                                            {coupon?.used_count ?? 0}
                                        </span>
                                    </div>
                                    <div className="flex items-center justify-between">
                                        <span className="text-muted-foreground">
                                            Usage limit
                                        </span>
                                        <span className="font-medium">
                                            {coupon?.usage_limit ?? 'Unlimited'}
                                        </span>
                                    </div>
                                    <div className="flex items-center justify-between">
                                        <span className="text-muted-foreground">
                                            Starts
                                        </span>
                                        <span className="font-medium">
                                            {coupon?.starts_at
                                                ? new Date(
                                                      coupon.starts_at,
                                                  ).toLocaleDateString()
                                                : 'Immediately'}
                                        </span>
                                    </div>
                                    <div className="flex items-center justify-between">
                                        <span className="text-muted-foreground">
                                            Expires
                                        </span>
                                        <span className="font-medium">
                                            {coupon?.expires_at
                                                ? new Date(
                                                      coupon.expires_at,
                                                  ).toLocaleDateString()
                                                : 'No expiry'}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div className="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
                        <div className="space-y-6">
                            <FormSection
                                title="Coupon identity"
                                description="Define the code customers enter and the core discount behavior."
                                badge="Basics"
                                contentClassName="p-6"
                            >
                                <div className="grid gap-4 md:grid-cols-2">
                                    <div className="space-y-2">
                                        <Label htmlFor="code">
                                            Coupon code
                                        </Label>
                                        <Input
                                            id="code"
                                            name="code"
                                            defaultValue={coupon?.code ?? ''}
                                            placeholder="WELCOME10"
                                            className="h-11 font-mono uppercase"
                                        />
                                        <p className="text-xs text-muted-foreground">
                                            Keep it short, obvious, and easy to
                                            type.
                                        </p>
                                        <FieldError message={errors.code} />
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="type-select">
                                            Discount type
                                        </Label>
                                        <Select
                                            value={couponType}
                                            onValueChange={setCouponType}
                                        >
                                            <SelectTrigger
                                                id="type-select"
                                                className="h-11"
                                            >
                                                <SelectValue placeholder="Select type" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="fixed">
                                                    Fixed amount
                                                </SelectItem>
                                                <SelectItem value="percentage">
                                                    Percentage
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>
                                        <p className="text-xs text-muted-foreground">
                                            Fixed discounts use minor units.
                                            Percentage discounts use
                                            whole-number percentages.
                                        </p>
                                        <FieldError message={errors.type} />
                                    </div>
                                </div>
                            </FormSection>

                            <FormSection
                                title="Discount economics"
                                description="Set the discount value and the minimum or maximum spend guardrails."
                                badge="Value"
                                contentClassName="p-6"
                            >
                                <div className="grid gap-4 md:grid-cols-3">
                                    <div className="space-y-2">
                                        <Label htmlFor="value">
                                            {couponType === 'percentage'
                                                ? 'Percentage value'
                                                : 'Discount value'}
                                        </Label>
                                        <Input
                                            id="value"
                                            name="value"
                                            type="number"
                                            min="0"
                                            defaultValue={coupon?.value ?? ''}
                                            placeholder={
                                                couponType === 'percentage'
                                                    ? '10'
                                                    : '1000'
                                            }
                                            className="h-11"
                                        />
                                        <p className="text-xs text-muted-foreground">
                                            {couponType === 'percentage'
                                                ? 'Example: 10 means 10% off.'
                                                : 'Stored in minor units. Example: 1000 = 10.00.'}
                                        </p>
                                        <FieldError message={errors.value} />
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="minimum_order_amount">
                                            Minimum order amount
                                        </Label>
                                        <Input
                                            id="minimum_order_amount"
                                            name="minimum_order_amount"
                                            type="number"
                                            min="0"
                                            defaultValue={
                                                coupon?.minimum_order_amount ??
                                                ''
                                            }
                                            placeholder="5000"
                                            className="h-11"
                                        />
                                        <p className="text-xs text-muted-foreground">
                                            Leave blank if no minimum basket
                                            value is required.
                                        </p>
                                        <FieldError
                                            message={
                                                errors.minimum_order_amount
                                            }
                                        />
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="maximum_discount_amount">
                                            Maximum discount
                                        </Label>
                                        <Input
                                            id="maximum_discount_amount"
                                            name="maximum_discount_amount"
                                            type="number"
                                            min="0"
                                            defaultValue={
                                                coupon?.maximum_discount_amount ??
                                                ''
                                            }
                                            placeholder="2500"
                                            className="h-11"
                                        />
                                        <p className="text-xs text-muted-foreground">
                                            Useful for percentage coupons so
                                            they do not run wild.
                                        </p>
                                        <FieldError
                                            message={
                                                errors.maximum_discount_amount
                                            }
                                        />
                                    </div>
                                </div>
                            </FormSection>

                            <FormSection
                                title="Usage and validity"
                                description="Control how often the code can be redeemed and when it becomes live."
                                badge="Rules"
                                contentClassName="p-6"
                            >
                                <div className="grid gap-4 md:grid-cols-3">
                                    <div className="space-y-2">
                                        <Label htmlFor="usage_limit">
                                            Usage limit
                                        </Label>
                                        <Input
                                            id="usage_limit"
                                            name="usage_limit"
                                            type="number"
                                            min="1"
                                            defaultValue={
                                                coupon?.usage_limit ?? ''
                                            }
                                            placeholder="100"
                                            className="h-11"
                                        />
                                        <p className="text-xs text-muted-foreground">
                                            Leave blank for unlimited
                                            redemptions.
                                        </p>
                                        <FieldError
                                            message={errors.usage_limit}
                                        />
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="starts_at">
                                            Starts at
                                        </Label>
                                        <Input
                                            id="starts_at"
                                            name="starts_at"
                                            type="datetime-local"
                                            defaultValue={toDateTimeLocalValue(
                                                coupon?.starts_at ?? null,
                                            )}
                                            className="h-11"
                                        />
                                        <FieldError
                                            message={errors.starts_at}
                                        />
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="expires_at">
                                            Expires at
                                        </Label>
                                        <Input
                                            id="expires_at"
                                            name="expires_at"
                                            type="datetime-local"
                                            defaultValue={toDateTimeLocalValue(
                                                coupon?.expires_at ?? null,
                                            )}
                                            className="h-11"
                                        />
                                        <FieldError
                                            message={errors.expires_at}
                                        />
                                    </div>
                                </div>

                                <div className="rounded-2xl border border-border/70 bg-muted/30 p-4">
                                    <div className="flex items-start gap-3">
                                        <Checkbox
                                            id="is_active"
                                            checked={isActive}
                                            onCheckedChange={(checked) =>
                                                setIsActive(Boolean(checked))
                                            }
                                            className="mt-0.5"
                                        />
                                        <div className="space-y-1">
                                            <Label
                                                htmlFor="is_active"
                                                className="cursor-pointer"
                                            >
                                                Active and available for
                                                redemption
                                            </Label>
                                            <p className="text-sm text-muted-foreground">
                                                Disable this when you want to
                                                keep the code for records
                                                without allowing new
                                                redemptions.
                                            </p>
                                        </div>
                                    </div>
                                    <FieldError
                                        message={errors.is_active}
                                        className="mt-3"
                                    />
                                </div>
                            </FormSection>
                        </div>

                        <div className="space-y-6">
                            <div className="sticky top-6 space-y-6">
                                <FormSection
                                    title="Quick read"
                                    description="A compact operations summary while you edit."
                                    badge="Summary"
                                    contentClassName="p-6"
                                >
                                    <div className="grid gap-3">
                                        <div className="rounded-2xl border border-border/70 bg-background/80 p-4">
                                            <p className="text-xs font-semibold tracking-[0.2em] text-muted-foreground uppercase">
                                                Existing usage
                                            </p>
                                            <p className="mt-2 text-2xl font-semibold">
                                                {coupon?.used_count ?? 0}
                                            </p>
                                        </div>
                                        <div className="rounded-2xl border border-border/70 bg-background/80 p-4">
                                            <p className="text-xs font-semibold tracking-[0.2em] text-muted-foreground uppercase">
                                                Minimum spend
                                            </p>
                                            <p className="mt-2 text-lg font-semibold">
                                                {formatMinorUnits(
                                                    coupon?.minimum_order_amount,
                                                )}
                                            </p>
                                        </div>
                                        <div className="rounded-2xl border border-border/70 bg-background/80 p-4">
                                            <p className="text-xs font-semibold tracking-[0.2em] text-muted-foreground uppercase">
                                                Max discount
                                            </p>
                                            <p className="mt-2 text-lg font-semibold">
                                                {formatMinorUnits(
                                                    coupon?.maximum_discount_amount,
                                                )}
                                            </p>
                                        </div>
                                    </div>
                                </FormSection>

                                <div className="rounded-[1.75rem] border border-border/70 bg-secondary/40 p-5">
                                    <p className="text-xs font-semibold tracking-[0.24em] text-foreground/70 uppercase">
                                        Guardrails
                                    </p>
                                    <div className="mt-4 space-y-3 text-sm text-muted-foreground">
                                        <p>
                                            Fixed discounts and spending
                                            thresholds are stored in minor
                                            units.
                                        </p>
                                        <p>
                                            Percentage coupons should usually
                                            define a maximum discount cap.
                                        </p>
                                        <p>
                                            If both dates are blank, the coupon
                                            is valid immediately and
                                            indefinitely while active.
                                        </p>
                                    </div>
                                </div>

                                <FormActions
                                    submitLabel={submitLabel}
                                    onCancel={() => window.history.back()}
                                    children={
                                        <div
                                            className={cn(
                                                'mr-auto rounded-full border border-border/70 px-3 py-1 text-xs font-medium tracking-[0.18em] uppercase',
                                                isActive
                                                    ? 'bg-primary/5 text-primary'
                                                    : 'bg-muted text-muted-foreground',
                                            )}
                                        >
                                            {isActive ? 'Active' : 'Inactive'}
                                        </div>
                                    }
                                />
                            </div>
                        </div>
                    </div>
                </>
            )}
        </Form>
    );
}
