<script setup>
import { ref, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import {
    ArrowRightIcon,
    SparklesIcon,
    PlayIcon,
    ArrowTrendingUpIcon,
    UsersIcon,
    CreditCardIcon,
    BoltIcon,
    CheckCircleIcon,
} from '@heroicons/vue/24/outline';
import { CheckCircleIcon as CheckCircleSolid } from '@heroicons/vue/20/solid';

// ── Animated counters ─────────────────────────────────────────────────────────

const mrrDisplay    = ref('$0');
const tenantsDisplay = ref('0');
const usersDisplay  = ref('0');
const uptimeDisplay = ref('0%');

function animateCounter(target, duration, formatter, setter) {
    const start = performance.now();
    function step(now) {
        const elapsed  = now - start;
        const progress = Math.min(elapsed / duration, 1);
        const eased    = 1 - Math.pow(1 - progress, 3);
        setter(formatter(Math.floor(eased * target)));
        if (progress < 1) { requestAnimationFrame(step); }
    }
    requestAnimationFrame(step);
}

// ── Animated chart bars ───────────────────────────────────────────────────────

const barHeights = ref([0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]);
const rawHeights = [28, 42, 35, 58, 48, 66, 55, 74, 63, 82, 71, 100];

function animateBars() {
    rawHeights.forEach((h, i) => {
        setTimeout(() => {
            barHeights.value[i] = h;
        }, i * 60);
    });
}

// ── Live activity feed ────────────────────────────────────────────────────────

const activityItems = [
    { icon: UsersIcon,   color: 'bg-primary-100 text-primary-600',  text: 'Acme Corp invited 3 members',       time: '2s ago' },
    { icon: CreditCardIcon, color: 'bg-success-100 text-success-600', text: 'Globex upgraded to Pro plan',   time: '1m ago' },
    { icon: BoltIcon,    color: 'bg-accent-100 text-accent-600',   text: 'API — 12K requests this hour',      time: '3m ago' },
    { icon: CheckCircleIcon, color: 'bg-secondary-100 text-secondary-600',    text: 'Soylent Inc. provisioned',          time: '5m ago' },
];

const visibleActivity = ref(0);

// ── Lifecycle ─────────────────────────────────────────────────────────────────

onMounted(() => {
    // Stagger the element entrance
    setTimeout(() => {
        animateCounter(48200, 1800, (v) => `$${(v / 1000).toFixed(1)}K`, (v) => { mrrDisplay.value = v; });
        animateCounter(384,   1600, (v) => String(v),                     (v) => { tenantsDisplay.value = v; });
        animateCounter(4201,  1900, (v) => v.toLocaleString(),            (v) => { usersDisplay.value = v; });
        animateCounter(9998,  2000, (v) => `${(v / 100).toFixed(2)}%`,   (v) => { uptimeDisplay.value = v; });
        animateBars();
        // Feed items appear one by one
        activityItems.forEach((_, i) => {
            setTimeout(() => { visibleActivity.value = i + 1; }, 600 + i * 500);
        });
    }, 400);
});
</script>

<template>
    <section class="hero-section relative overflow-hidden bg-gray-950 pb-20 pt-28 sm:pb-28 sm:pt-36">

        <!-- ── Animated mesh gradient background ───────────────────────────── -->
        <div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
            <!-- Primary orb -->
            <div class="orb-1 absolute -left-32 -top-32 size-[640px] rounded-full bg-primary-600/20 blur-3xl" />
            <!-- Secondary orb -->
            <div class="orb-2 absolute -right-48 top-16 size-[540px] rounded-full bg-accent-600/15 blur-3xl" />
            <!-- Tertiary orb -->
            <div class="orb-3 absolute -bottom-40 left-1/3 size-[480px] rounded-full bg-secondary-600/10 blur-3xl" />
            <!-- Grid overlay -->
            <div class="hero-grid absolute inset-0 opacity-[0.04]"
                style="background-image: linear-gradient(rgba(255,255,255,0.6) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.6) 1px, transparent 1px); background-size: 48px 48px;"
            />
            <!-- Radial vignette -->
            <div class="absolute inset-0 bg-radial-to-b from-transparent via-transparent to-gray-950/80" />
        </div>

        <div class="relative mx-auto max-w-7xl px-6">
            <div class="grid items-center gap-14 lg:grid-cols-[1fr_1.15fr] lg:gap-10">

                <!-- ── Left: Copy ─────────────────────────────────────────── -->
                <div class="hero-copy text-center lg:text-left">

                    <!-- Eyebrow badge -->
                    <div class="hero-badge mb-6 inline-flex items-center gap-2 rounded-full border border-primary-500/30 bg-primary-500/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-primary-300">
                        <SparklesIcon class="size-3.5 animate-pulse" />
                        Multi-Tenant SaaS Platform
                    </div>

                    <!-- Headline -->
                    <h1 class="hero-headline text-4xl font-extrabold leading-[1.08] tracking-tight text-white sm:text-5xl lg:text-[3.4rem] xl:text-6xl">
                        Build and Manage
                        <span class="relative block">
                            <span class="text-gradient-hero">
                                Multi-Tenant SaaS
                            </span>
                        </span>
                        Applications
                        <span class="text-gradient-primary">
                            Effortlessly
                        </span>
                    </h1>

                    <!-- Subheading -->
                    <p class="hero-sub mt-6 max-w-xl text-lg leading-relaxed text-gray-400 lg:max-w-none">
                        Fully isolated tenant workspaces, role-based permissions, subscription billing, and real-time analytics — all production-ready on a battle-tested Laravel foundation.
                    </p>

                    <!-- CTA buttons -->
                    <div class="hero-cta mt-9 flex flex-wrap items-center justify-center gap-4 lg:justify-start">
                        <Link
                            :href="route('register')"
                            class="group inline-flex items-center gap-2.5 rounded-xl bg-primary-600 px-7 py-3.5 text-sm font-bold text-white shadow-lg shadow-primary-900/40 transition-all duration-200 hover:bg-primary-500 hover:shadow-primary-600/50 hover:shadow-xl active:scale-[0.97]"
                        >
                            Start Building Free
                            <ArrowRightIcon class="size-4 transition-transform duration-200 group-hover:translate-x-0.5" />
                        </Link>
                        <Link
                            :href="route('demo.admin')"
                            class="group inline-flex items-center gap-2.5 rounded-xl border border-gray-600 bg-gray-800/60 px-7 py-3.5 text-sm font-bold text-gray-200 backdrop-blur transition-all duration-200 hover:border-gray-400 hover:bg-gray-700/80 hover:text-white active:scale-[0.97]"
                        >
                            <PlayIcon class="size-4 fill-current opacity-70 transition-opacity group-hover:opacity-100" />
                            Watch Demo
                        </Link>
                    </div>

                    <!-- Social proof row -->
                    <div class="hero-proof mt-8 flex flex-col items-center gap-4 sm:flex-row lg:items-start lg:justify-start">
                        <!-- Avatar stack -->
                        <div class="flex -space-x-2.5">
                            <div
                                v-for="(color, i) in ['bg-indigo-500', 'bg-violet-500', 'bg-sky-500', 'bg-emerald-500', 'bg-amber-500']"
                                :key="i"
                                class="flex size-8 items-center justify-center rounded-full border-2 border-gray-950 text-xs font-bold text-white"
                                :class="color"
                            >
                                {{ ['A', 'J', 'M', 'S', 'R'][i] }}
                            </div>
                        </div>
                        <div class="text-center sm:text-left">
                            <div class="flex items-center justify-center gap-0.5 sm:justify-start">
                                <span v-for="s in 5" :key="s" class="text-warning-400 text-sm">★</span>
                            </div>
                            <p class="text-xs text-gray-400">
                                <span class="font-semibold text-gray-200">500+ teams</span> trust this platform
                            </p>
                        </div>
                        <div class="hidden h-8 w-px bg-gray-700 sm:block" />
                        <p class="text-xs text-gray-500">No credit card required</p>
                    </div>

                    <!-- Tech stack pills -->
                    <div class="hero-badges mt-8 flex flex-wrap items-center justify-center gap-2 lg:justify-start">
                        <span
                            v-for="badge in ['Laravel 12', 'Vue 3', 'Inertia.js', 'TailwindCSS v4', 'Pest v4']"
                            :key="badge"
                            class="rounded-full border border-gray-700 bg-gray-800/50 px-3 py-1 text-xs font-medium text-gray-400"
                        >
                            {{ badge }}
                        </span>
                    </div>
                </div>

                <!-- ── Right: Dashboard mockup ─────────────────────────────── -->
                <div class="hero-mockup relative mx-auto w-full lg:mx-0">

                    <!-- Glow behind the card -->
                    <div class="absolute inset-x-4 top-6 h-full rounded-3xl bg-primary-600/20 blur-2xl" />

                    <!-- Browser chrome shell -->
                    <div class="relative overflow-hidden rounded-2xl border border-gray-700/80 bg-gray-900 shadow-[0_24px_80px_rgba(0,0,0,0.6)] ring-1 ring-white/5">

                        <!-- Chrome bar -->
                        <div class="flex h-10 items-center gap-2 border-b border-gray-700/60 bg-gray-800/80 px-4 backdrop-blur">
                            <span class="size-3 rounded-full bg-red-500/70" />
                            <span class="size-3 rounded-full bg-warning-500/70" />
                            <span class="size-3 rounded-full bg-success-500/70" />
                            <div class="mx-3 flex-1 rounded-md border border-gray-600/50 bg-gray-700/50 px-3 py-1 text-xs text-gray-400">
                                app.yoursaas.com/dashboard
                            </div>
                        </div>

                        <!-- Dashboard layout -->
                        <div class="flex" style="height: 380px;">

                            <!-- Sidebar -->
                            <div class="flex w-12 shrink-0 flex-col items-center gap-3 border-r border-gray-700/50 bg-gray-900/80 py-4 sm:w-44 sm:items-start sm:px-3">
                                <!-- Workspace selector -->
                                <div class="hidden w-full items-center gap-2 rounded-lg bg-gray-700/50 px-2 py-1.5 sm:flex">
                                    <div class="flex size-5 items-center justify-center rounded bg-primary-600 text-xs font-bold text-white">A</div>
                                    <span class="flex-1 truncate text-xs font-semibold text-white">Acme Corp</span>
                                </div>
                                <!-- Nav items -->
                                <nav class="mt-1 w-full space-y-0.5">
                                    <div
                                        v-for="(item, i) in [
                                            { label: 'Dashboard', dot: 'bg-primary-500' },
                                            { label: 'Analytics', dot: 'bg-gray-600' },
                                            { label: 'Users', dot: 'bg-gray-600' },
                                            { label: 'Billing', dot: 'bg-gray-600' },
                                            { label: 'Settings', dot: 'bg-gray-600' },
                                        ]"
                                        :key="item.label"
                                        class="flex h-7 items-center gap-2 rounded-lg px-2 text-xs"
                                        :class="i === 0
                                            ? 'bg-primary-600/80 text-white font-semibold'
                                            : 'text-gray-500 hover:text-gray-300'"
                                    >
                                        <span class="size-1.5 shrink-0 rounded-full" :class="item.dot" />
                                        <span class="hidden sm:block">{{ item.label }}</span>
                                    </div>
                                </nav>
                                <!-- Tenant list -->
                                <div class="mt-auto hidden w-full sm:block">
                                    <p class="mb-1.5 px-2 text-[10px] font-semibold uppercase tracking-widest text-gray-600">Tenants</p>
                                    <div
                                        v-for="t in ['Globex', 'Initech', 'Umbrella']"
                                        :key="t"
                                        class="flex h-6 items-center gap-2 rounded px-2 text-[11px] text-gray-500"
                                    >
                                        <div class="size-3.5 rounded-sm bg-gray-700" />
                                        {{ t }}
                                    </div>
                                </div>
                            </div>

                            <!-- Main panel -->
                            <div class="flex-1 overflow-hidden bg-gray-950/50 p-3.5">

                                <!-- Top row: KPI cards -->
                                <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                                    <div
                                        v-for="kpi in [
                                            { label: 'MRR', value: mrrDisplay, change: '+8.3%', up: true, accent: 'border-t-primary-500' },
                                            { label: 'Tenants', value: tenantsDisplay, change: '+28', up: true, accent: 'border-t-accent-500' },
                                            { label: 'Active Users', value: usersDisplay, change: '+156', up: true, accent: 'border-t-secondary-500' },
                                            { label: 'Uptime', value: uptimeDisplay, change: 'SLA met', up: true, accent: 'border-t-success-500' },
                                        ]"
                                        :key="kpi.label"
                                        class="rounded-xl border border-gray-700/60 bg-gray-800/60 p-2.5 backdrop-blur"
                                        :class="'border-t-2 ' + kpi.accent"
                                    >
                                        <p class="text-[10px] font-medium text-gray-500">{{ kpi.label }}</p>
                                        <p class="mt-0.5 text-sm font-bold tabular-nums text-white sm:text-base">{{ kpi.value }}</p>
                                        <p class="mt-0.5 text-[10px] font-semibold text-success-400">
                                            ↑ {{ kpi.change }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Middle row: chart + activity feed -->
                                <div class="mt-2.5 grid gap-2.5 sm:grid-cols-[1.6fr_1fr]">

                                    <!-- Revenue chart -->
                                    <div class="rounded-xl border border-gray-700/60 bg-gray-800/50 p-3">
                                        <div class="mb-2.5 flex items-center justify-between">
                                            <div>
                                                <p class="text-xs font-semibold text-white">Revenue Overview</p>
                                                <p class="text-[10px] text-gray-500">Monthly recurring revenue</p>
                                            </div>
                                            <div class="flex gap-1">
                                                <span
                                                    v-for="r in ['1M', '3M', '1Y']"
                                                    :key="r"
                                                    class="cursor-pointer rounded-md px-2 py-0.5 text-[10px] font-medium"
                                                    :class="r === '1Y' ? 'bg-primary-600 text-white' : 'text-gray-500 hover:text-gray-300'"
                                                >
                                                    {{ r }}
                                                </span>
                                            </div>
                                        </div>
                                        <!-- Bars -->
                                        <div class="flex h-24 items-end gap-1">
                                            <div
                                                v-for="(h, i) in barHeights"
                                                :key="i"
                                                class="group relative flex-1 cursor-pointer rounded-t transition-all duration-500 ease-out"
                                                :class="i === rawHeights.length - 1
                                                    ? 'bg-primary-500 hover:bg-primary-400'
                                                    : i >= rawHeights.length - 3
                                                        ? 'bg-primary-700/80 hover:bg-primary-600'
                                                        : 'bg-gray-700 hover:bg-gray-600'"
                                                :style="{ height: h + '%' }"
                                            >
                                                <!-- Tooltip -->
                                                <div class="pointer-events-none absolute -top-6 left-1/2 -translate-x-1/2 whitespace-nowrap rounded bg-gray-700 px-1.5 py-0.5 text-[9px] text-white opacity-0 group-hover:opacity-100">
                                                    ${{ Math.round(h * 482) }}
                                                </div>
                                            </div>
                                        </div>
                                        <!-- X labels -->
                                        <div class="mt-1 flex justify-between px-0.5">
                                            <span v-for="m in ['Jan', 'Apr', 'Jul', 'Dec']" :key="m" class="text-[9px] text-gray-600">{{ m }}</span>
                                        </div>
                                    </div>

                                    <!-- Live activity feed -->
                                    <div class="rounded-xl border border-gray-700/60 bg-gray-800/50 p-3">
                                        <div class="mb-2.5 flex items-center justify-between">
                                            <p class="text-xs font-semibold text-white">Live Activity</p>
                                            <span class="flex items-center gap-1 text-[10px] text-success-400">
                                                <span class="size-1.5 animate-pulse rounded-full bg-success-400" />
                                                Live
                                            </span>
                                        </div>
                                        <TransitionGroup
                                            name="feed"
                                            tag="div"
                                            class="space-y-2"
                                        >
                                            <div
                                                v-for="(item, i) in activityItems.slice(0, visibleActivity)"
                                                :key="item.text"
                                                class="flex items-start gap-2"
                                            >
                                                <div
                                                    class="mt-0.5 flex size-5 shrink-0 items-center justify-center rounded-md"
                                                    :class="item.color"
                                                >
                                                    <component :is="item.icon" class="size-3" />
                                                </div>
                                                <div class="min-w-0">
                                                    <p class="truncate text-[10px] leading-tight text-gray-300">{{ item.text }}</p>
                                                    <p class="text-[9px] text-gray-600">{{ item.time }}</p>
                                                </div>
                                            </div>
                                        </TransitionGroup>
                                    </div>
                                </div>

                                <!-- Bottom row: mini tenant status pills -->
                                <div class="mt-2.5 flex flex-wrap items-center gap-2">
                                    <p class="text-[10px] font-semibold text-gray-600">Active tenants:</p>
                                    <span
                                        v-for="t in ['Acme Corp', 'Globex', 'Initech', 'Umbrella', 'Soylent']"
                                        :key="t"
                                        class="rounded-full border border-success-700/50 bg-success-900/30 px-2 py-0.5 text-[10px] font-medium text-success-400"
                                    >
                                        {{ t }}
                                    </span>
                                    <span class="rounded-full bg-gray-800 px-2 py-0.5 text-[10px] text-gray-500">+379 more</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Floating notification card — bottom-left -->
                    <div class="absolute -bottom-5 -left-5 hidden w-56 animate-[float_3s_ease-in-out_infinite] rounded-2xl border border-gray-700/60 bg-gray-800/95 p-3.5 shadow-2xl backdrop-blur ring-1 ring-white/5 sm:block">
                        <div class="flex items-center gap-3">
                            <div class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-success-500/15">
                                <CheckCircleSolid class="size-5 text-success-400" />
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-white">New tenant live</p>
                                <p class="text-xs text-gray-400">Soylent Inc. just provisioned</p>
                                <p class="mt-0.5 text-[10px] text-gray-600">2 seconds ago</p>
                            </div>
                        </div>
                    </div>

                    <!-- Floating MRR badge — top-right -->
                    <div class="absolute -right-5 -top-5 hidden rounded-2xl border border-gray-700/60 bg-gray-800/95 px-4 py-3 shadow-2xl backdrop-blur ring-1 ring-white/5 sm:block">
                        <div class="flex items-center gap-2.5">
                            <ArrowTrendingUpIcon class="size-5 text-success-400" />
                            <div>
                                <p class="text-[10px] font-medium text-gray-500">Monthly Revenue</p>
                                <p class="text-base font-extrabold tabular-nums text-white">{{ mrrDisplay }}</p>
                                <p class="text-[10px] font-semibold text-success-400">↑ 8.3% this month</p>
                            </div>
                        </div>
                    </div>

                    <!-- Floating plan upgrade badge — right edge -->
                    <div class="absolute -right-3 bottom-24 hidden rounded-xl border border-accent-700/40 bg-accent-900/50 px-3 py-2 shadow-xl backdrop-blur ring-1 ring-accent-500/20 sm:block">
                        <div class="flex items-center gap-2">
                            <BoltIcon class="size-4 text-accent-400" />
                            <span class="text-xs font-semibold text-accent-300">Globex → Pro Plan</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
/* ── Animated gradient orbs ──────────────────────────────────────────────── */
.orb-1 {
    animation: drift-1 12s ease-in-out infinite alternate;
}
.orb-2 {
    animation: drift-2 14s ease-in-out infinite alternate;
}
.orb-3 {
    animation: drift-3 10s ease-in-out infinite alternate;
}

@keyframes drift-1 {
    0%   { transform: translate(0, 0) scale(1); }
    100% { transform: translate(60px, 40px) scale(1.1); }
}
@keyframes drift-2 {
    0%   { transform: translate(0, 0) scale(1); }
    100% { transform: translate(-50px, 30px) scale(1.08); }
}
@keyframes drift-3 {
    0%   { transform: translate(0, 0) scale(1); }
    100% { transform: translate(40px, -30px) scale(1.12); }
}

/* ── Floating card animation ─────────────────────────────────────────────── */
@keyframes float {
    0%, 100% { transform: translateY(0); }
    50%       { transform: translateY(-8px); }
}

/* ── Hero entrance animations ────────────────────────────────────────────── */
.hero-badge {
    animation: fade-up 0.5s ease both;
}
.hero-headline {
    animation: fade-up 0.6s 0.1s ease both;
}
.hero-sub {
    animation: fade-up 0.6s 0.2s ease both;
}
.hero-cta {
    animation: fade-up 0.6s 0.3s ease both;
}
.hero-proof {
    animation: fade-up 0.6s 0.4s ease both;
}
.hero-badges {
    animation: fade-up 0.6s 0.5s ease both;
}
.hero-mockup {
    animation: fade-in-right 0.8s 0.3s ease both;
}

@keyframes fade-up {
    from { opacity: 0; transform: translateY(20px); }
    to   { opacity: 1; transform: translateY(0); }
}
@keyframes fade-in-right {
    from { opacity: 0; transform: translateX(24px); }
    to   { opacity: 1; transform: translateX(0); }
}

/* ── Activity feed transition ─────────────────────────────────────────────── */
.feed-enter-active {
    transition: all 0.4s ease;
}
.feed-enter-from {
    opacity: 0;
    transform: translateX(-8px);
}
.feed-enter-to {
    opacity: 1;
    transform: translateX(0);
}
</style>
