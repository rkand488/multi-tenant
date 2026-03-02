<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Card from '@/Components/UI/Card.vue';
import Table from '@/Components/UI/Table.vue';
import { Link } from '@inertiajs/vue3';
import {
    BuildingOfficeIcon,
    UserGroupIcon,
    BanknotesIcon,
    CreditCardIcon,
    ArrowTrendingUpIcon,
    ArrowTrendingDownIcon,
} from '@heroicons/vue/24/outline';
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

ChartJS.register(CategoryScale, LinearScale, BarElement, PointElement, LineElement, Title, Tooltip, Legend, Filler);

defineOptions({ layout: AdminLayout });

const props = defineProps({
    stats: { type: Object, default: () => ({}) },
    revenueChart: { type: Object, default: () => ({}) },
    signupsChart: { type: Object, default: () => ({}) },
    recentTenants: { type: Array, default: () => [] },
    recentActivity: { type: Array, default: () => [] },
});

const statCards = [
    {
        label: 'Total Tenants',
        value: props.stats.total_tenants ?? 0,
        icon: BuildingOfficeIcon,
        change: props.stats.tenants_change ?? '+0%',
        up: true,
        color: 'bg-indigo-500',
    },
    {
        label: 'Active Users',
        value: props.stats.active_users ?? 0,
        icon: UserGroupIcon,
        change: props.stats.users_change ?? '+0%',
        up: true,
        color: 'bg-emerald-500',
    },
    {
        label: 'MRR',
        value: `$${((props.stats.mrr_cents ?? 0) / 100).toLocaleString()}`,
        icon: BanknotesIcon,
        change: props.stats.mrr_change ?? '+0%',
        up: true,
        color: 'bg-violet-500',
    },
    {
        label: 'Active Plans',
        value: props.stats.active_plans ?? 0,
        icon: CreditCardIcon,
        change: props.stats.plans_change ?? '0',
        up: false,
        color: 'bg-amber-500',
    },
];

const tenantColumns = [
    { key: 'name',   label: 'Tenant' },
    { key: 'plan',   label: 'Plan' },
    { key: 'users',  label: 'Users',   class: 'w-24 text-center' },
    { key: 'status', label: 'Status',  class: 'w-28' },
    { key: 'joined', label: 'Joined',  class: 'w-36' },
    { key: 'action', label: '',        class: 'w-16' },
];

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
        x: { grid: { display: false } },
        y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
    },
};

const revenueData = {
    labels: props.revenueChart.labels ?? ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
    datasets: [{
        label: 'Revenue ($)',
        data: props.revenueChart.data ?? [1200, 1900, 1500, 2400, 2100, 2800],
        backgroundColor: 'rgba(99,102,241,0.15)',
        borderColor: 'rgb(99,102,241)',
        borderWidth: 2,
        fill: true,
        tension: 0.4,
        pointRadius: 3,
    }],
};

const signupsData = {
    labels: props.signupsChart.labels ?? ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
    datasets: [{
        label: 'New Tenants',
        data: props.signupsChart.data ?? [3, 7, 5, 9, 6, 11],
        backgroundColor: 'rgb(16,185,129)',
        borderRadius: 6,
    }],
};
</script>

<template>

    <div class="space-y-6">

        <!-- ── Stat cards ──────────────────────────────────────────────── -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div
                v-for="card in statCards"
                :key="card.label"
                class="flex items-start gap-4 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-100 dark:bg-gray-900 dark:ring-gray-800"
            >
                <div :class="`${card.color} flex size-11 shrink-0 items-center justify-center rounded-xl`">
                    <component :is="card.icon" class="size-5 text-white" />
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ card.value }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ card.label }}</p>
                    <span
                        class="mt-1 inline-flex items-center gap-1 text-xs font-medium"
                        :class="card.up ? 'text-emerald-600' : 'text-red-500'"
                    >
                        <ArrowTrendingUpIcon v-if="card.up" class="size-3" />
                        <ArrowTrendingDownIcon v-else class="size-3" />
                        {{ card.change }} vs last month
                    </span>
                </div>
            </div>
        </div>

        <!-- ── Charts row ──────────────────────────────────────────────── -->
        <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
            <Card title="Revenue (last 6 months)" :padded="false">
                <div class="px-5 pb-5 pt-2 h-52">
                    <Line :data="revenueData" :options="chartOptions" />
                </div>
            </Card>

            <Card title="New Tenant Sign-ups" :padded="false">
                <div class="px-5 pb-5 pt-2 h-52">
                    <Bar :data="signupsData" :options="chartOptions" />
                </div>
            </Card>
        </div>

        <!-- ── Recent tenants ──────────────────────────────────────────── -->
        <Card title="Recent Tenants" subtitle="Last 10 sign-ups" :padded="false">
            <template #header>
                <div class="flex items-center justify-between px-5 py-4">
                    <div>
                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">Recent Tenants</p>
                        <p class="text-xs text-gray-400">Last 10 sign-ups</p>
                    </div>
                    <Link
                        :href="route('admin.tenants.index')"
                        class="text-xs font-medium text-indigo-600 hover:underline dark:text-indigo-400"
                    >
                        View all →
                    </Link>
                </div>
            </template>

            <Table :columns="tenantColumns" :rows="recentTenants">
                <template #row="{ row }">
                    <td class="whitespace-nowrap px-4 py-3">
                        <div class="flex items-center gap-3">
                            <div class="flex size-8 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-xs font-bold text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-400">
                                {{ row.name?.[0]?.toUpperCase() }}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ row.name }}</p>
                                <p class="text-xs text-gray-400">{{ row.domain }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="whitespace-nowrap px-4 py-3">
                        <span class="inline-flex items-center rounded-full bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300">
                            {{ row.plan ?? 'Free' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center text-sm text-gray-500 dark:text-gray-400">{{ row.users ?? 0 }}</td>
                    <td class="px-4 py-3">
                        <span
                            class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium"
                            :class="row.status === 'active'
                                ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400'
                                : 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400'"
                        >
                            {{ row.status ?? 'inactive' }}
                        </span>
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-400">{{ row.joined }}</td>
                    <td class="px-4 py-3">
                        <Link
                            :href="route('admin.tenants.show', row.id)"
                            class="text-xs font-medium text-indigo-600 hover:underline dark:text-indigo-400"
                        >
                            View
                        </Link>
                    </td>
                </template>
                <template #empty>No tenants signed up yet.</template>
            </Table>
        </Card>

        <!-- ── Recent activity ─────────────────────────────────────────── -->
        <Card title="System Activity" :padded="false">
            <ul class="divide-y divide-gray-50 dark:divide-gray-800">
                <li
                    v-for="(event, idx) in recentActivity"
                    :key="idx"
                    class="flex items-start gap-3 px-5 py-3"
                >
                    <span class="mt-1.5 block size-2 shrink-0 rounded-full bg-indigo-400" />
                    <p class="text-sm text-gray-700 dark:text-gray-300">
                        {{ event.description }}
                        <span class="ml-2 text-xs text-gray-400">{{ event.time }}</span>
                    </p>
                </li>
                <li v-if="!recentActivity.length" class="px-5 py-8 text-center text-sm text-gray-400">
                    No recent system activity.
                </li>
            </ul>
        </Card>
    </div>
</template>
