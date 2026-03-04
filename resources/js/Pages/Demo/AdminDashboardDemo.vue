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
    BuildingOfficeIcon,
    UserGroupIcon,
    BanknotesIcon,
    CreditCardIcon,
    ArrowTrendingUpIcon,
    Cog6ToothIcon,
    HomeIcon,
    ChartBarIcon,
    BellIcon,
    MagnifyingGlassIcon,
    EllipsisVerticalIcon,
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

// ── Stat cards ────────────────────────────────────────────────────────────────
const stats = [
    { label: 'Total Tenants',    value: '1,284',  change: '+12.4%', up: true,  icon: BuildingOfficeIcon, color: 'bg-indigo-500', light: 'bg-indigo-50 text-indigo-700' },
    { label: 'Active Users',     value: '28,391', change: '+8.1%',  up: true,  icon: UserGroupIcon,      color: 'bg-emerald-500', light: 'bg-emerald-50 text-emerald-700' },
    { label: 'MRR',              value: '$84,320', change: '+19.3%', up: true, icon: BanknotesIcon,      color: 'bg-violet-500', light: 'bg-violet-50 text-violet-700' },
    { label: 'Churn Rate',       value: '1.8%',   change: '-0.4%',  up: true,  icon: CreditCardIcon,     color: 'bg-rose-500', light: 'bg-rose-50 text-rose-700' },
];

// ── MRR chart ─────────────────────────────────────────────────────────────────
const mrrChartData = {
    labels: ['Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'Jan', 'Feb', 'Mar'],
    datasets: [
        {
            label: 'MRR ($)',
            data: [52000, 58400, 61200, 67800, 73100, 78500, 81200, 84320],
            borderColor: '#6366f1',
            backgroundColor: 'rgba(99,102,241,0.12)',
            borderWidth: 2.5,
            pointRadius: 3,
            pointBackgroundColor: '#6366f1',
            tension: 0.4,
            fill: true,
        },
    ],
};

const mrrChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false }, tooltip: { mode: 'index', intersect: false } },
    scales: {
        x: { grid: { display: false }, ticks: { color: '#9ca3af', font: { size: 11 } } },
        y: { grid: { color: '#f3f4f6' }, ticks: { color: '#9ca3af', font: { size: 11 }, callback: (v) => `$${(v / 1000).toFixed(0)}k` } },
    },
};

// ── New tenants chart ─────────────────────────────────────────────────────────
const tenantChartData = {
    labels: ['Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'Jan', 'Feb', 'Mar'],
    datasets: [
        {
            label: 'New Tenants',
            data: [48, 62, 71, 88, 95, 114, 127, 134],
            backgroundColor: (ctx) => {
                const chart = ctx.chart;
                const { ctx: c, chartArea } = chart;
                if (!chartArea) { return '#6366f1'; }
                const gradient = c.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                gradient.addColorStop(0, '#6366f1');
                gradient.addColorStop(1, '#a5b4fc');
                return gradient;
            },
            borderRadius: 6,
            borderSkipped: false,
        },
    ],
};

const tenantChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
        x: { grid: { display: false }, ticks: { color: '#9ca3af', font: { size: 11 } } },
        y: { grid: { color: '#f3f4f6' }, ticks: { color: '#9ca3af', font: { size: 11 } } },
    },
};

// ── Tenant table ──────────────────────────────────────────────────────────────
const tenants = [
    { name: 'Acme Corp',       plan: 'Enterprise', users: 342, mrr: '$2,400', status: 'Active',    joined: 'Jan 3, 2025' },
    { name: 'Globex LLC',      plan: 'Pro',        users: 87,  mrr: '$490',   status: 'Active',    joined: 'Jan 11, 2025' },
    { name: 'Initech Inc.',    plan: 'Pro',        users: 54,  mrr: '$490',   status: 'Active',    joined: 'Jan 18, 2025' },
    { name: 'Umbrella Ltd',    plan: 'Starter',    users: 12,  mrr: '$0',     status: 'Trial',     joined: 'Feb 2, 2025' },
    { name: 'Massive Dynamic', plan: 'Enterprise', users: 219, mrr: '$2,400', status: 'Active',    joined: 'Feb 14, 2025' },
    { name: 'Soylent Corp',    plan: 'Pro',        users: 38,  mrr: '$490',   status: 'Suspended', joined: 'Feb 28, 2025' },
];

const planBadge = {
    Enterprise: 'bg-violet-100 text-violet-700',
    Pro:        'bg-blue-100 text-blue-700',
    Starter:    'bg-gray-100 text-gray-600',
};

const statusBadge = {
    Active:    'bg-emerald-100 text-emerald-700',
    Trial:     'bg-amber-100 text-amber-700',
    Suspended: 'bg-rose-100 text-rose-700',
};

// ── Recent activity ───────────────────────────────────────────────────────────
const activity = [
    { user: 'Acme Corp',       action: 'upgraded to Enterprise',      time: '2 min ago',  dot: 'bg-violet-500' },
    { user: 'Umbrella Ltd',    action: 'started a free trial',        time: '18 min ago', dot: 'bg-amber-500'  },
    { user: 'Soylent Corp',    action: 'payment failed — suspended',  time: '1 hr ago',   dot: 'bg-rose-500'   },
    { user: 'Initech Inc.',    action: 'added 12 new users',          time: '3 hr ago',   dot: 'bg-blue-500'   },
    { user: 'Globex LLC',      action: 'renewed Pro subscription',    time: 'Yesterday',  dot: 'bg-emerald-500' },
];

const navItems = [
    { label: 'Dashboard',  icon: HomeIcon,              active: true  },
    { label: 'Tenants',    icon: BuildingOfficeIcon,    active: false },
    { label: 'Users',      icon: UserGroupIcon,         active: false },
    { label: 'Billing',    icon: BanknotesIcon,         active: false },
    { label: 'Analytics',  icon: ChartBarIcon,          active: false },
    { label: 'Settings',   icon: Cog6ToothIcon,         active: false },
];
</script>

<template>
    <Head title="Admin Dashboard — Demo" />

    <div class="flex h-screen overflow-hidden bg-gray-50 font-sans antialiased">

        <!-- ── Sidebar ──────────────────────────────────────────────────────── -->
        <aside class="flex w-60 shrink-0 flex-col bg-gray-900 text-gray-300">

            <!-- Logo -->
            <div class="flex h-16 items-center gap-3 border-b border-gray-800 px-5">
                <img src="/logo.png" alt="Tenantrix" class="h-7" />
                <div>
                    <p class="text-sm font-bold text-white">Tenantrix</p>
                    <p class="text-xs text-gray-500">Admin Console</p>
                </div>
            </div>

            <!-- Nav -->
            <nav class="flex-1 overflow-y-auto px-3 py-4">
                <p class="mb-2 px-2 text-xs font-semibold uppercase tracking-widest text-gray-600">Main</p>
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

            <!-- User -->
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
                    <h1 class="text-lg font-bold text-gray-900">Dashboard</h1>
                    <p class="text-xs text-gray-500">Welcome back, Super Admin</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="relative hidden sm:block">
                        <MagnifyingGlassIcon class="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-gray-400" />
                        <input
                            type="text"
                            placeholder="Search tenants…"
                            class="rounded-lg border border-gray-200 bg-gray-50 py-2 pl-9 pr-4 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                        />
                    </div>
                    <button class="relative rounded-lg border border-gray-200 p-2 text-gray-500 hover:bg-gray-50">
                        <BellIcon class="size-5" />
                        <span class="absolute right-1.5 top-1.5 size-2 rounded-full bg-rose-500" />
                    </button>
                    <div class="flex size-8 items-center justify-center rounded-full bg-indigo-600 text-xs font-bold text-white">SA</div>
                </div>
            </header>

            <!-- Content -->
            <main class="flex-1 overflow-y-auto p-6">

                <!-- Stat cards -->
                <div class="mb-6 grid grid-cols-2 gap-4 xl:grid-cols-4">
                    <div
                        v-for="stat in stats"
                        :key="stat.label"
                        class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm"
                    >
                        <div class="mb-3 flex items-center justify-between">
                            <p class="text-sm font-medium text-gray-500">{{ stat.label }}</p>
                            <div class="rounded-lg p-2" :class="stat.light">
                                <component :is="stat.icon" class="size-4" />
                            </div>
                        </div>
                        <p class="text-2xl font-extrabold text-gray-900">{{ stat.value }}</p>
                        <div class="mt-1 flex items-center gap-1">
                            <ArrowTrendingUpIcon class="size-3.5" :class="stat.up ? 'text-emerald-500' : 'rotate-180 text-rose-500'" />
                            <span class="text-xs font-semibold" :class="stat.up ? 'text-emerald-600' : 'text-rose-600'">{{ stat.change }}</span>
                            <span class="text-xs text-gray-400">vs last month</span>
                        </div>
                    </div>
                </div>

                <!-- Charts row -->
                <div class="mb-6 grid gap-4 lg:grid-cols-3">

                    <!-- MRR line chart — 2/3 -->
                    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm lg:col-span-2">
                        <div class="mb-4 flex items-center justify-between">
                            <div>
                                <p class="font-semibold text-gray-900">Monthly Recurring Revenue</p>
                                <p class="text-xs text-gray-400">Trailing 8 months</p>
                            </div>
                            <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">+19.3%</span>
                        </div>
                        <div class="h-52">
                            <Line :data="mrrChartData" :options="mrrChartOptions" />
                        </div>
                    </div>

                    <!-- New tenants bar chart — 1/3 -->
                    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                        <div class="mb-4">
                            <p class="font-semibold text-gray-900">New Tenants</p>
                            <p class="text-xs text-gray-400">Per month</p>
                        </div>
                        <div class="h-52">
                            <Bar :data="tenantChartData" :options="tenantChartOptions" />
                        </div>
                    </div>
                </div>

                <!-- Tenant table + Activity feed -->
                <div class="grid gap-4 lg:grid-cols-3">

                    <!-- Tenant table — 2/3 -->
                    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm lg:col-span-2">
                        <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                            <p class="font-semibold text-gray-900">Recent Tenants</p>
                            <a href="#" class="text-sm font-medium text-indigo-600 hover:underline">View all</a>
                        </div>
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-100 bg-gray-50">
                                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Tenant</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Plan</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">Users</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">MRR</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <tr v-for="t in tenants" :key="t.name" class="hover:bg-gray-50/60">
                                    <td class="whitespace-nowrap px-5 py-3 font-medium text-gray-900">{{ t.name }}</td>
                                    <td class="px-4 py-3">
                                        <span class="rounded-full px-2 py-0.5 text-xs font-semibold" :class="planBadge[t.plan]">{{ t.plan }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-center text-gray-600">{{ t.users }}</td>
                                    <td class="px-4 py-3 text-right font-medium text-gray-900">{{ t.mrr }}</td>
                                    <td class="px-4 py-3">
                                        <span class="rounded-full px-2 py-0.5 text-xs font-semibold" :class="statusBadge[t.status]">{{ t.status }}</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Activity feed — 1/3 -->
                    <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
                        <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                            <p class="font-semibold text-gray-900">Activity Feed</p>
                            <EllipsisVerticalIcon class="size-4 text-gray-400" />
                        </div>
                        <ul class="divide-y divide-gray-50">
                            <li v-for="ev in activity" :key="ev.user + ev.time" class="flex gap-3 px-5 py-3.5">
                                <div class="mt-1.5 size-2 shrink-0 rounded-full" :class="ev.dot" />
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-800">{{ ev.user }}</p>
                                    <p class="text-xs text-gray-500">{{ ev.action }}</p>
                                    <p class="mt-0.5 text-xs text-gray-400">{{ ev.time }}</p>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>

            </main>
        </div>
    </div>
</template>
