<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import {
    UsersIcon,
    CreditCardIcon,
    ClipboardDocumentListIcon,
    CheckCircleIcon,
} from '@heroicons/vue/24/outline';
import { Link } from '@inertiajs/vue3';

defineOptions({ layout: TenantLayout });

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({
            user_count: 0,
            active_sessions: 0,
            storage_used_mb: 0,
            subscription_status: 'active',
        }),
    },
    recentActivity: {
        type: Array,
        default: () => [],
    },
});

const statCards = [
    {
        label: 'Team Members',
        value: props.stats.user_count,
        icon: UsersIcon,
        href: route('tenant.users.index'),
        color: 'text-indigo-600 bg-indigo-50 dark:bg-indigo-900/30 dark:text-indigo-400',
    },
    {
        label: 'Active Sessions',
        value: props.stats.active_sessions,
        icon: CheckCircleIcon,
        href: null,
        color: 'text-green-600 bg-green-50 dark:bg-green-900/30 dark:text-green-400',
    },
    {
        label: 'Storage Used',
        value: `${props.stats.storage_used_mb} MB`,
        icon: ClipboardDocumentListIcon,
        href: null,
        color: 'text-yellow-600 bg-yellow-50 dark:bg-yellow-900/30 dark:text-yellow-400',
    },
    {
        label: 'Subscription',
        value: props.stats.subscription_status,
        icon: CreditCardIcon,
        href: route('tenant.subscription.index'),
        color: 'text-blue-600 bg-blue-50 dark:bg-blue-900/30 dark:text-blue-400',
    },
];

const formatDate = (dateString) => {
    if (!dateString) { return '—'; }
    return new Intl.DateTimeFormat('en-US', {
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).format(new Date(dateString));
};
</script>

<template>
    <div class="space-y-6">

        <!-- Page header -->
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Dashboard</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Welcome back — here's what's happening in your workspace.
            </p>
        </div>

        <!-- Stats grid -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
            <div
                v-for="card in statCards"
                :key="card.label"
                class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-gray-700"
            >
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ card.label }}</p>
                    <div class="rounded-lg p-2" :class="card.color">
                        <component :is="card.icon" class="size-5" />
                    </div>
                </div>
                <p class="mt-3 text-3xl font-bold capitalize text-gray-900 dark:text-white">
                    {{ card.value }}
                </p>
                <div v-if="card.href" class="mt-2">
                    <Link :href="card.href" class="text-xs font-medium text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">
                        View all →
                    </Link>
                </div>
            </div>
        </div>

        <!-- Recent activity -->
        <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-gray-700">
            <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">Recent Activity</h2>
                <Link
                    :href="route('tenant.activity-log.index')"
                    class="text-sm font-medium text-indigo-600 hover:text-indigo-700 dark:text-indigo-400"
                >
                    View all
                </Link>
            </div>

            <!-- Empty state -->
            <template v-if="recentActivity.length === 0">
                <div class="flex flex-col items-center justify-center py-16 text-center">
                    <ClipboardDocumentListIcon class="size-10 text-gray-300 dark:text-gray-600" />
                    <p class="mt-3 text-sm font-medium text-gray-500 dark:text-gray-400">No activity yet</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500">Actions taken in your workspace will appear here.</p>
                </div>
            </template>

            <!-- Activity list -->
            <ul v-else class="divide-y divide-gray-100 dark:divide-gray-700">
                <li
                    v-for="item in recentActivity"
                    :key="item.id"
                    class="flex items-start gap-4 px-6 py-4"
                >
                    <div class="flex size-8 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-sm font-semibold text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">
                        {{ item.causer?.name?.[0]?.toUpperCase() ?? '?' }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm text-gray-800 dark:text-gray-200">
                            <span class="font-medium">{{ item.causer?.name ?? 'System' }}</span>
                            {{ item.description }}
                        </p>
                        <p class="mt-0.5 text-xs text-gray-400">{{ formatDate(item.created_at) }}</p>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</template>
