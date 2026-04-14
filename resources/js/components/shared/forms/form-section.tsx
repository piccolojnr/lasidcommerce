import type { ReactNode } from 'react';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { cn } from '@/lib/utils';

interface FormSectionProps {
    title: string;
    description?: string;
    children: ReactNode;
    className?: string;
    contentClassName?: string;
    badge?: string;
}

export function FormSection({
    title,
    description,
    children,
    className,
    contentClassName,
    badge,
}: FormSectionProps) {
    return (
        <Card className={cn('border-border/70 pt-0 shadow-sm', className)}>
            <CardHeader className="gap-3 border-b border-border/60 bg-muted/20 py-6">
                {badge ? (
                    <div className="w-fit rounded-full border border-border/70 bg-background px-2.5 py-1 text-[11px] font-medium tracking-[0.18em] text-muted-foreground uppercase">
                        {badge}
                    </div>
                ) : null}
                <CardTitle>{title}</CardTitle>
                {description ? (
                    <CardDescription>{description}</CardDescription>
                ) : null}
            </CardHeader>
            <CardContent className={cn('space-y-5', contentClassName)}>
                {children}
            </CardContent>
        </Card>
    );
}
