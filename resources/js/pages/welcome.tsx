import { Head, Link, usePage } from '@inertiajs/react';
import {
    ArrowRight,
    Boxes,
    LayoutGrid,
    ShieldCheck,
    ShoppingCart,
    Truck,
    Users,
} from 'lucide-react';
import AppLogoIcon from '@/components/app-logo-icon';
import { login } from '@/routes';
import { dashboard } from '@/support/app-routes';

const sections = [
    {
        title: 'Dashboard',
        description: 'Monitor the current state of the workspace.',
        icon: LayoutGrid,
    },
    {
        title: 'Catalog',
        description: 'Manage products, brands, and category structure.',
        icon: Boxes,
    },
    {
        title: 'Orders',
        description: 'Review purchases, fulfillment progress, and exceptions.',
        icon: ShoppingCart,
    },
    {
        title: 'Shipments',
        description: 'Coordinate delivery workflows and warehouse activity.',
        icon: Truck,
    },
    {
        title: 'Users',
        description: 'Control staff access and operational ownership.',
        icon: Users,
    },
    {
        title: 'Settings',
        description: 'Maintain internal rules, permissions, and configuration.',
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

            <div className="min-h-screen bg-[radial-gradient(circle_at_top,rgba(15,23,42,0.08),transparent_40%),linear-gradient(180deg,#f8fafc_0%,#eef2f7_100%)] px-6 py-8 text-foreground dark:bg-[radial-gradient(circle_at_top,rgba(148,163,184,0.14),transparent_32%),linear-gradient(180deg,#020617_0%,#0f172a_100%)]">
                <div className="mx-auto flex min-h-[calc(100vh-4rem)] max-w-6xl flex-col rounded-[28px] border border-border/80 bg-background/90 shadow-[0_24px_80px_rgba(15,23,42,0.08)] backdrop-blur dark:shadow-[0_24px_80px_rgba(2,6,23,0.45)]">
                    <header className="flex flex-col gap-6 border-b border-border px-6 py-6 sm:px-8 lg:flex-row lg:items-center lg:justify-between">
                        <div className="flex items-center gap-4">
                            <div className="flex size-11 items-center justify-center rounded-2xl bg-foreground text-background shadow-sm">
                                <AppLogoIcon className="size-5 fill-current" />
                            </div>
                            <div>
                                <p className="text-xs font-medium tracking-[0.22em] text-muted-foreground uppercase">
                                    Internal application
                                </p>
                                <h1 className="text-lg font-semibold tracking-tight text-foreground">
                                    {appName}
                                </h1>
                            </div>
                        </div>

                        <div className="flex items-center gap-3">
                            <span className="hidden rounded-full border border-border bg-muted px-3 py-1 text-xs font-medium text-muted-foreground sm:inline-flex">
                                Staff workspace only
                            </span>
                            <Link
                                href={primaryHref}
                                className="inline-flex items-center gap-2 rounded-full bg-foreground px-4 py-2 text-sm font-medium text-background transition hover:opacity-90"
                            >
                                {primaryLabel}
                                <ArrowRight className="size-4" />
                            </Link>
                        </div>
                    </header>

                    <main className="grid flex-1 gap-10 px-6 py-10 sm:px-8 lg:grid-cols-[minmax(0,1.15fr)_minmax(320px,0.85fr)] lg:items-end lg:py-14">
                        <section className="max-w-2xl">
                            <div className="space-y-6">
                                <div className="space-y-3">
                                    <p className="text-sm font-medium text-muted-foreground">
                                        Commerce operations control center
                                    </p>
                                    <h2 className="text-4xl font-semibold tracking-tight text-foreground sm:text-5xl">
                                        A quiet front door for the team that
                                        runs the business.
                                    </h2>
                                    <p className="max-w-xl text-base leading-7 text-muted-foreground">
                                        Use this workspace to manage catalog
                                        data, orders, shipments, inventory,
                                        users, and configuration without the
                                        noise of a public-facing landing page.
                                    </p>
                                </div>

                                <div className="grid gap-3 sm:grid-cols-3">
                                    <div className="rounded-2xl border border-border bg-muted/60 px-4 py-4">
                                        <p className="text-xs font-medium tracking-[0.18em] text-muted-foreground uppercase">
                                            Audience
                                        </p>
                                        <p className="mt-2 text-sm font-medium text-foreground">
                                            Internal staff
                                        </p>
                                    </div>
                                    <div className="rounded-2xl border border-border bg-muted/60 px-4 py-4">
                                        <p className="text-xs font-medium tracking-[0.18em] text-muted-foreground uppercase">
                                            Surface
                                        </p>
                                        <p className="mt-2 text-sm font-medium text-foreground">
                                            Admin operations
                                        </p>
                                    </div>
                                    <div className="rounded-2xl border border-border bg-muted/60 px-4 py-4">
                                        <p className="text-xs font-medium tracking-[0.18em] text-muted-foreground uppercase">
                                            Access
                                        </p>
                                        <p className="mt-2 text-sm font-medium text-foreground">
                                            Authenticated only
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section className="rounded-[24px] border border-border bg-muted/40 p-4 sm:p-5">
                            <div className="mb-4 flex items-center justify-between">
                                <div>
                                    <p className="text-sm font-medium text-foreground">
                                        Workspace areas
                                    </p>
                                    <p className="text-sm text-muted-foreground">
                                        Core modules available to operators.
                                    </p>
                                </div>
                                <span className="rounded-full bg-background px-3 py-1 text-xs font-medium text-muted-foreground shadow-sm">
                                    {sections.length} modules
                                </span>
                            </div>

                            <div className="grid gap-3">
                                {sections.map((section) => {
                                    const Icon = section.icon;

                                    return (
                                        <div
                                            key={section.title}
                                            className="flex items-start gap-3 rounded-2xl border border-border bg-background px-4 py-4"
                                        >
                                            <div className="mt-0.5 flex size-10 items-center justify-center rounded-xl bg-muted text-muted-foreground">
                                                <Icon className="size-4" />
                                            </div>
                                            <div className="min-w-0">
                                                <p className="text-sm font-semibold text-foreground">
                                                    {section.title}
                                                </p>
                                                <p className="mt-1 text-sm leading-6 text-muted-foreground">
                                                    {section.description}
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
