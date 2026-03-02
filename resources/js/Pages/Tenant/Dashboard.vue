<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import Card from '@/Components/UI/Card.vue';
import { Link } from '@inertiajs/vue3';
import {
    UsersIcon,
    ServerIcon,
    CreditCardIcon,
    ClipboardDocumentListIcon,
    ArrowRightIcon,
} from '@heroicons/vue/24/outline';
import { CheckCircleIcon, ExclamationCircleIcon } from '@heroicons/vue/20/solid';

defineOptions({ layout: TenantLayout });

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({
            user_count: 0,
            user_limit: null,
            active_sessions: 0,
            storage_used_mb: 0,
            storage_limit_mb: null,
            subscription_status: 'active',
        }),
    },
    plan: { type: Object, default: () => ({}) },
    recentActivity: { type: Array, default: () => [] },
    invitationsPending: { type: Number, default: 0 },
});

const usagePct = (used, limit) => {
    if (!limit) { return 0; }
    return Math.min(100, Math.round((used / limit) * 100));
};

const usageBar = (pct) => {
    if (pct >= 90) { return 'bg-red-500'; }
    if (pct >= 70) { return 'bg-amber-400'; }
    return 'bg-indigo-500';
};

const statusConfig = {
    active:    { label: 'Active',    color: 'text-emerald-600 bg-emerald-50 dark:bg-emerald-900/20 dark:text-emerald-400' },
    trialing:  { label: 'Free Trial', color: 'text-blue-600 bg-blue-50 dark:bg-blue-900/20 dark:text-blue-400' },
    past_due:  { label: 'Past Due',  color: 'text-red-600 bg-red-50 dark:bg-red-900/20 dark:text-red-400' },
    canceled:  { label: 'Canceled',  color: 'text-gray-500 bg-gray-100 dark:bg-gray-800 dark:text-gray-400' },
};

const subConfig = (s) => statusConfig[s] ?? statusConfig.active;

const userPct    = usagePct(props.stats.user_count, props.stats.user_limit);
const storagePct = usagePct(props.stats.storage_used_mb, props.stats.storage_limit_mb);

const quickLinks = [
    { label: 'Invite a team member', href: route('tenant.users.create'),      icon: UsersIcon },
    { label: 'View billing & plan',  href: route('tenant.billing.index'),      icon: CreditCardIcon },
    { label: 'Check activity log',   href: route('tenant.activity-log.index'), icon: ClipboardDocumentListIcon },
    { label: 'Workspace settings',   href: route('tenant.settings.team'),      icon: ServerIcon },
];
</script>

<template>
    <div class="space-y-6">

        <!-- ── Welcome header ─────────────────────────────────────────── -->
        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">Dashboard</h1>
                <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                    Welcome back. Here's what's happening in your workspace.
                </p>
            </div>
            <span
                class="inline-flex w-fit items-center rounded-full px-3 py-1 text-xs font-semibold"
                :class="subConfig(stats.subscription_status).color"
            >
                {{ subConfig(stats.subscription_status).label }}
            </span>
        </div>

        <!-- ── Stat cards ─────────────────────────────────────────────── -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

            <!-- Users -->
            <Card :flat="false">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                            {{ stats.user_count }}
                            <span v-if="stats.user_limit" class="text-sm font-normal text-gray-400">
                                / {{ stats.user_limit }}
                            </span>
                        </p>
                        <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Team members</p>
                    </div>
                    <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-indigo-50 dark:bg-indigo-900/20">
                        <UsersIcon class="size-5 text-indigo-600 dark:text-indigo-400" />
                    </div>
                </div>
                <div v-if="stats.user_limit" class="mt-3">
                    <div class="mb-1 flex justify-between text-xs text-gray-400">
                        <span>{{ userPct }}% used</span>
                        <span>{{ stats.user_limit - stats.user_count }} remaining</span>
                    </div>
                    <div class="h-1.5 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                        <div
                            class="h-full rounded-full transition-all"
                            :class="usageBar(userPct)"
                            :style="`width:${userPct}%`"
                        />
                    </div>
                </div>
                <div v-if="invitationsPending > 0" class="mt-2 flex items-center gap-1 text-xs text-amber-600 dark:text-amber-400">
                    <ExclamationCircleIcon class="size-3.5 shrink-0" />
                    {{ invitationsPending }} pending invitation{{ invitationsPending > 1 ? 's' : '' }}
                </div>
            </Card>

            <!-- Storage -->
            <Card :flat="false">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                            {{ stats.storage_used_mb }} MB
                            <span v-if="stats.storage_limit_mb" class="text-sm font-normal text-gray-400">
                                / {{ stats.storage_limit_mb }} MB
                            </span>
                        </p>
                        <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Storage used</p>
                    </div>
                    <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-violet-50 dark:bg-violet-900/20">
                        <ServerIcon class="size-5 text-violet-600 dark:text-violet-400" />
                    </div>
                </div>
                <div v-if="stats.storage_limit_mb" class="mt-3">
                    <div class="mb-1 flex justify-between text-xs text-gray-400">
                        <span>{{ storagePct }}% used</span>
                        <span>{{ stats.storage_limit_mb - stats.storage_used_mb }} MB free</span>
                    </div>
                    <div class="h-1.5 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                        <div
                            class="h-full rounded-full transition-all"
                            :class="usageBar(storagePct)"
                            :style="`width:${storagePct}%`"
                        />
                    </div>
                </div>
            </Card>

            <!-- Plan -->
            <Card :flat="false">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                            {{ plan?.name ?? 'Free' }}
                        </p>
                        <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Current plan</p>
                    </div>
                    <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-emerald-50 dark:bg-emerald-900/20">
                        <CreditCardIcon class="size-5 text-emerald-600 dark:text-emerald-400" />
                    </div>
                </div>
                <div class="mt-3">
                    <Link
                        :href="route('tenant.billing.index')"
                        class="text-xs font-medium text-indigo-600 hover:underline dark:text-indigo-400"
                    >
                        Manage subscription →
                    </Link>
                </div>
            </Card>

            <!-- Sessions -->
            <Card :flat="false">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                            {{ stats.active_sessions }}
                        </p>
                        <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Active sessions</p>
                    </div>
                    <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-amber-50 dark:bg-amber-900/20">
                        <ClipboardDocumentListIcon class="size-5 text-amber-600 dark:text-amber-400" />
                    </div>
                </div>
                <div class="mt-3">
                    <Link
                        :href="route('tenant.activity-log.index')"
                        class="text-xs font-medium text-indigo-600 hover:underline dark:text-indigo-400"
                    >
                        View activity log →
                    </Link>
                </div>
            </Card>
        </div>

        <!-- ── Quick links + Activity ─────────────────────────────────── -->
        <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">

            <!-- Quick links -->
            <Card title="Quick Actions">
                <ul class="space-y-1">
                    <li v-for="link in quickLinks" :key="link.label">
                        <Link
                            :href="link.href"
                            class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-gray-700 transition-colors hover:bg-indigo-50 hover:text-indigo-700 dark:text-gray-300 dark:hover:bg-indigo-900/20 dark:hover:text-indigo-300"
                        >
                            <component :is="link.icon" class="size-4 shrink-0 text-gray-400" />
                            {{ link.label }}
                            <ArrowRightIcon class="ml-auto size-3.5 text-gray-300" />
                        </Link>
                    </li>
                </ul>
            </Card>

            <!-- Recent activity -->
            <Card title="Recent Activity" class="xl:col-span-2" :padded="false">
                <ul class="divide-y divide-gray-50 dark:divide-gray-800">
                    <li
                        v-for="(event, idx) in recentActivity"
                        :key="idx"
                        class="flex items-start gap-3 px-5 py-3"
                    >
                        <div class="mt-1.5 flex size-6 shrink-0 items-center justify-center rounded-full bg-indigo-100 dark:bg-indigo-900/30">
                            <span class="block size-2 rounded-full bg-indigo-500" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm text-gray-700 dark:text-gray-200">{{ event.description }}</p>
                            <p class="mt-0.5 text-xs text-gray-400">
                                <span v-if="event.causer_name">{{ event.causer_name }} · </span>
                                {{ event.time }}
                            </p>
                        </div>
                        <CheckCircleIcon class="mt-1 size-4 shrink-0 text-emerald-400" />
                    </li>
                    <li v-if="!recentActivity.length" class="px-5 py-10 text-center text-sm text-gray-400">
                        No recent activity.
                    </li>
                </ul>
                <div class="border-t border-gray-50 px-5 py-3 dark:border-gray-800">
                    <Link
                        :href="route('tenant.activity-log.index')"
                        class="text-xs font-medium text-indigo-600 hover:underline dark:text-indigo-400"
                    >
                        View full activity log →
                    </Link>
                </div>
            </Card>
        </div>
    </div>
</template>
