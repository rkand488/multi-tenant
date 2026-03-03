<script setup>
import { onMounted } from 'vue';
import {
    BuildingOffice2Icon,
    UsersIcon,
    ShieldCheckIcon,
    CreditCardIcon,
    CodeBracketIcon,
    ChartBarIcon,
    CircleStackIcon,
    LockClosedIcon,
    ArrowPathIcon,
    BellAlertIcon,
    ServerStackIcon,
    CommandLineIcon,
} from '@heroicons/vue/24/outline';

// ── Feature definitions ────────────────────────────────────────────────────────

const features = [
    {
        icon: BuildingOffice2Icon,
        tag: 'Infrastructure',
        title: 'Multi-Tenant Infrastructure',
        description:
            'Every tenant gets a fully isolated database and workspace. Zero data bleed by design — provision a new workspace in under two seconds with automated pipeline tooling.',
        accent: 'indigo',
        bullets: ['Isolated DB per tenant', 'Auto-provisioning', 'Custom domain support'],
    },
    {
        icon: UsersIcon,
        tag: 'Collaboration',
        title: 'Team Collaboration',
        description:
            'Invite teammates with a single link, manage workspace access, and keep everyone aligned with shared activity feeds and real-time notifications across your organisation.',
        accent: 'violet',
        bullets: ['Invite via email link', 'Shared activity feed', 'Workspace switching'],
    },
    {
        icon: ShieldCheckIcon,
        tag: 'Security',
        title: 'Advanced Permissions',
        description:
            'Fine-grained role-based access control spans platform admins and per-tenant roles. Define exactly what each user can read, write, or manage — down to the resource level.',
        accent: 'sky',
        bullets: ['Hierarchical RBAC', 'Per-resource policies', 'Audit log included'],
    },
    {
        icon: CreditCardIcon,
        tag: 'Billing',
        title: 'Subscription Billing',
        description:
            'Flexible plan tiers — monthly or annual — with usage limits, grace periods, and automatic proration. Powered by Stripe with webhook handling already wired up.',
        accent: 'emerald',
        bullets: ['Stripe integration', 'Proration & trials', 'Invoice history'],
    },
    {
        icon: CodeBracketIcon,
        tag: 'Developer',
        title: 'API Access',
        description:
            'Every tenant gets a versioned RESTful API secured with Sanctum token authentication. Full OpenAPI documentation is auto-generated and served per workspace.',
        accent: 'pink',
        bullets: ['Per-tenant tokens', 'OpenAPI docs', 'Rate limiting built in'],
    },
    {
        icon: ChartBarIcon,
        tag: 'Insights',
        title: 'Analytics Dashboard',
        description:
            'Real-time metrics for platform admins and per-tenant views. MRR trends, churn rate, user growth, storage usage, and API call volume — all in one place.',
        accent: 'amber',
        bullets: ['MRR & churn tracking', 'Per-tenant views', 'CSV export'],
    },
];

const accentMap = {
    indigo:  { bg: 'bg-primary-50',  icon: 'text-primary-600',  tag: 'bg-primary-50 text-primary-600',  bar: 'bg-primary-500',  ring: 'ring-primary-100',  hover: 'group-hover:border-primary-200' },
    violet:  { bg: 'bg-accent-50',  icon: 'text-accent-600',  tag: 'bg-accent-50 text-accent-600',  bar: 'bg-accent-500',  ring: 'ring-accent-100',  hover: 'group-hover:border-accent-200' },
    sky:     { bg: 'bg-secondary-50',     icon: 'text-secondary-600',     tag: 'bg-secondary-50 text-secondary-600',        bar: 'bg-secondary-500',     ring: 'ring-secondary-100',     hover: 'group-hover:border-secondary-200' },
    emerald: { bg: 'bg-success-50', icon: 'text-success-600', tag: 'bg-success-50 text-success-600', bar: 'bg-success-500', ring: 'ring-success-100', hover: 'group-hover:border-success-200' },
    pink:    { bg: 'bg-accent-50',    icon: 'text-accent-600',    tag: 'bg-accent-50 text-accent-600',      bar: 'bg-accent-500',    ring: 'ring-accent-100',    hover: 'group-hover:border-accent-200' },
    amber:   { bg: 'bg-warning-50',   icon: 'text-warning-600',   tag: 'bg-warning-50 text-warning-600',    bar: 'bg-warning-500',   ring: 'ring-warning-100',   hover: 'group-hover:border-warning-200' },
};

// ── Scroll-triggered animation ─────────────────────────────────────────────────

onMounted(() => {
    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                    observer.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.12 },
    );

    document.querySelectorAll('[data-feat-animate]').forEach((el) => {
        observer.observe(el);
    });
});
</script>

<template>
    <section id="features" class="relative overflow-hidden bg-white py-24 sm:py-32">

        <!-- Subtle background pattern -->
        <div class="pointer-events-none absolute inset-0 -z-10" aria-hidden="true">
            <div class="absolute inset-y-0 right-0 w-1/2 bg-gray-50/60" />
            <div class="absolute inset-0 opacity-[0.025] bg-hexagon-pattern" />
        </div>

        <div class="mx-auto max-w-7xl px-6">

            <!-- ── Section heading ──────────────────────────────────────────── -->
            <div class="mx-auto mb-20 max-w-2xl text-center" data-feat-animate>
                <p class="mb-3 text-xs font-semibold uppercase tracking-widest text-primary-600">
                    Platform Capabilities
                </p>
                <h2 class="text-3xl font-extrabold tracking-tight text-gray-900 sm:text-4xl lg:text-5xl">
                    Everything you need to run a
                    <span class="text-gradient-primary">
                        multi-tenant SaaS
                    </span>
                </h2>
                <p class="mt-5 text-lg leading-relaxed text-gray-500">
                    All the infrastructure pieces ship together — pre-wired, tested, and ready to extend. Focus on your product, not the plumbing.
                </p>
            </div>

            <!-- ── Feature cards grid ───────────────────────────────────────── -->
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="(feature, i) in features"
                    :key="feature.title"
                    data-feat-animate
                    :style="{ transitionDelay: (i % 3) * 80 + 'ms' }"
                    class="feat-card group relative flex flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-lg"
                    :class="accentMap[feature.accent].hover"
                >
                    <!-- Coloured top bar -->
                    <div class="h-1 w-full" :class="accentMap[feature.accent].bar" />

                    <div class="flex flex-1 flex-col p-6">

                        <!-- Icon + tag row -->
                        <div class="mb-5 flex items-start justify-between">
                            <div
                                class="flex size-12 items-center justify-center rounded-xl transition-transform duration-300 group-hover:scale-110"
                                :class="accentMap[feature.accent].bg"
                            >
                                <component
                                    :is="feature.icon"
                                    class="size-6"
                                    :class="accentMap[feature.accent].icon"
                                />
                            </div>
                            <span
                                class="rounded-full px-2.5 py-1 text-xs font-semibold"
                                :class="accentMap[feature.accent].tag"
                            >
                                {{ feature.tag }}
                            </span>
                        </div>

                        <!-- Title -->
                        <h3 class="mb-2.5 text-base font-bold text-gray-900 transition-colors group-hover:text-gray-700">
                            {{ feature.title }}
                        </h3>

                        <!-- Description -->
                        <p class="flex-1 text-sm leading-relaxed text-gray-500">
                            {{ feature.description }}
                        </p>

                        <!-- Bullet list -->
                        <ul class="mt-5 space-y-1.5 border-t border-gray-100 pt-5">
                            <li
                                v-for="bullet in feature.bullets"
                                :key="bullet"
                                class="flex items-center gap-2.5 text-xs font-medium text-gray-600"
                            >
                                <span
                                    class="flex size-4 shrink-0 items-center justify-center rounded-full"
                                    :class="accentMap[feature.accent].bg"
                                >
                                    <svg viewBox="0 0 12 12" class="size-2.5" :class="accentMap[feature.accent].icon" fill="none">
                                        <path d="M2 6.5l2.5 2.5 5.5-5.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </span>
                                {{ bullet }}
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- ── Bottom CTA strip ─────────────────────────────────────────── -->
            <div
                class="mt-16 flex flex-col items-center justify-between gap-6 rounded-2xl border border-gray-100 bg-gray-50 px-8 py-8 sm:flex-row"
                data-feat-animate
            >
                <div>
                    <p class="font-semibold text-gray-900">Ready to see it in action?</p>
                    <p class="mt-1 text-sm text-gray-500">
                        Every feature ships with factories, tests, and documentation included.
                    </p>
                </div>
                <div class="flex shrink-0 flex-wrap items-center gap-3">
                    <a
                        href="#screenshots"
                        class="rounded-xl border border-gray-200 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50"
                        @click.prevent="document.querySelector('#screenshots')?.scrollIntoView({ behavior: 'smooth' })"
                    >
                        Explore Screenshots
                    </a>
                    <a
                        href="#pricing"
                        class="rounded-xl bg-primary-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-700"
                        @click.prevent="document.querySelector('#pricing')?.scrollIntoView({ behavior: 'smooth' })"
                    >
                        View Pricing
                    </a>
                </div>
            </div>

        </div>
    </section>
</template>

<style scoped>
[data-feat-animate] {
    opacity: 0;
    transform: translateY(20px);
    transition: opacity 0.5s ease, transform 0.5s ease;
}
[data-feat-animate].animate-in {
    opacity: 1;
    transform: translateY(0);
}

/* Hover glow on card icon background */
.feat-card:hover .size-12 {
    box-shadow: 0 0 0 6px var(--glow, transparent);
}
</style>
