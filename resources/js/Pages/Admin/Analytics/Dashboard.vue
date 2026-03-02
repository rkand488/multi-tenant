<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Card from '@/Components/UI/Card.vue';
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { Bar, Line, Doughnut } from 'vue-chartjs';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    BarElement,
    PointElement,
    LineElement,
    ArcElement,
    Title,
    Tooltip,
    Legend,
    Filler,
} from 'chart.js';
import {
    BuildingOfficeIcon,
    UserGroupIcon,
    BanknotesIcon,
    ArrowTrendingUpIcon,
    ArrowTrendingDownIcon,
} from '@heroicons/vue/24/outline';

ChartJS.register(
    CategoryScale,
    LinearScale,
    BarElement,
    PointElement,
    LineElement,
    ArcElement,
    Title,
    Tooltip,
    Legend,
    Filler,
);

defineOptions({ layout: AdminLayout });

const props = defineProps({
    kpis: { type: Object, default: () => ({}) },
    revenueByMonth:    { type: Object, default: () => ({}) },
    tenantsByMonth:    { type: Object, default: () => ({}) },
    planDistribution:  { type: Object, default: () => ({}) },
    churnByMonth:      { type: Object, default: () => ({}) },
    topTenants:        { type: Array,  default: () => [] },
});

// ── KPI cards ──────────────────────────────────────────────────────────────
const kpiCards = computed(() => [
    {
        label: 'Total Tenants',
        value: props.kpis.total_tenants ?? 0,
        change: props.kpis.tenants_growth ?? '+0%',
        up: true,
        icon: BuildingOfficeIcon,
        color: 'bg-indigo-500',
    },
    {
        label: 'Total Users',
        value: props.kpis.total_users ?? 0,
        change: props.kpis.users_growth ?? '+0%',
        up: true,
        icon: UserGroupIcon,
        color: 'bg-emerald-500',
    },
    {
        label: 'MRR',
        value: `$${((props.kpis.mrr_cents ?? 0) / 100).toLocaleString()}`,
        change: props.kpis.mrr_growth ?? '+0%',
        up: (props.kpis.mrr_growth ?? '+0%').startsWith('+'),
        icon: BanknotesIcon,
        color: 'bg-violet-500',
    },
    {
        label: 'Churn Rate',
        value: `${props.kpis.churn_rate ?? 0}%`,
        change: props.kpis.churn_change ?? '0%',
        up: false,
        icon: ArrowTrendingDownIcon,
        color: 'bg-rose-500',
    },
]);

// ── Chart options ──────────────────────────────────────────────────────────
const lineOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
        x: { grid: { display: false } },
        y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.04)' } },
    },
};

const barOptions = { ...lineOptions };

const doughnutOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { position: 'right', labels: { boxWidth: 12, font: { size: 12 } } },
    },
    cutout: '65%',
};

// ── Chart datasets ─────────────────────────────────────────────────────────
const revenueData = computed(() => ({
    labels: props.revenueByMonth.labels ?? ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
    datasets: [{
        label: 'MRR',
        data:  props.revenueByMonth.data  ?? [5000, 7200, 6800, 9100, 8400, 11200],
        borderColor: 'rgb(99,102,241)',
        backgroundColor: 'rgba(99,102,241,0.1)',
        fill: true,
        tension: 0.4,
        borderWidth: 2,
        pointRadius: 3,
    }],
}));

const tenantsData = computed(() => ({
    labels:   props.tenantsByMonth.labels ?? ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
    datasets: [{
        label: 'New Tenants',
        data:   props.tenantsByMonth.data  ?? [4, 8, 6, 11, 9, 14],
        backgroundColor: 'rgb(16,185,129)',
        borderRadius: 6,
    }],
}));

const planColors = ['#6366f1', '#8b5cf6', '#06b6d4', '#10b981', '#f59e0b'];

const planData = computed(() => ({
    labels:   props.planDistribution.labels ?? ['Starter', 'Growth', 'Pro', 'Enterprise'],
    datasets: [{
        data:            props.planDistribution.data   ?? [42, 28, 17, 13],
        backgroundColor: planColors,
        borderWidth: 0,
    }],
}));

const churnData = computed(() => ({
    labels:   props.churnByMonth.labels ?? ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
    datasets: [{
        label: 'Churn %',
        data:   props.churnByMonth.data  ?? [2.1, 1.8, 3.2, 2.4, 1.9, 2.6],
        borderColor: 'rgb(244, 63, 94)',
        backgroundColor: 'rgba(244,63,94,0.08)',
        fill: true,
        tension: 0.4,
        borderWidth: 2,
        pointRadius: 3,
    }],
}));

// ── Time range filter ──────────────────────────────────────────────────────
const range  = ref('6m');
const ranges = [
    { label: '30d', value: '30d' },
    { label: '3m',  value: '3m'  },
    { label: '6m',  value: '6m'  },
    { label: '1y',  value: '1y'  },
];

const switchRange = (r) => {
    range.value = r;
    router.get(route('admin.analytics.dashboard'), { range: r }, { preserveState: true, replace: true });
};
</script>

<template>

    <div class="space-y-6">

        <!-- ── Date range tabs ─────────────────────────────────────────── -->
        <div class="flex items-center gap-1 rounded-lg bg-gray-100 p-0.5 self-start dark:bg-gray-800 w-fit">
            <button
                v-for="r in ranges"
                :key="r.value"
                type="button"
                class="rounded-md px-3 py-1 text-xs font-medium transition-colors"
                :class="range === r.value
                    ? 'bg-white text-gray-900 shadow-sm dark:bg-gray-700 dark:text-gray-100'
                    : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
                @click="switchRange(r.value)"
            >
                {{ r.label }}
            </button>
        </div>

        <!-- ── KPI cards ───────────────────────────────────────────────── -->
        <div class="grid grid-cols-2 gap-4 xl:grid-cols-4">
            <div
                v-for="card in kpiCards"
                :key="card.label"
                class="flex items-start gap-4 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-100 dark:bg-gray-900 dark:ring-gray-800"
            >
                <div :class="`${card.color} flex size-11 shrink-0 items-center justify-center rounded-xl`">
                    <component :is="card.icon" class="size-5 text-white" />
                </div>
                <div>
                    <p class="text-2xl font-bold tracking-tight text-gray-900 dark:text-gray-100">{{ card.value }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ card.label }}</p>
                    <span
                        class="mt-1 inline-flex items-center gap-1 text-xs font-medium"
                        :class="card.up ? 'text-emerald-600' : 'text-rose-500'"
                    >
                        <ArrowTrendingUpIcon v-if="card.up" class="size-3" />
                        <ArrowTrendingDownIcon v-else class="size-3" />
                        {{ card.change }}
                    </span>
                </div>
            </div>
        </div>

        <!-- ── Revenue + Churn ─────────────────────────────────────────── -->
        <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
            <Card title="Monthly Recurring Revenue" :padded="false">
                <div class="h-60 px-5 pb-5 pt-2">
                    <Line :data="revenueData" :options="lineOptions" />
                </div>
            </Card>

            <Card title="Churn Rate (%)" :padded="false">
                <div class="h-60 px-5 pb-5 pt-2">
                    <Line :data="churnData" :options="lineOptions" />
                </div>
            </Card>
        </div>

        <!-- ── Tenants + Plan distribution ────────────────────────────── -->
        <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
            <Card title="New Tenants per Month" :padded="false" class="xl:col-span-2">
                <div class="h-60 px-5 pb-5 pt-2">
                    <Bar :data="tenantsData" :options="barOptions" />
                </div>
            </Card>

            <Card title="Plan Distribution" :padded="false">
                <div class="flex h-60 items-center justify-center px-4 pb-5 pt-2">
                    <Doughnut :data="planData" :options="doughnutOptions" />
                </div>
            </Card>
        </div>

        <!-- ── Top tenants by usage ────────────────────────────────────── -->
        <Card title="Top Tenants by Storage">
            <div class="space-y-3">
                <div
                    v-for="(tenant, idx) in topTenants"
                    :key="tenant.id ?? idx"
                    class="flex items-center gap-3"
                >
                    <span class="w-5 shrink-0 text-right text-xs font-bold text-gray-400">{{ idx + 1 }}</span>
                    <div class="flex size-7 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-xs font-bold text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400">
                        {{ tenant.name?.[0]?.toUpperCase() }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center justify-between">
                            <p class="truncate text-sm font-medium text-gray-800 dark:text-gray-200">{{ tenant.name }}</p>
                            <p class="ml-3 shrink-0 text-xs text-gray-500 dark:text-gray-400">{{ tenant.storage_mb }} MB</p>
                        </div>
                        <div class="mt-1 h-1.5 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                            <div
                                class="h-full rounded-full bg-indigo-500 transition-all duration-500"
                                :style="`width:${tenant.storage_pct ?? 0}%`"
                            />
                        </div>
                    </div>
                </div>
                <p v-if="!topTenants.length" class="text-center text-sm text-gray-400">No data yet.</p>
            </div>
        </Card>
    </div>
</template>
