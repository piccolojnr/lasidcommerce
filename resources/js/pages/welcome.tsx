import { Head, Link, usePage } from '@inertiajs/react';
import { ArrowRight, LayoutGrid, ShieldCheck, Truck } from 'lucide-react';
import AppLogoIcon from '@/components/app-logo-icon';
import { login } from '@/routes';
import { dashboard } from '@/support/app-routes';

const highlights = [
    {
        title: 'Catalog and orders',
        description: 'Keep products current and purchases moving.',
        icon: LayoutGrid,
    },
    {
        title: 'Fulfillment',
        description: 'Track stock, shipment flow, and operational handoff.',
        icon: Truck,
    },
    {
        title: 'Access control',
        description: 'Manage staff permissions inside one admin surface.',
        icon: ShieldCheck,
    },
] as const;

export default function Welcome() {
    const { auth } = usePage().props as {
        auth: {
            user?: unknown;
        };
    };

    const appName = import.meta.env.VITE_APP_NAME || 'Operations Workspace';
    const primaryHref = auth.user ? dashboard() : login();
    const primaryLabel = auth.user ? 'Open dashboard' : 'Sign in';

    return (
        <>
            <Head title="Workspace" />

            <div className="min-h-screen bg-[linear-gradient(180deg,color-mix(in_oklab,var(--background)_86%,white)_0%,var(--background)_100%)] px-6 py-4 text-foreground">
                <div className="mx-auto flex min-h-[calc(100vh-4rem)] max-w-6xl flex-col rounded-[32px] border border-border/80 bg-background/95 shadow-xl">
                    <header className="flex items-center justify-between gap-4 border-b border-border/80 px-6 py-5 sm:px-8">
                        <div className="flex items-center gap-4">
                            <div className="flex size-11 items-center justify-center rounded-2xl bg-primary text-primary-foreground shadow-sm">
                                <AppLogoIcon className="size-5 fill-current" />
                            </div>
                            <div>
                                <p className="text-xs font-medium tracking-[0.2em] text-muted-foreground uppercase">
                                    Internal workspace
                                </p>
                                <h1 className="text-base font-semibold tracking-tight sm:text-lg">
                                    {appName}
                                </h1>
                            </div>
                        </div>

                        <Link
                            href={primaryHref}
                            className="inline-flex items-center gap-2 rounded-full bg-foreground px-4 py-2 text-sm font-medium text-background transition hover:opacity-90"
                        >
                            {primaryLabel}
                            <ArrowRight className="size-4" />
                        </Link>
                    </header>

                    <main className="grid flex-1 gap-10 px-6 py-4 sm:px-8 lg:grid-cols-[minmax(0,1.1fr)_360px] lg:items-center lg:gap-4 lg:py-6">
                        <section className="max-w-2xl">
                            <div className="inline-flex rounded-full border border-border bg-muted px-3 py-1 text-xs font-medium text-muted-foreground">
                                Commerce admin
                            </div>

                            <div className="mt-6 space-y-5">
                                <h2 className="max-w-xl text-4xl font-semibold tracking-tight text-balance sm:text-5xl">
                                    One calm place to run daily ecommerce
                                    operations.
                                </h2>
                                <p className="max-w-xl text-base leading-7 text-muted-foreground sm:text-lg">
                                    This entry page now stays focused: staff
                                    sign in, continue into the dashboard, and
                                    understand what the workspace is for without
                                    scanning a full product tour.
                                </p>
                            </div>

                            <div className="mt-8 flex flex-wrap items-center gap-3">
                                <Link
                                    href={primaryHref}
                                    className="inline-flex items-center gap-2 rounded-full bg-primary px-5 py-3 text-sm font-medium text-primary-foreground transition hover:opacity-90"
                                >
                                    {primaryLabel}
                                    <ArrowRight className="size-4" />
                                </Link>
                                <div className="rounded-full border border-border px-4 py-3 text-sm text-muted-foreground">
                                    Staff-only access
                                </div>
                            </div>
                        </section>

                        <section className="rounded-[28px] border border-border bg-muted/50 p-4 sm:p-5">
                            <div className="rounded-[22px] bg-background p-5 shadow-sm">
                                <p className="text-sm font-semibold text-foreground">
                                    What happens here
                                </p>
                                <p className="mt-1 text-sm leading-6 text-muted-foreground">
                                    The workspace is organized around a few core
                                    responsibilities, not a crowded landing
                                    page.
                                </p>
                            </div>

                            <div className="mt-3 space-y-3">
                                {highlights.map((highlight) => {
                                    const Icon = highlight.icon;

                                    return (
                                        <div
                                            key={highlight.title}
                                            className="flex items-start gap-3 rounded-[22px] border border-border bg-background px-4 py-4"
                                        >
                                            <div className="flex size-10 shrink-0 items-center justify-center rounded-2xl bg-primary/10 text-primary">
                                                <Icon className="size-4" />
                                            </div>
                                            <div>
                                                <p className="text-sm font-semibold text-foreground">
                                                    {highlight.title}
                                                </p>
                                                <p className="mt-1 text-sm leading-6 text-muted-foreground">
                                                    {highlight.description}
                                                </p>
                                            </div>
                                        </div>
                                    );
                                })}
                            </div>
                        </section>
                    </main>
                </div>
            </div>
        </>
    );
}
