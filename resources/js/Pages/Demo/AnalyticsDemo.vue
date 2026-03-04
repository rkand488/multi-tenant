<script setup>
import { Head } from '@inertiajs/vue3';
import { Bar, Line } from 'vue-chartjs';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    BarElement,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    Filler,
} from 'chart.js';
import {
    HomeIcon,
    ChartBarIcon,
    UsersIcon,
    FolderOpenIcon,
    CreditCardIcon,
    Cog6ToothIcon,
    BellIcon,
    ArrowTrendingUpIcon,
    ArrowTrendingDownIcon,
    CalendarDaysIcon,
    CursorArrowRaysIcon,
    EyeIcon,
    ArrowPathIcon,
} from '@heroicons/vue/24/outline';

ChartJS.register(
    CategoryScale,
    LinearScale,
    BarElement,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    Filler,
);

// ── KPI cards ─────────────────────────────────────────────────────────────────
const kpis = [
    { label: 'Page Views',       value: '483,214', change: '+21.4%', up: true,  icon: EyeIcon,              light: 'bg-indigo-50 text-indigo-600'  },
    { label: 'Unique Visitors',  value: '128,931', change: '+14.7%', up: true,  icon: UsersIcon,            light: 'bg-emerald-50 text-emerald-600' },
    { label: 'Conversion Rate',  value: '3.84%',   change: '+0.32%', up: true,  icon: CursorArrowRaysIcon,  light: 'bg-violet-50 text-violet-600'   },
    { label: 'Avg. Session',     value: '4m 22s',  change: '-12s',   up: false, icon: ArrowPathIcon,        light: 'bg-rose-50 text-rose-600'       },
];

// ── Traffic line chart ────────────────────────────────────────────────────────
const trafficChartData = {
    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
    datasets: [
        {
            label: 'Page Views',
            data: [32100, 37400, 41200, 38900, 46300, 51200, 48700, 55400, 61800, 58200, 64100, 71300],
            borderColor: '#6366f1',
            backgroundColor: 'rgba(99,102,241,0.1)',
            borderWidth: 2.5,
            pointRadius: 3,
            tension: 0.4,
            fill: true,
        },
        {
            label: 'Unique Visitors',
            data: [8400, 9700, 10800, 10200, 12100, 13400, 12800, 14500, 16200, 15300, 16800, 18700],
            borderColor: '#10b981',
            backgroundColor: 'rgba(16,185,129,0.08)',
            borderWidth: 2.5,
            pointRadius: 3,
            tension: 0.4,
            fill: true,
        },
    ],
};

const trafficChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: true, position: 'top', labels: { boxWidth: 10, font: { size: 11 } } },
        tooltip: { mode: 'index', intersect: false },
    },
    scales: {
        x: { grid: { display: false }, ticks: { color: '#9ca3af', font: { size: 11 } } },
        y: { grid: { color: '#f3f4f6' }, ticks: { color: '#9ca3af', font: { size: 11 }, callback: (v) => v >= 1000 ? `${(v / 1000).toFixed(0)}k` : v } },
    },
};

// ── Channel bar chart ─────────────────────────────────────────────────────────
const channelChartData = {
    labels: ['Organic', 'Direct', 'Referral', 'Email', 'Social', 'Paid'],
    datasets: [
        {
            label: 'Sessions',
            data: [42300, 31800, 18400, 14200, 22100, 9800],
            backgroundColor: ['#6366f1', '#10b981', '#f59e0b', '#3b82f6', '#ec4899', '#8b5cf6'],
            borderRadius: 6,
            borderSkipped: false,
        },
    ],
};

const channelChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: { callbacks: { label: (ctx) => ` ${ctx.parsed.y.toLocaleString()} sessions` } },
    },
    scales: {
        x: { grid: { display: false }, ticks: { color: '#9ca3af', font: { size: 11 } } },
        y: { grid: { color: '#f3f4f6' }, ticks: { color: '#9ca3af', font: { size: 11 }, callback: (v) => `${(v / 1000).toFixed(0)}k` } },
    },
};

// ── Funnel ────────────────────────────────────────────────────────────────────
const funnel = [
    { label: 'Visitors',           value: 128931, pct: 100, color: 'bg-indigo-500'  },
    { label: 'Signed Up',          value: 18412,  pct: 14.3, color: 'bg-indigo-400' },
    { label: 'Activated',          value: 9847,   pct: 7.6,  color: 'bg-violet-500' },
    { label: 'Converted to Paid',  value: 4952,   pct: 3.8,  color: 'bg-violet-400' },
    { label: 'Retained (90d)',     value: 3714,   pct: 2.9,  color: 'bg-purple-500' },
];

// ── Top pages ─────────────────────────────────────────────────────────────────
const topPages = [
    { page: '/dashboard',         views: '84,210', bounce: '18%', time: '6m 12s' },
    { page: '/pricing',           views: '62,481', bounce: '42%', time: '2m 38s' },
    { page: '/features',          views: '51,394', bounce: '31%', time: '3m 47s' },
    { page: '/docs/api',          views: '38,217', bounce: '14%', time: '8m 04s' },
    { page: '/blog/saas-guide',   views: '27,933', bounce: '55%', time: '1m 52s' },
    { page: '/',                  views: '22,419', bounce: '61%', time: '1m 18s' },
];

const navItems = [
    { label: 'Dashboard',  icon: HomeIcon,          active: false },
    { label: 'Analytics',  icon: ChartBarIcon,      active: true  },
    { label: 'Users',      icon: UsersIcon,         active: false },
    { label: 'Projects',   icon: FolderOpenIcon,    active: false },
    { label: 'Billing',    icon: CreditCardIcon,    active: false },
    { label: 'Settings',   icon: Cog6ToothIcon,     active: false },
];
</script>

<template>
    <Head title="Analytics — Demo" />

    <div class="flex h-screen overflow-hidden bg-gray-50 font-sans antialiased">

        <!-- ── Sidebar ──────────────────────────────────────────────────────── -->
        <aside class="flex w-60 shrink-0 flex-col bg-gray-900 text-gray-300">

            <div class="flex h-16 items-center gap-3 border-b border-gray-800 px-5">
                <img src="/logo.png" alt="Tenantrix" class="h-7" />
                <div>
                    <p class="text-sm font-bold text-white">Tenantrix</p>
                    <p class="text-xs text-gray-500">Admin Console</p>
                </div>
            </div>

            <nav class="flex-1 overflow-y-auto px-3 py-4">
                <a
                    v-for="item in navItems"
                    :key="item.label"
                    href="#"
                    class="mb-0.5 flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors"
                    :class="item.active ? 'bg-indigo-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white'"
                >
                    <component :is="item.icon" class="size-4.5 shrink-0" />
                    {{ item.label }}
                </a>
            </nav>

            <div class="border-t border-gray-800 p-4">
                <div class="flex items-center gap-3">
                    <div class="flex size-8 items-center justify-center rounded-full bg-indigo-600 text-xs font-bold text-white">SA</div>
                    <div class="min-w-0">
                        <p class="truncate text-sm font-medium text-white">Super Admin</p>
                        <p class="truncate text-xs text-gray-500">admin@saas.io</p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- ── Main ─────────────────────────────────────────────────────────── -->
        <div class="flex flex-1 flex-col overflow-hidden">

            <!-- Topbar -->
            <header class="flex h-16 shrink-0 items-center justify-between border-b border-gray-200 bg-white px-6">
                <div>
                    <h1 class="text-lg font-bold text-gray-900">Analytics</h1>
                    <p class="text-xs text-gray-500">Platform-wide data — Last 12 months</p>
                </div>
                <div class="flex items-center gap-3">
                    <button class="inline-flex items-center gap-2 rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">
                        <CalendarDaysIcon class="size-4" /> Jan 2025 – Dec 2025
                    </button>
                    <button class="relative rounded-lg border border-gray-200 p-2 text-gray-500 hover:bg-gray-50">
                        <BellIcon class="size-5" />
                    </button>
                </div>
            </header>

            <!-- Content -->
            <main class="flex-1 overflow-y-auto p-6">

                <!-- KPI cards -->
                <div class="mb-6 grid grid-cols-2 gap-4 xl:grid-cols-4">
                    <div
                        v-for="kpi in kpis"
                        :key="kpi.label"
                        class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm"
                    >
                        <div class="mb-3 flex items-center justify-between">
                            <p class="text-sm font-medium text-gray-500">{{ kpi.label }}</p>
                            <div class="rounded-lg p-2" :class="kpi.light">
                                <component :is="kpi.icon" class="size-4" />
                            </div>
                        </div>
                        <p class="text-2xl font-extrabold text-gray-900">{{ kpi.value }}</p>
                        <div class="mt-1 flex items-center gap-1">
                            <component
                                :is="kpi.up ? ArrowTrendingUpIcon : ArrowTrendingDownIcon"
                                class="size-3.5"
                                :class="kpi.up ? 'text-emerald-500' : 'text-rose-500'"
                            />
                            <span class="text-xs font-semibold" :class="kpi.up ? 'text-emerald-600' : 'text-rose-600'">{{ kpi.change }}</span>
                            <span class="text-xs text-gray-400">vs last year</span>
                        </div>
                    </div>
                </div>

                <!-- Traffic chart -->
                <div class="mb-6 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <p class="font-semibold text-gray-900">Traffic Overview</p>
                            <p class="text-xs text-gray-400">Page views and unique visitors per month</p>
                        </div>
                        <div class="flex gap-2">
                            <button class="rounded-lg bg-indigo-600 px-3 py-1 text-xs font-semibold text-white">All time</button>
                            <button class="rounded-lg px-3 py-1 text-xs font-semibold text-gray-500 hover:bg-gray-100">30d</button>
                            <button class="rounded-lg px-3 py-1 text-xs font-semibold text-gray-500 hover:bg-gray-100">7d</button>
                        </div>
                    </div>
                    <div class="h-56">
                        <Line :data="trafficChartData" :options="trafficChartOptions" />
                    </div>
                </div>

                <!-- Channel chart + Funnel -->
                <div class="mb-6 grid gap-4 lg:grid-cols-2">

                    <!-- Channel bar -->
                    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                        <div class="mb-4">
                            <p class="font-semibold text-gray-900">Traffic by Channel</p>
                            <p class="text-xs text-gray-400">Sessions per acquisition source</p>
                        </div>
                        <div class="h-52">
                            <Bar :data="channelChartData" :options="channelChartOptions" />
                        </div>
                    </div>

                    <!-- Conversion funnel -->
                    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                        <div class="mb-4">
                            <p class="font-semibold text-gray-900">Conversion Funnel</p>
                            <p class="text-xs text-gray-400">Visitor-to-paid conversion steps</p>
                        </div>
                        <div class="space-y-2.5">
                            <div v-for="step in funnel" :key="step.label" class="flex items-center gap-3">
                                <div class="w-36 shrink-0 text-xs font-medium text-gray-600">{{ step.label }}</div>
                                <div class="flex flex-1 items-center gap-2">
                                    <div class="h-5 rounded-md transition-all" :class="step.color" :style="{ width: step.pct + '%' }" />
                                    <span class="text-xs font-semibold text-gray-700">{{ step.value.toLocaleString() }}</span>
                                    <span class="text-xs text-gray-400">({{ step.pct }}%)</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top pages table -->
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                    <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                        <p class="font-semibold text-gray-900">Top Pages</p>
                        <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-500">Last 12 months</span>
                    </div>
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 bg-gray-50">
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Page</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Views</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Bounce Rate</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Avg. Time</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr v-for="row in topPages" :key="row.page" class="hover:bg-gray-50/60">
                                <td class="px-5 py-3 font-mono text-sm text-indigo-600">{{ row.page }}</td>
                                <td class="px-4 py-3 text-right font-medium text-gray-900">{{ row.views }}</td>
                                <td class="px-4 py-3 text-right" :class="parseFloat(row.bounce) > 50 ? 'text-rose-600' : 'text-emerald-600'">{{ row.bounce }}</td>
                                <td class="px-4 py-3 text-right text-gray-600">{{ row.time }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </main>
        </div>
    </div>
</template>
