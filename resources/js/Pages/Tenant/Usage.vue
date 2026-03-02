<script setup>
/**
 * Tenant Usage Dashboard — showcase page
 *
 * Real-time usage metrics for a workspace: storage, users, API calls, files.
 * All data is hardcoded for GitHub screenshot purposes.
 */

import TenantLayout from '@/Layouts/TenantLayout.vue';
import Card from '@/Components/UI/Card.vue';
import { computed } from 'vue';
import { Bar, Line, Doughnut } from 'vue-chartjs';
import {
    Chart as ChartJS,
    CategoryScale, LinearScale,
    BarElement, PointElement, LineElement, ArcElement,
    Title, Tooltip, Legend, Filler,
} from 'chart.js';
import {
    UsersIcon,
    CircleStackIcon,
    BoltIcon,
    DocumentIcon,
    ArrowTrendingUpIcon,
    CheckCircleIcon,
} from '@heroicons/vue/24/outline';
import { ExclamationTriangleIcon } from '@heroicons/vue/24/solid';

ChartJS.register(
    CategoryScale, LinearScale,
    BarElement, PointElement, LineElement, ArcElement,
    Title, Tooltip, Legend, Filler,
);

defineOptions({ layout: TenantLayout });

// ── Usage metrics ──────────────────────────────────────────────────────────────
const metrics = [
    { label: 'Team Members', used: 18, limit: 25,   unit: 'users',   icon: UsersIcon,        color: 'indigo' },
    { label: 'Storage',      used: 14.3, limit: 20, unit: 'GB',      icon: CircleStackIcon,  color: 'violet' },
    { label: 'API Calls',    used: 84200, limit: 100000, unit: 'this month', icon: BoltIcon, color: 'sky'    },
    { label: 'Files Stored', used: 1847, limit: 5000, unit: 'files',  icon: DocumentIcon,     color: 'emerald'},
];

const pct = (u, l) => Math.min(100, Math.round((u / l) * 100));

const barColor = (p) => {
    if (p >= 90) { return { track: 'bg-red-100 dark:bg-red-900/20', fill: 'bg-red-500', text: 'text-red-600 dark:text-red-400' }; }
    if (p >= 70) { return { track: 'bg-amber-100 dark:bg-amber-900/20', fill: 'bg-amber-400', text: 'text-amber-600 dark:text-amber-400' }; }
    return { track: 'bg-indigo-100 dark:bg-indigo-900/20', fill: 'bg-indigo-500', text: 'text-indigo-600 dark:text-indigo-400' };
};

const iconColorMap = {
    indigo:  'bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400',
    violet:  'bg-violet-50 dark:bg-violet-900/20 text-violet-600 dark:text-violet-400',
    sky:     'bg-sky-50 dark:bg-sky-900/20 text-sky-600 dark:text-sky-400',
    emerald: 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400',
};

const formattedUsed = (m) => {
    if (m.unit === 'this month') { return m.used.toLocaleString(); }
    return m.used;
};

const formattedLimit = (m) => {
    if (m.unit === 'this month') { return m.limit.toLocaleString(); }
    return m.limit;
};

// ── API calls — last 14 days ──────────────────────────────────────────────────
const days14 = ['18 Feb', '19 Feb', '20 Feb', '21 Feb', '22 Feb', '23 Feb', '24 Feb', '25 Feb', '26 Feb', '27 Feb', '28 Feb', '1 Mar', '2 Mar', '3 Mar'];

const apiCallsData = {
    labels: days14,
    datasets: [
        {
            label: 'Read',
            data: [3200, 2900, 3400, 2800, 3600, 1800, 1600, 3800, 4100, 4400, 3900, 4600, 5100, 4800],
            backgroundColor: 'rgba(99,102,241,0.75)',
            borderRadius: 4,
            stack: 's',
        },
        {
            label: 'Write',
            data: [1100, 950, 1300, 1050, 1450, 680, 590, 1400, 1550, 1700, 1480, 1820, 2010, 1940],
            backgroundColor: 'rgba(139,92,246,0.75)',
            borderRadius: 4,
            stack: 's',
        },
        {
            label: 'Delete',
            data: [210, 180, 290, 150, 310, 120, 80, 260, 300, 340, 280, 390, 430, 380],
            backgroundColor: 'rgba(244,63,94,0.65)',
            borderRadius: 4,
            stack: 's',
        },
    ],
};

const apiCallsOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { position: 'top', labels: { boxWidth: 10, font: { size: 11 } } },
    },
    scales: {
        x: { grid: { display: false }, stacked: true },
        y: { stacked: true, grid: { color: 'rgba(0,0,0,0.04)' },
             ticks: { callback: (v) => `${(v / 1000).toFixed(0)}k` } },
    },
};

// ── Storage growth over 6 months ──────────────────────────────────────────────
const storageLabels = ['Oct', 'Nov', 'Dec', 'Jan', 'Feb', 'Mar'];

const storageData = {
    labels: storageLabels,
    datasets: [{
        label: 'Storage (GB)',
        data: [6.2, 7.8, 9.4, 11.1, 12.7, 14.3],
        borderColor: '#8b5cf6',
        backgroundColor: 'rgba(139,92,246,0.12)',
        fill: true,
        tension: 0.4,
        borderWidth: 2.5,
        pointRadius: 5,
        pointBackgroundColor: '#8b5cf6',
        pointHoverRadius: 7,
    }],
};

const storageOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
        x: { grid: { display: false } },
        y: {
            grid: { color: 'rgba(0,0,0,0.04)' },
            ticks: { callback: (v) => `${v} GB` },
        },
    },
};

// ── Feature usage breakdown (donut) ──────────────────────────────────────────
const featureData = {
    labels: ['CRM Module', 'File Manager', 'Analytics', 'API Access', 'Integrations'],
    datasets: [{
        data: [38, 24, 19, 12, 7],
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

// ── Recent activity feed ──────────────────────────────────────────────────────
const activityFeed = [
    { user: 'Sarah Chen',    action: 'Uploaded',      target: 'Q4 Report.pdf',    time: '2m ago',  type: 'upload' },
    { user: 'Marcus Reid',   action: 'Invited',        target: 'jessica@acme.com', time: '18m ago', type: 'invite' },
    { user: 'Priya Sharma',  action: 'Updated',        target: 'Project Settings', time: '1h ago',  type: 'edit'   },
    { user: 'Tom Nguyen',    action: 'Exported',       target: 'CRM Data (CSV)',   time: '2h ago',  type: 'export' },
    { user: 'Sarah Chen',    action: 'Created role',   target: 'Finance Viewer',   time: '3h ago',  type: 'role'   },
    { user: 'Alex Kim',      action: 'Removed',        target: 'Old Backup.zip',   time: '5h ago',  type: 'delete' },
    { user: 'Marcus Reid',   action: 'API key created', target: 'Dashboard App',   time: 'Yesterday', type: 'api' },
];

const actionColor = {
    upload: 'text-indigo-600 bg-indigo-50 dark:bg-indigo-900/20 dark:text-indigo-400',
    invite: 'text-emerald-600 bg-emerald-50 dark:bg-emerald-900/20 dark:text-emerald-400',
    edit:   'text-sky-600 bg-sky-50 dark:bg-sky-900/20 dark:text-sky-400',
    export: 'text-violet-600 bg-violet-50 dark:bg-violet-900/20 dark:text-violet-400',
    role:   'text-amber-600 bg-amber-50 dark:bg-amber-900/20 dark:text-amber-400',
    delete: 'text-rose-600 bg-rose-50 dark:bg-rose-900/20 dark:text-rose-400',
    api:    'text-gray-600 bg-gray-100 dark:bg-gray-800 dark:text-gray-400',
};

// ── Plan info ─────────────────────────────────────────────────────────────────
const plan = { name: 'Pro', renewsAt: 'Apr 1, 2026', price: '$149/mo' };
</script>

<template>
    <div class="space-y-6">

        <!-- ── Page header ────────────────────────────────────────────────── -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">Workspace Usage</h1>
                <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                    Monitor resource consumption, activity, and feature engagement for your workspace.
                </p>
            </div>
            <!-- Plan badge -->
            <div class="flex shrink-0 items-center gap-3 rounded-xl bg-gradient-to-r from-indigo-50 to-violet-50 px-4 py-2.5 dark:from-indigo-900/20 dark:to-violet-900/20">
                <div class="flex size-8 items-center justify-center rounded-lg bg-indigo-600">
                    <CheckCircleIcon class="size-4 text-white" />
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ plan.name }} Plan</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ plan.price }} · Renews {{ plan.renewsAt }}</p>
                </div>
            </div>
        </div>

        <!-- ── Usage meter cards ──────────────────────────────────────────── -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div
                v-for="m in metrics"
                :key="m.label"
                class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-100 dark:bg-gray-900 dark:ring-gray-800"
            >
                <div class="mb-4 flex items-start justify-between">
                    <div class="flex size-10 items-center justify-center rounded-xl" :class="iconColorMap[m.color]">
                        <component :is="m.icon" class="size-5" />
                    </div>
                    <span
                        v-if="pct(m.used, m.limit) >= 80"
                        class="flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium"
                        :class="pct(m.used, m.limit) >= 90
                            ? 'bg-red-50 text-red-600 dark:bg-red-900/20 dark:text-red-400'
                            : 'bg-amber-50 text-amber-600 dark:bg-amber-900/20 dark:text-amber-400'"
                    >
                        <ExclamationTriangleIcon class="size-3" />
                        {{ pct(m.used, m.limit) >= 90 ? 'Critical' : 'High' }}
                    </span>
                </div>

                <p class="text-2xl font-bold tracking-tight text-gray-900 dark:text-gray-100">
                    {{ formattedUsed(m) }}
                    <span class="text-sm font-normal text-gray-400">/ {{ formattedLimit(m) }}</span>
                </p>
                <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">{{ m.label }}</p>

                <!-- Progress bar -->
                <div class="mt-3">
                    <div class="mb-1 flex justify-between text-xs">
                        <span class="font-medium" :class="barColor(pct(m.used, m.limit)).text">
                            {{ pct(m.used, m.limit) }}% used
                        </span>
                        <span class="text-gray-400">{{ 100 - pct(m.used, m.limit) }}% remaining</span>
                    </div>
                    <div class="h-2 w-full overflow-hidden rounded-full" :class="barColor(pct(m.used, m.limit)).track">
                        <div
                            class="h-full rounded-full transition-all duration-700"
                            :class="barColor(pct(m.used, m.limit)).fill"
                            :style="`width: ${pct(m.used, m.limit)}%`"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- ── API calls chart + feature usage ───────────────────────────── -->
        <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
            <Card title="API Calls (Last 14 Days)" subtitle="Read · Write · Delete breakdown" :padded="false" class="xl:col-span-2">
                <div class="h-60 px-5 pb-5 pt-2">
                    <Bar :data="apiCallsData" :options="apiCallsOptions" />
                </div>
            </Card>

            <Card title="Feature Usage" subtitle="Activity share by module" :padded="false">
                <div class="flex h-60 items-center justify-center px-4 pb-5 pt-2">
                    <Doughnut :data="featureData" :options="doughnutOptions" />
                </div>
            </Card>
        </div>

        <!-- ── Storage growth ─────────────────────────────────────────────── -->
        <Card title="Storage Growth" subtitle="Cumulative storage consumption (last 6 months)" :padded="false">
            <div class="h-56 px-5 pb-5 pt-2">
                <Line :data="storageData" :options="storageOptions" />
            </div>
        </Card>

        <!-- ── Activity feed + quota summary ─────────────────────────────── -->
        <div class="grid grid-cols-1 gap-4 xl:grid-cols-5">

            <!-- Activity feed -->
            <Card title="Recent Activity" class="xl:col-span-3">
                <div class="-mx-5 -mb-4">
                    <div
                        v-for="(item, idx) in activityFeed"
                        :key="idx"
                        class="flex items-start gap-3 border-b border-gray-50 px-5 py-3 last:border-0 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors"
                    >
                        <!-- Avatar -->
                        <div class="flex size-7 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-indigo-400 to-violet-500 text-xs font-bold text-white">
                            {{ item.user.charAt(0) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm text-gray-700 dark:text-gray-300">
                                <span class="font-semibold text-gray-900 dark:text-gray-100">{{ item.user }}</span>
                                {{ item.action }}
                                <span class="font-medium text-indigo-600 dark:text-indigo-400">{{ item.target }}</span>
                            </p>
                            <p class="mt-0.5 text-xs text-gray-400">{{ item.time }}</p>
                        </div>
                        <span class="shrink-0 rounded-full px-2 py-0.5 text-xs font-medium" :class="actionColor[item.type]">
                            {{ item.type }}
                        </span>
                    </div>
                </div>
            </Card>

            <!-- Quota summary -->
            <Card title="Quota Summary" subtitle="Overall usage at a glance" class="xl:col-span-2">
                <div class="space-y-5">
                    <div v-for="m in metrics" :key="m.label" class="flex items-center gap-4">
                        <div class="flex size-8 shrink-0 items-center justify-center rounded-lg" :class="iconColorMap[m.color]">
                            <component :is="m.icon" class="size-4" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="mb-1 flex items-center justify-between">
                                <p class="text-xs font-medium text-gray-600 dark:text-gray-400">{{ m.label }}</p>
                                <p class="text-xs font-bold" :class="barColor(pct(m.used, m.limit)).text">
                                    {{ pct(m.used, m.limit) }}%
                                </p>
                            </div>
                            <div class="h-1.5 w-full overflow-hidden rounded-full" :class="barColor(pct(m.used, m.limit)).track">
                                <div
                                    class="h-full rounded-full"
                                    :class="barColor(pct(m.used, m.limit)).fill"
                                    :style="`width: ${pct(m.used, m.limit)}%`"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Total plan info -->
                    <div class="mt-2 rounded-xl bg-gradient-to-br from-indigo-50 to-violet-50 p-4 dark:from-indigo-900/20 dark:to-violet-900/20">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Current plan</p>
                        <p class="mt-1 text-lg font-bold text-indigo-600 dark:text-indigo-400">Pro · $149/mo</p>
                        <p class="mt-1 text-xs text-gray-500">Next billing cycle Apr 1, 2026</p>
                        <div class="mt-3">
                            <a
                                href="#"
                                class="text-xs font-semibold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 underline-offset-2 hover:underline"
                            >
                                Upgrade plan &rarr;
                            </a>
                        </div>
                    </div>
                </div>
            </Card>
        </div>
    </div>
</template>
