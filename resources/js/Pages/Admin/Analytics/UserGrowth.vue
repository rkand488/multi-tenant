<script setup>
/**
 * User Growth Analytics — showcase page
 *
 * Platform-wide user acquisition, activity, and engagement metrics.
 * All data is hardcoded for GitHub screenshot purposes.
 */

import AdminLayout from '@/Layouts/AdminLayout.vue';
import Card from '@/Components/UI/Card.vue';
import { ref } from 'vue';
import { Bar, Line, Doughnut } from 'vue-chartjs';
import {
    Chart as ChartJS,
    CategoryScale, LinearScale,
    BarElement, PointElement, LineElement, ArcElement,
    Title, Tooltip, Legend, Filler,
} from 'chart.js';
import {
    UserGroupIcon,
    UserPlusIcon,
    BoltIcon,
    CalendarDaysIcon,
    ArrowTrendingUpIcon,
    FireIcon,
} from '@heroicons/vue/24/outline';

ChartJS.register(
    CategoryScale, LinearScale,
    BarElement, PointElement, LineElement, ArcElement,
    Title, Tooltip, Legend, Filler,
);

defineOptions({ layout: AdminLayout });

// ── KPI cards ─────────────────────────────────────────────────────────────────
const kpis = [
    { label: 'Total Users',    value: '9,471',  change: '+15.7%', up: true,  icon: UserGroupIcon,   bg: 'bg-indigo-50 dark:bg-indigo-900/20',   icon_color: 'text-indigo-600 dark:text-indigo-400' },
    { label: 'New This Month', value: '847',    change: '+22.1%', up: true,  icon: UserPlusIcon,    bg: 'bg-emerald-50 dark:bg-emerald-900/20', icon_color: 'text-emerald-600 dark:text-emerald-400' },
    { label: 'WAU',            value: '3,284',  change: '+9.3%',  up: true,  icon: BoltIcon,        bg: 'bg-violet-50 dark:bg-violet-900/20',  icon_color: 'text-violet-600 dark:text-violet-400' },
    { label: 'MAU',            value: '6,102',  change: '+11.8%', up: true,  icon: CalendarDaysIcon,bg: 'bg-sky-50 dark:bg-sky-900/20',         icon_color: 'text-sky-600 dark:text-sky-400' },
    { label: 'Avg per Tenant', value: '7.4',    change: '+0.8',   up: true,  icon: ArrowTrendingUpIcon, bg: 'bg-amber-50 dark:bg-amber-900/20', icon_color: 'text-amber-600 dark:text-amber-400' },
    { label: 'DAU/MAU Ratio',  value: '31.4%',  change: '+2.1pp', up: true,  icon: FireIcon,        bg: 'bg-rose-50 dark:bg-rose-900/20',       icon_color: 'text-rose-600 dark:text-rose-400' },
];

// ── User signup trend (12 months) ─────────────────────────────────────────────
const months12 = ['Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'Jan', 'Feb', 'Mar'];

const signupTrendData = {
    labels: months12,
    datasets: [
        {
            label: 'New Signups',
            data: [412, 489, 521, 467, 604, 678, 590, 712, 748, 803, 821, 847],
            borderColor: '#6366f1',
            backgroundColor: 'rgba(99,102,241,0.12)',
            fill: true,
            tension: 0.4,
            borderWidth: 2.5,
            pointRadius: 4,
            pointBackgroundColor: '#6366f1',
            yAxisID: 'y',
        },
        {
            label: 'Cumulative',
            data: [5800, 6289, 6810, 7277, 7881, 8559, 9149, 9861, 10609, 11412, 12233, 13080],
            borderColor: '#10b981',
            backgroundColor: 'transparent',
            tension: 0.4,
            borderWidth: 2,
            borderDash: [5, 3],
            pointRadius: 0,
            yAxisID: 'y1',
        },
    ],
};

const signupTrendOptions = {
    responsive: true,
    maintainAspectRatio: false,
    interaction: { mode: 'index', intersect: false },
    plugins: {
        legend: { position: 'top', labels: { boxWidth: 10, font: { size: 11 } } },
    },
    scales: {
        x: { grid: { display: false } },
        y:  { position: 'left',  grid: { color: 'rgba(0,0,0,0.04)' }, title: { display: true, text: 'New Signups', font: { size: 10 } } },
        y1: { position: 'right', grid: { drawOnChartArea: false }, title: { display: true, text: 'Cumulative', font: { size: 10 } },
              ticks: { callback: (v) => `${(v / 1000).toFixed(0)}k` } },
    },
};

// ── Daily active users (last 30 days) ─────────────────────────────────────────
const days30 = Array.from({ length: 30 }, (_, i) => {
    const d = new Date(2026, 1, 1 + i);
    return `${d.getMonth() + 1}/${d.getDate()}`;
});

const dauData = {
    labels: days30,
    datasets: [{
        label: 'DAU',
        data: [
            1820, 1890, 1755, 1910, 2010, 1640, 1580,
            1940, 2020, 2100, 1985, 2150, 2090, 1710,
            1660, 2000, 2180, 2240, 2130, 2290, 2060, 1820,
            1770, 2120, 2310, 2400, 2280, 2460, 2380, 2150,
        ],
        backgroundColor: (ctx) => {
            const val = ctx.dataset.data[ctx.dataIndex];
            return val >= 2300 ? 'rgba(99,102,241,0.9)' : val >= 2000 ? 'rgba(99,102,241,0.65)' : 'rgba(99,102,241,0.4)';
        },
        borderRadius: 4,
        borderSkipped: false,
    }],
};

const dauOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
        x: { grid: { display: false }, ticks: { maxTicksLimit: 10 } },
        y: { grid: { color: 'rgba(0,0,0,0.04)' }, beginAtZero: false },
    },
};

// ── Users by plan ─────────────────────────────────────────────────────────────
const usersByPlanData = {
    labels: months12,
    datasets: [
        { label: 'Enterprise', data: [201, 218, 240, 265, 290, 312, 344, 371, 398, 427, 451, 478], backgroundColor: '#10b981', borderRadius: 3, stack: 's' },
        { label: 'Pro',        data: [580, 630, 680, 730, 780, 830, 890, 950, 1010, 1075, 1140, 1200], backgroundColor: '#06b6d4', borderRadius: 3, stack: 's' },
        { label: 'Growth',     data: [1200, 1300, 1400, 1510, 1620, 1730, 1850, 1970, 2090, 2220, 2340, 2460], backgroundColor: '#8b5cf6', borderRadius: 3, stack: 's' },
        { label: 'Starter',    data: [1840, 1960, 2080, 2190, 2320, 2450, 2590, 2730, 2870, 3010, 3140, 3270], backgroundColor: '#6366f1', borderRadius: 3, stack: 's' },
    ],
};

const stackedBarOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { position: 'top', reverse: true, labels: { boxWidth: 10, font: { size: 11 } } },
    },
    scales: {
        x: { grid: { display: false }, stacked: true },
        y: { stacked: true, grid: { color: 'rgba(0,0,0,0.04)' },
             ticks: { callback: (v) => `${(v / 1000).toFixed(1)}k` } },
    },
};

// ── Role distribution ─────────────────────────────────────────────────────────
const roleData = {
    labels: ['Admin', 'Manager', 'Member', 'Viewer', 'Billing'],
    datasets: [{
        data: [1284, 1782, 4826, 1160, 419],
        backgroundColor: ['#6366f1', '#8b5cf6', '#06b6d4', '#10b981', '#f59e0b'],
        borderWidth: 0,
        hoverOffset: 6,
    }],
};

const doughnutOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { position: 'right', labels: { boxWidth: 10, font: { size: 11 }, padding: 12 } },
    },
    cutout: '68%',
};

// ── Top workspaces by user count ──────────────────────────────────────────────
const topWorkspaces = [
    { name: 'Globex Technologies',  users: 84, plan: 'Enterprise', growth: '+12' },
    { name: 'Initech Solutions',    users: 71, plan: 'Enterprise', growth: '+7'  },
    { name: 'Umbrella Corp',        users: 68, plan: 'Pro',        growth: '+4'  },
    { name: 'Dunder Mifflin Inc.',  users: 63, plan: 'Pro',        growth: '+9'  },
    { name: 'Sterling Cooper',      users: 58, plan: 'Growth',     growth: '+3'  },
    { name: 'Hooli Inc.',           users: 54, plan: 'Enterprise', growth: '+11' },
    { name: 'Pied Piper',           users: 49, plan: 'Pro',        growth: '+6'  },
    { name: 'Massive Dynamic',      users: 44, plan: 'Growth',     growth: '+2'  },
];

const planBadge = { Enterprise: 'bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-300', Pro: 'bg-sky-100 text-sky-700 dark:bg-sky-900/30 dark:text-sky-300', Growth: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' };

// ── Page filter ───────────────────────────────────────────────────────────────
const range  = ref('12m');
const ranges = ['30d', '3m', '6m', '12m'];
</script>

<template>
    <div class="space-y-6">

        <!-- ── Header ─────────────────────────────────────────────────────── -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">User Growth</h1>
                <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Signup trends, activity, engagement, and workspace distribution</p>
            </div>
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

        <!-- ── KPI grid ───────────────────────────────────────────────────── -->
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-3 xl:grid-cols-6">
            <div
                v-for="kpi in kpis"
                :key="kpi.label"
                class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-100 dark:bg-gray-900 dark:ring-gray-800"
            >
                <div class="flex items-start justify-between">
                    <div class="min-w-0">
                        <p class="text-2xl font-bold tracking-tight text-gray-900 dark:text-gray-100">{{ kpi.value }}</p>
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ kpi.label }}</p>
                        <p class="mt-1.5 flex items-center gap-0.5 text-xs font-semibold" :class="kpi.up ? 'text-emerald-600' : 'text-rose-500'">
                            <ArrowTrendingUpIcon class="size-3" />
                            {{ kpi.change }}
                        </p>
                    </div>
                    <div class="flex size-9 shrink-0 items-center justify-center rounded-xl" :class="kpi.bg">
                        <component :is="kpi.icon" class="size-5" :class="kpi.icon_color" />
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Signup trend + Role mix ─────────────────────────────────────── -->
        <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
            <Card title="Signup Trend" subtitle="New users vs cumulative total" :padded="false" class="xl:col-span-2">
                <div class="h-64 px-5 pb-5 pt-2">
                    <Line :data="signupTrendData" :options="signupTrendOptions" />
                </div>
            </Card>

            <Card title="Role Distribution" subtitle="Users by assigned role" :padded="false">
                <div class="flex h-64 flex-col items-center justify-center px-4 pb-5 pt-2">
                    <Doughnut :data="roleData" :options="doughnutOptions" />
                </div>
            </Card>
        </div>

        <!-- ── DAU (30 d) ──────────────────────────────────────────────────── -->
        <Card title="Daily Active Users" subtitle="Login / session activity over the last 30 days" :padded="false">
            <div class="h-60 px-5 pb-5 pt-2">
                <Bar :data="dauData" :options="dauOptions" />
            </div>
        </Card>

        <!-- ── Users by plan over time ─────────────────────────────────────── -->
        <Card title="User Growth by Plan" subtitle="Stacked monthly totals per subscription tier" :padded="false">
            <div class="h-64 px-5 pb-5 pt-2">
                <Bar :data="usersByPlanData" :options="stackedBarOptions" />
            </div>
        </Card>

        <!-- ── Top workspaces table ───────────────────────────────────────── -->
        <Card title="Largest Workspaces" subtitle="Ranked by active user count">
            <div class="-mx-5 -mb-4 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-700/50">
                            <th class="pb-3 pl-5 pr-4 text-left text-xs font-medium uppercase tracking-wide text-gray-500">#</th>
                            <th class="px-4 pb-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Workspace</th>
                            <th class="px-4 pb-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Plan</th>
                            <th class="px-4 pb-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Users</th>
                            <th class="px-5 pb-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">+30d</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                        <tr
                            v-for="(ws, idx) in topWorkspaces"
                            :key="ws.name"
                            class="group hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors"
                        >
                            <td class="py-3 pl-5 pr-4 text-sm font-bold text-gray-300 dark:text-gray-600">{{ String(idx + 1).padStart(2, '0') }}</td>
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100">{{ ws.name }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full px-2 py-0.5 text-xs font-medium" :class="planBadge[ws.plan]">{{ ws.plan }}</span>
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-gray-700 dark:text-gray-300">{{ ws.users }}</td>
                            <td class="px-5 py-3 text-right text-xs font-semibold text-emerald-600 dark:text-emerald-400">{{ ws.growth }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </Card>
    </div>
</template>
