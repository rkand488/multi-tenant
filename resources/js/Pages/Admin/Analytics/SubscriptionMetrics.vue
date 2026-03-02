<script setup>
/**
 * Subscription Metrics — showcase page
 *
 * Visual-first analytics hub for SaaS subscription health.
 * All data is hardcoded for GitHub screenshot purposes.
 */

import AdminLayout from '@/Layouts/AdminLayout.vue';
import Card from '@/Components/UI/Card.vue';
import { ref, computed } from 'vue';
import { Bar, Line, Doughnut } from 'vue-chartjs';
import {
    Chart as ChartJS,
    CategoryScale, LinearScale,
    BarElement, PointElement, LineElement, ArcElement,
    Title, Tooltip, Legend, Filler,
} from 'chart.js';
import {
    CurrencyDollarIcon,
    ArrowTrendingUpIcon,
    ArrowTrendingDownIcon,
    UsersIcon,
    XMarkIcon,
    ArrowPathIcon,
    SparklesIcon,
} from '@heroicons/vue/24/outline';

ChartJS.register(
    CategoryScale, LinearScale,
    BarElement, PointElement, LineElement, ArcElement,
    Title, Tooltip, Legend, Filler,
);

defineOptions({ layout: AdminLayout });

// ── KPI strip ─────────────────────────────────────────────────────────────────
const kpis = [
    { label: 'MRR',         value: '$48,290',  sub: '+12.4% vs last month', up: true,  color: 'from-indigo-500 to-violet-600',  icon: CurrencyDollarIcon },
    { label: 'ARR',         value: '$579,480', sub: 'Annualised run rate',  up: true,  color: 'from-violet-500 to-purple-600',  icon: SparklesIcon },
    { label: 'New MRR',     value: '$5,814',   sub: '+8 new subscriptions', up: true,  color: 'from-emerald-500 to-teal-600',   icon: ArrowTrendingUpIcon },
    { label: 'Churn MRR',   value: '$1,230',   sub: '-3 cancellations',     up: false, color: 'from-rose-500 to-red-600',       icon: XMarkIcon },
    { label: 'Net Rev Ret', value: '108%',     sub: 'Expansion > churn',    up: true,  color: 'from-amber-500 to-orange-500',   icon: ArrowPathIcon },
    { label: 'Subscribers', value: '1,284',    sub: '+47 this month',       up: true,  color: 'from-sky-500 to-cyan-600',       icon: UsersIcon },
];

// ── MRR waterfall (12 months) ─────────────────────────────────────────────────
const months = ['Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'Jan', 'Feb', 'Mar'];

const mrrStackedData = {
    labels: months,
    datasets: [
        {
            label: 'New MRR',
            data: [3800, 4200, 4600, 3900, 5100, 5400, 4800, 5200, 5600, 5100, 5500, 5814],
            backgroundColor: 'rgba(99,102,241,0.85)',
            borderRadius: 4,
            stack: 'mrr',
        },
        {
            label: 'Expansion MRR',
            data: [900, 1100, 850, 1200, 1300, 980, 1400, 1100, 1250, 1380, 1500, 1640],
            backgroundColor: 'rgba(16,185,129,0.85)',
            borderRadius: 4,
            stack: 'mrr',
        },
        {
            label: 'Churned MRR',
            data: [-900, -780, -1100, -860, -970, -820, -1050, -780, -910, -1020, -980, -1230],
            backgroundColor: 'rgba(244,63,94,0.8)',
            borderRadius: 4,
            stack: 'mrr',
        },
    ],
};

const mrrStackedOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { position: 'top', labels: { boxWidth: 10, font: { size: 11 } } },
        tooltip: { callbacks: { label: (c) => ` $${Math.abs(c.raw).toLocaleString()}` } },
    },
    scales: {
        x: { grid: { display: false }, stacked: true },
        y: { stacked: true, grid: { color: 'rgba(0,0,0,0.04)' },
             ticks: { callback: (v) => `$${(v / 1000).toFixed(0)}k` } },
    },
};

// ── Cumulative MRR trend ──────────────────────────────────────────────────────
const mrrTrendData = {
    labels: months,
    datasets: [{
        label: 'MRR',
        data: [33200, 36800, 40100, 43300, 47200, 50800, 53900, 57400, 61100, 64600, 67800, 70290],
        borderColor: '#6366f1',
        backgroundColor: 'rgba(99,102,241,0.1)',
        fill: true,
        tension: 0.4,
        borderWidth: 2.5,
        pointRadius: 3,
        pointBackgroundColor: '#6366f1',
    }],
};

const mrrTrendOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
        x: { grid: { display: false } },
        y: { grid: { color: 'rgba(0,0,0,0.04)' },
             ticks: { callback: (v) => `$${(v / 1000).toFixed(0)}k` } },
    },
};

// ── Plan revenue mix ──────────────────────────────────────────────────────────
const planMixData = {
    labels: ['Starter', 'Growth', 'Pro', 'Enterprise'],
    datasets: [{
        data: [11400, 18600, 22800, 17640],
        backgroundColor: ['#6366f1', '#8b5cf6', '#06b6d4', '#10b981'],
        borderWidth: 0,
        hoverOffset: 6,
    }],
};

const doughnutOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { position: 'right', labels: { boxWidth: 11, font: { size: 11 }, padding: 14 } },
        tooltip: { callbacks: { label: (c) => ` $${c.raw.toLocaleString()} MRR` } },
    },
    cutout: '68%',
};

// ── Cohort retention table ────────────────────────────────────────────────────
const cohortMonths = ['Month 0', 'Month 1', 'Month 2', 'Month 3', 'Month 4', 'Month 5'];
const cohorts = [
    { month: 'Oct 2025', size: 74,  data: [100, 92, 88, 85, 82, 80] },
    { month: 'Nov 2025', size: 81,  data: [100, 94, 90, 87, 83, null] },
    { month: 'Dec 2025', size: 69,  data: [100, 91, 86, 83, null, null] },
    { month: 'Jan 2026', size: 88,  data: [100, 93, 89, null, null, null] },
    { month: 'Feb 2026', size: 97,  data: [100, 95, null, null, null, null] },
    { month: 'Mar 2026', size: 103, data: [100, null, null, null, null, null] },
];

const retentionColor = (v) => {
    if (v === null)  { return 'bg-gray-50 text-gray-300 dark:bg-gray-800 dark:text-gray-600'; }
    if (v === 100)   { return 'bg-indigo-600 text-white'; }
    if (v >= 90)     { return 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300'; }
    if (v >= 80)     { return 'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/20 dark:text-indigo-400'; }
    return 'bg-amber-50 text-amber-700 dark:bg-amber-900/20 dark:text-amber-400';
};

// ── Plan stats table ──────────────────────────────────────────────────────────
const plans = [
    { name: 'Starter',    price: '$29',  tenants: 393, mrr: '$11,397', churn: '3.1%', growth: '+6.2%'  },
    { name: 'Growth',     price: '$79',  tenants: 236, mrr: '$18,644', churn: '2.4%', growth: '+11.4%' },
    { name: 'Pro',        price: '$149', tenants: 153, mrr: '$22,797', churn: '1.8%', growth: '+14.2%' },
    { name: 'Enterprise', price: 'Custom', tenants: 47, mrr: '$17,640', churn: '0.9%', growth: '+18.7%' },
];

// ── page range filter ─────────────────────────────────────────────────────────
const range  = ref('12m');
const ranges = ['30d', '3m', '6m', '12m'];
</script>

<template>
    <div class="space-y-6">

        <!-- ── Header ─────────────────────────────────────────────────────── -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">Subscription Metrics</h1>
                <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Revenue health, plan performance, and cohort retention</p>
            </div>
            <!-- Range picker -->
            <div class="flex items-center gap-1 self-start rounded-lg bg-gray-100 p-0.5 dark:bg-gray-800">
                <button
                    v-for="r in ranges"
                    :key="r"
                    type="button"
                    class="rounded-md px-3 py-1 text-xs font-medium transition-colors"
                    :class="range === r
                        ? 'bg-white text-gray-900 shadow-sm dark:bg-gray-700 dark:text-gray-100'
                        : 'text-gray-500 hover:text-gray-700 dark:text-gray-400'"
                    @click="range = r"
                >
                    {{ r }}
                </button>
            </div>
        </div>

        <!-- ── KPI strip ──────────────────────────────────────────────────── -->
        <div class="grid grid-cols-2 gap-3 lg:grid-cols-3 xl:grid-cols-6">
            <div
                v-for="kpi in kpis"
                :key="kpi.label"
                class="relative overflow-hidden rounded-2xl p-5 text-white shadow-md"
                :class="`bg-gradient-to-br ${kpi.color}`"
            >
                <!-- Decorative circle -->
                <div class="pointer-events-none absolute -right-4 -top-4 size-20 rounded-full bg-white/10" />
                <div class="pointer-events-none absolute -bottom-6 -left-3 size-16 rounded-full bg-white/10" />

                <component :is="kpi.icon" class="mb-3 size-6 opacity-90" />
                <p class="text-2xl font-bold tracking-tight">{{ kpi.value }}</p>
                <p class="mt-0.5 text-xs font-medium uppercase tracking-wider opacity-80">{{ kpi.label }}</p>
                <p class="mt-1 text-xs opacity-70">{{ kpi.sub }}</p>
            </div>
        </div>

        <!-- ── MRR trend + Plan mix ────────────────────────────────────────── -->
        <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
            <Card title="Cumulative MRR Trend" subtitle="12-month growth trajectory" :padded="false" class="xl:col-span-2">
                <div class="h-64 px-5 pb-5 pt-2">
                    <Line :data="mrrTrendData" :options="mrrTrendOptions" />
                </div>
            </Card>

            <Card title="Revenue by Plan" subtitle="MRR contribution mix" :padded="false">
                <div class="flex h-64 flex-col items-center justify-center px-4 pb-5 pt-2">
                    <Doughnut :data="planMixData" :options="doughnutOptions" />
                </div>
            </Card>
        </div>

        <!-- ── MRR waterfall ──────────────────────────────────────────────── -->
        <Card title="MRR Waterfall" subtitle="New · Expansion · Churn breakdown per month" :padded="false">
            <div class="h-64 px-5 pb-5 pt-2">
                <Bar :data="mrrStackedData" :options="mrrStackedOptions" />
            </div>
        </Card>

        <!-- ── Plan performance table ─────────────────────────────────────── -->
        <Card title="Plan Performance">
            <div class="-mx-5 -mb-4 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-700/50">
                            <th class="px-5 pb-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Plan</th>
                            <th class="px-4 pb-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Price</th>
                            <th class="px-4 pb-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Tenants</th>
                            <th class="px-4 pb-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">MRR</th>
                            <th class="px-4 pb-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Churn</th>
                            <th class="px-5 pb-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Growth</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                        <tr
                            v-for="plan in plans"
                            :key="plan.name"
                            class="group hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors"
                        >
                            <td class="px-5 py-3.5 font-semibold text-gray-900 dark:text-gray-100">{{ plan.name }}</td>
                            <td class="px-4 py-3.5 text-right text-gray-500">{{ plan.price }}</td>
                            <td class="px-4 py-3.5 text-right font-medium text-gray-700 dark:text-gray-300">{{ plan.tenants.toLocaleString() }}</td>
                            <td class="px-4 py-3.5 text-right font-semibold text-gray-900 dark:text-gray-100">{{ plan.mrr }}</td>
                            <td class="px-4 py-3.5 text-right">
                                <span class="inline-flex rounded-full bg-rose-50 px-2 py-0.5 text-xs font-medium text-rose-600 dark:bg-rose-900/20 dark:text-rose-400">
                                    {{ plan.churn }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <span class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                                    <ArrowTrendingUpIcon class="size-3" />
                                    {{ plan.growth }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </Card>

        <!-- ── Cohort retention ───────────────────────────────────────────── -->
        <Card title="Cohort Retention" subtitle="Monthly subscription retention by cohort">
            <div class="overflow-x-auto">
                <table class="w-full text-center text-xs">
                    <thead>
                        <tr>
                            <th class="pb-3 pr-4 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Cohort</th>
                            <th class="pb-3 pr-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Size</th>
                            <th
                                v-for="m in cohortMonths"
                                :key="m"
                                class="px-2 pb-3 text-xs font-medium uppercase tracking-wide text-gray-500"
                            >
                                {{ m }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="space-y-1">
                        <tr v-for="c in cohorts" :key="c.month" class="group">
                            <td class="pb-2 pr-4 text-left text-sm font-medium text-gray-700 dark:text-gray-300">{{ c.month }}</td>
                            <td class="pb-2 pr-3 text-left text-gray-500">{{ c.size }}</td>
                            <td v-for="(v, idx) in c.data" :key="idx" class="px-1 pb-2">
                                <span
                                    class="inline-flex h-8 w-14 items-center justify-center rounded-lg text-xs font-semibold transition-transform group-hover:scale-105"
                                    :class="retentionColor(v)"
                                >
                                    {{ v !== null ? `${v}%` : '—' }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </Card>
    </div>
</template>
