<script setup>
import { onMounted, ref } from 'vue';
import {
    ArrowTrendingUpIcon,
    BuildingOffice2Icon,
    ChartBarIcon,
    CreditCardIcon,
    UsersIcon,
} from '@heroicons/vue/24/outline';

// ── Screen definitions ─────────────────────────────────────────────────────────

const screens = [
    {
        id: 'admin',
        label: 'Admin Dashboard',
        url: 'admin.yoursaas.com/dashboard',
        icon: BuildingOffice2Icon,
        accent: 'indigo',
        badge: 'Platform',
        tagline: 'Full platform visibility at a glance',
    },
    {
        id: 'tenant',
        label: 'Tenant Dashboard',
        url: 'app.yoursaas.com/dashboard',
        icon: BuildingOffice2Icon,
        accent: 'violet',
        badge: 'Workspace',
        tagline: 'Tenant-scoped overview and quick actions',
    },
    {
        id: 'users',
        label: 'User Management',
        url: 'app.yoursaas.com/users',
        icon: UsersIcon,
        accent: 'sky',
        badge: 'Access',
        tagline: 'Invite, manage, and audit team members',
    },
    {
        id: 'billing',
        label: 'Billing & Subscription',
        url: 'app.yoursaas.com/billing',
        icon: CreditCardIcon,
        accent: 'emerald',
        badge: 'Billing',
        tagline: 'Plans, invoices, and payment methods',
    },
    {
        id: 'analytics',
        label: 'Analytics Reports',
        url: 'app.yoursaas.com/analytics',
        icon: ChartBarIcon,
        accent: 'amber',
        badge: 'Insights',
        tagline: 'Usage trends, MRR, and growth metrics',
    },
];

const accentMap = {
    indigo: {
        badge: 'bg-primary-50 text-primary-700 ring-1 ring-primary-200',
        bar: 'bg-primary-500',
        glow: 'from-primary-500/10 via-transparent',
        dot: 'bg-primary-500',
        ring: 'group-hover:ring-primary-300',
        stat: 'text-primary-400',
        pill: 'bg-primary-900/50 text-primary-300',
    },
    violet: {
        badge: 'bg-accent-50 text-accent-700 ring-1 ring-accent-200',
        bar: 'bg-accent-500',
        glow: 'from-accent-500/10 via-transparent',
        dot: 'bg-accent-500',
        ring: 'group-hover:ring-accent-300',
        stat: 'text-accent-400',
        pill: 'bg-accent-900/50 text-accent-300',
    },
    sky: {
        badge: 'bg-secondary-50 text-secondary-700 ring-1 ring-secondary-200',
        bar: 'bg-secondary-500',
        glow: 'from-secondary-500/10 via-transparent',
        dot: 'bg-secondary-500',
        ring: 'group-hover:ring-secondary-300',
        stat: 'text-secondary-400',
        pill: 'bg-secondary-900/50 text-secondary-300',
    },
    emerald: {
        badge: 'bg-success-50 text-success-700 ring-1 ring-success-200',
        bar: 'bg-success-500',
        glow: 'from-success-500/10 via-transparent',
        dot: 'bg-success-500',
        ring: 'group-hover:ring-success-300',
        stat: 'text-success-400',
        pill: 'bg-success-900/50 text-success-300',
    },
    amber: {
        badge: 'bg-warning-50 text-warning-700 ring-1 ring-warning-200',
        bar: 'bg-warning-500',
        glow: 'from-warning-500/10 via-transparent',
        dot: 'bg-warning-500',
        ring: 'group-hover:ring-warning-300',
        stat: 'text-warning-400',
        pill: 'bg-warning-900/50 text-warning-300',
    },
};

// ── Scroll-triggered entrance ──────────────────────────────────────────────────

onMounted(() => {
    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.12 },
    );

    document.querySelectorAll('[data-screen-animate]').forEach((el) => observer.observe(el));
});
</script>

<template>
    <section id="screenshots" class="bg-gray-950 py-24 sm:py-32">
        <div class="mx-auto max-w-7xl px-6">

            <!-- Heading -->
            <div class="mx-auto mb-16 max-w-2xl text-center" data-screen-animate>
                <p class="mb-3 text-xs font-semibold uppercase tracking-widest text-primary-400">Product Preview</p>
                <h2 class="text-3xl font-extrabold tracking-tight text-white sm:text-4xl">
                    Every screen designed for clarity
                </h2>
                <p class="mt-4 text-lg leading-relaxed text-gray-400">
                    From platform-wide admin views to tenant-scoped dashboards — purpose-built UI for every role.
                </p>
            </div>

            <!-- Featured card (Admin Dashboard) — full width -->
            <div
                data-screen-animate
                style="transition-delay: 60ms"
                class="screen-card group mb-6 overflow-hidden rounded-2xl ring-1 ring-gray-700/60 transition-all duration-300 hover:-translate-y-1 hover:ring-2"
                :class="accentMap[screens[0].accent].ring"
            >
                <!-- Browser chrome -->
                <div class="flex h-9 items-center gap-2 border-b border-gray-700 bg-gray-800/90 px-4">
                    <span class="size-2.5 rounded-full bg-red-500/60" />
                    <span class="size-2.5 rounded-full bg-warning-500/60" />
                    <span class="size-2.5 rounded-full bg-success-500/60" />
                    <div class="mx-3 flex-1 rounded bg-gray-900 px-3 py-1 text-xs text-gray-500">
                        {{ screens[0].url }}
                    </div>
                    <span class="rounded px-2 py-0.5 text-xs font-medium" :class="accentMap[screens[0].accent].pill">
                        {{ screens[0].badge }}
                    </span>
                </div>

                <!-- Screen body -->
                <div class="relative bg-gray-900">
                    <!-- Accent glow -->
                    <div class="pointer-events-none absolute inset-0 bg-gradient-to-br opacity-60" :class="accentMap[screens[0].accent].glow" />

                    <div class="relative px-6 py-5">
                        <!-- Top bar -->
                        <div class="mb-5 flex items-center justify-between">
                            <div>
                                <p class="text-xs text-gray-500">Platform Overview</p>
                                <h3 class="text-sm font-bold text-white">Admin Dashboard</h3>
                            </div>
                            <span class="rounded-full bg-primary-900/60 px-3 py-1 text-xs text-primary-300">Last 30 days</span>
                        </div>

                        <!-- KPI row -->
                        <div class="mb-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
                            <div
                                v-for="stat in [
                                    { label: 'Total MRR', value: '$48.2K', change: '+12%' },
                                    { label: 'Active Tenants', value: '384', change: '+28' },
                                    { label: 'Active Users', value: '4,201', change: '+156' },
                                    { label: 'Churn Rate', value: '1.8%', change: '-0.2%' },
                                ]"
                                :key="stat.label"
                                class="rounded-xl bg-gray-800/80 p-3"
                            >
                                <p class="text-xs text-gray-500">{{ stat.label }}</p>
                                <p class="mt-1 text-base font-bold text-white">{{ stat.value }}</p>
                                <p class="mt-0.5 text-xs text-success-400">{{ stat.change }}</p>
                            </div>
                        </div>

                        <!-- Bottom row: chart + recent tenants -->
                        <div class="grid gap-4 sm:grid-cols-3">
                            <!-- Revenue chart -->
                            <div class="rounded-xl bg-gray-800/80 p-4 sm:col-span-2">
                                <p class="mb-3 text-xs font-semibold text-gray-400">Revenue Trend (12 mo)</p>
                                <div class="flex h-24 items-end gap-1.5">
                                    <div
                                        v-for="(h, idx) in [28, 35, 30, 48, 42, 56, 50, 68, 62, 80, 75, 100]"
                                        :key="idx"
                                        class="flex-1 rounded-t transition-all duration-300 group-hover:opacity-100"
                                        :class="idx >= 10 ? 'bg-primary-500' : 'bg-gray-700'"
                                        :style="{ height: h + '%' }"
                                    />
                                </div>
                            </div>
                            <!-- Recent tenants -->
                            <div class="rounded-xl bg-gray-800/80 p-4">
                                <p class="mb-3 text-xs font-semibold text-gray-400">New Tenants</p>
                                <div class="space-y-2">
                                    <div
                                        v-for="t in ['Acme Corp', 'Globex Inc', 'Initech', 'Umbrella']"
                                        :key="t"
                                        class="flex items-center gap-2"
                                    >
                                        <span class="size-1.5 shrink-0 rounded-full" :class="accentMap[screens[0].accent].dot" />
                                        <span class="truncate text-xs text-gray-300">{{ t }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card footer -->
                <div class="flex items-center justify-between border-t border-gray-700/60 bg-gray-800/60 px-5 py-3">
                    <div class="flex items-center gap-2">
                        <component :is="screens[0].icon" class="size-4 text-gray-400" />
                        <span class="text-xs font-medium text-white">{{ screens[0].label }}</span>
                    </div>
                    <p class="text-xs text-gray-500">{{ screens[0].tagline }}</p>
                </div>
            </div>

            <!-- 2×2 grid (remaining 4 screens) -->
            <div class="grid gap-6 sm:grid-cols-2">
                <div
                    v-for="(screen, idx) in screens.slice(1)"
                    :key="screen.id"
                    data-screen-animate
                    :style="{ transitionDelay: (idx % 2 + 1) * 100 + 'ms' }"
                    class="screen-card group overflow-hidden rounded-2xl ring-1 ring-gray-700/60 transition-all duration-300 hover:-translate-y-1 hover:ring-2"
                    :class="accentMap[screen.accent].ring"
                >
                    <!-- Browser chrome -->
                    <div class="flex h-9 items-center gap-2 border-b border-gray-700 bg-gray-800/90 px-4">
                        <span class="size-2.5 rounded-full bg-red-500/60" />
                        <span class="size-2.5 rounded-full bg-warning-500/60" />
                        <span class="size-2.5 rounded-full bg-success-500/60" />
                        <div class="mx-3 flex-1 truncate rounded bg-gray-900 px-3 py-1 text-xs text-gray-500">
                            {{ screen.url }}
                        </div>
                        <span class="shrink-0 rounded px-2 py-0.5 text-xs font-medium" :class="accentMap[screen.accent].pill">
                            {{ screen.badge }}
                        </span>
                    </div>

                    <!-- Screen body -->
                    <div class="relative bg-gray-900">
                        <div class="pointer-events-none absolute inset-0 bg-gradient-to-br opacity-60" :class="accentMap[screen.accent].glow" />

                        <!-- ── Tenant Dashboard ── -->
                        <div v-if="screen.id === 'tenant'" class="relative px-5 py-4">
                            <p class="mb-1 text-xs text-gray-500">Welcome back, Acme Corp</p>
                            <h3 class="mb-4 text-sm font-bold text-white">Workspace Overview</h3>
                            <div class="mb-4 grid grid-cols-3 gap-3">
                                <div
                                    v-for="s in [{ l: 'Members', v: '12' }, { l: 'Storage', v: '2.3 GB' }, { l: 'API Calls', v: '84K' }]"
                                    :key="s.l"
                                    class="rounded-xl bg-gray-800/80 p-3"
                                >
                                    <p class="text-xs text-gray-500">{{ s.l }}</p>
                                    <p class="mt-1 text-sm font-bold text-white">{{ s.v }}</p>
                                </div>
                            </div>
                            <div class="rounded-xl bg-gray-800/80 p-4">
                                <p class="mb-2 text-xs font-semibold text-gray-400">Recent Activity</p>
                                <div class="space-y-2">
                                    <div
                                        v-for="a in ['User invited: j.smith@acme.com', 'Plan upgraded to Pro', 'API token generated', 'Report exported']"
                                        :key="a"
                                        class="flex items-center gap-2 text-xs text-gray-300"
                                    >
                                        <span class="size-1.5 shrink-0 rounded-full" :class="accentMap[screen.accent].dot" />
                                        {{ a }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ── User Management ── -->
                        <div v-else-if="screen.id === 'users'" class="relative px-5 py-4">
                            <div class="mb-4 flex items-center justify-between">
                                <h3 class="text-sm font-bold text-white">Team Members</h3>
                                <span class="rounded-full bg-secondary-900/60 px-2.5 py-0.5 text-xs text-secondary-300">12 members</span>
                            </div>
                            <!-- Search bar mock -->
                            <div class="mb-4 flex items-center gap-2 rounded-lg bg-gray-800/80 px-3 py-2">
                                <span class="size-3.5 rounded-full border border-gray-500" />
                                <span class="text-xs text-gray-500">Search users...</span>
                            </div>
                            <!-- User rows -->
                            <div class="divide-y divide-gray-700/40 rounded-xl bg-gray-800/80 overflow-hidden">
                                <div
                                    v-for="user in [
                                        { name: 'Alice Novak', role: 'Admin', status: 'active' },
                                        { name: 'Ben Harlow', role: 'Member', status: 'active' },
                                        { name: 'Clara Sun', role: 'Viewer', status: 'pending' },
                                        { name: 'Dan Walsh', role: 'Member', status: 'active' },
                                    ]"
                                    :key="user.name"
                                    class="flex items-center justify-between px-4 py-2.5"
                                >
                                    <div class="flex items-center gap-2.5">
                                        <div class="flex size-7 shrink-0 items-center justify-center rounded-full bg-secondary-700 text-xs font-bold text-white">
                                            {{ user.name[0] }}
                                        </div>
                                        <span class="text-xs font-medium text-gray-200">{{ user.name }}</span>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <span class="text-xs text-gray-500">{{ user.role }}</span>
                                        <span
                                            class="rounded-full px-2 py-0.5 text-xs"
                                            :class="user.status === 'active' ? 'bg-success-900/60 text-success-300' : 'bg-warning-900/40 text-warning-400'"
                                        >{{ user.status }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ── Billing & Subscription ── -->
                        <div v-else-if="screen.id === 'billing'" class="relative px-5 py-4">
                            <h3 class="mb-4 text-sm font-bold text-white">Billing Overview</h3>
                            <!-- Current plan -->
                            <div class="mb-3 flex items-center justify-between rounded-xl bg-success-900/30 px-4 py-3 ring-1 ring-success-700/40">
                                <div>
                                    <p class="text-xs text-gray-400">Current Plan</p>
                                    <p class="mt-0.5 font-semibold text-white">Pro — $49<span class="text-xs font-normal text-gray-400">/mo</span></p>
                                </div>
                                <span class="rounded-full bg-success-600 px-3 py-0.5 text-xs text-white">Active</span>
                            </div>
                            <!-- Usage bar -->
                            <div class="mb-3 rounded-xl bg-gray-800/80 px-4 py-3">
                                <div class="mb-1.5 flex justify-between text-xs">
                                    <span class="text-gray-400">API calls used</span>
                                    <span class="text-gray-200">84,120 / 100,000</span>
                                </div>
                                <div class="h-2 overflow-hidden rounded-full bg-gray-700">
                                    <div class="h-full rounded-full bg-success-500" style="width: 84%" />
                                </div>
                            </div>
                            <!-- Recent invoices -->
                            <div class="space-y-1.5 rounded-xl bg-gray-800/80 px-4 py-3">
                                <p class="mb-2 text-xs font-semibold text-gray-400">Recent Invoices</p>
                                <div
                                    v-for="inv in [
                                        { date: 'Feb 2026', amount: '$49.00', status: 'Paid' },
                                        { date: 'Jan 2026', amount: '$49.00', status: 'Paid' },
                                        { date: 'Dec 2025', amount: '$49.00', status: 'Paid' },
                                    ]"
                                    :key="inv.date"
                                    class="flex items-center justify-between text-xs"
                                >
                                    <span class="text-gray-400">{{ inv.date }}</span>
                                    <span class="text-gray-200">{{ inv.amount }}</span>
                                    <span class="text-success-400">{{ inv.status }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- ── Analytics Reports ── -->
                        <div v-else-if="screen.id === 'analytics'" class="relative px-5 py-4">
                            <div class="mb-4 flex items-center justify-between">
                                <h3 class="text-sm font-bold text-white">Analytics Overview</h3>
                                <span class="rounded-full bg-warning-900/60 px-2.5 py-0.5 text-xs text-warning-300">Last 30 days</span>
                            </div>
                            <!-- Mini stat row -->
                            <div class="mb-4 grid grid-cols-3 gap-2">
                                <div
                                    v-for="s in [{ l: 'Page Views', v: '28K' }, { l: 'Conversion', v: '3.4%' }, { l: 'Avg. Session', v: '4m 12s' }]"
                                    :key="s.l"
                                    class="rounded-lg bg-gray-800/80 p-2.5"
                                >
                                    <p class="text-xs text-gray-500">{{ s.l }}</p>
                                    <p class="mt-0.5 text-sm font-bold" :class="accentMap[screen.accent].stat">{{ s.v }}</p>
                                </div>
                            </div>
                            <!-- Donut mock + bar chart side by side -->
                            <div class="grid grid-cols-2 gap-3">
                                <!-- Horizontal bars -->
                                <div class="rounded-xl bg-gray-800/80 p-3">
                                    <p class="mb-2 text-xs font-medium text-gray-400">Traffic Sources</p>
                                    <div class="space-y-2">
                                        <div
                                            v-for="src in [
                                                { label: 'Organic', pct: 52 },
                                                { label: 'Direct', pct: 28 },
                                                { label: 'Referral', pct: 14 },
                                                { label: 'Social', pct: 6 },
                                            ]"
                                            :key="src.label"
                                        >
                                            <div class="mb-0.5 flex justify-between text-xs">
                                                <span class="text-gray-400">{{ src.label }}</span>
                                                <span class="text-gray-300">{{ src.pct }}%</span>
                                            </div>
                                            <div class="h-1.5 overflow-hidden rounded-full bg-gray-700">
                                                <div class="h-full rounded-full bg-warning-500" :style="{ width: src.pct + '%' }" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Vertical bars -->
                                <div class="rounded-xl bg-gray-800/80 p-3">
                                    <p class="mb-2 text-xs font-medium text-gray-400">Weekly Trend</p>
                                    <div class="flex h-16 items-end gap-1">
                                        <div
                                            v-for="(h, idx) in [40, 62, 48, 80, 70, 90, 100]"
                                            :key="idx"
                                            class="flex-1 rounded-t"
                                            :class="idx === 6 ? 'bg-warning-500' : 'bg-gray-600'"
                                            :style="{ height: h + '%' }"
                                        />
                                    </div>
                                    <div class="mt-1.5 flex justify-between">
                                        <span
                                            v-for="d in ['M', 'T', 'W', 'T', 'F', 'S', 'S']"
                                            :key="d"
                                            class="flex-1 text-center text-xs text-gray-600"
                                        >{{ d }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card footer -->
                    <div class="flex items-center justify-between border-t border-gray-700/60 bg-gray-800/60 px-5 py-3">
                        <div class="flex items-center gap-2">
                            <component :is="screen.icon" class="size-4 text-gray-400" />
                            <span class="text-xs font-medium text-white">{{ screen.label }}</span>
                        </div>
                        <p class="hidden text-xs text-gray-500 sm:block">{{ screen.tagline }}</p>
                    </div>
                </div>
            </div>

            <!-- Bottom CTA -->
            <div class="mt-14 text-center" data-screen-animate style="transition-delay: 200ms">
                <p class="mb-4 text-sm text-gray-400">Every view is live-connected to your tenant data — no mocks in production.</p>
                <a
                    href="#pricing"
                    class="inline-flex items-center gap-2 rounded-full bg-white px-6 py-2.5 text-sm font-semibold text-gray-900 shadow-sm transition-all hover:bg-gray-100 hover:shadow-md"
                >
                    <ArrowTrendingUpIcon class="size-4" />
                    See Pricing
                </a>
            </div>

        </div>
    </section>
</template>

<style scoped>
[data-screen-animate] {
    opacity: 0;
    transform: translateY(24px);
    transition:
        opacity 0.5s ease,
        transform 0.5s ease;
}

[data-screen-animate].visible {
    opacity: 1;
    transform: translateY(0);
}

.screen-card {
    transition:
        transform 0.25s ease,
        box-shadow 0.25s ease,
        ring 0.25s ease;
}

.screen-card:hover {
    box-shadow: 0 20px 48px -8px rgb(0 0 0 / 0.5);
}
</style>
