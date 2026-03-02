<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import Card from '@/Components/UI/Card.vue';
import Button from '@/Components/UI/Button.vue';
import Alert from '@/Components/UI/Alert.vue';
import Modal from '@/Components/UI/Modal.vue';
import { useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import {
    CreditCardIcon,
    CheckCircleIcon,
    XCircleIcon,
    ArrowUpCircleIcon,
    DocumentArrowDownIcon,
    CheckIcon,
} from '@heroicons/vue/24/outline';

defineOptions({ layout: TenantLayout });

const props = defineProps({
    subscription: { type: Object, default: null },
    plan:         { type: Object, default: null },
    plans:        { type: Array,  default: () => [] },
    invoices:     { type: Array,  default: () => [] },
    usage:        { type: Object, default: () => ({}) },
});

// ── Cancel modal ─────────────────────────────────────────────────────────────
const showCancelModal = ref(false);
const cancelForm      = useForm({});

const confirmCancel = () => {
    cancelForm.post(route('tenant.billing.cancel'), {
        onSuccess: () => { showCancelModal.value = false; },
    });
};

// ── Upgrade form ─────────────────────────────────────────────────────────────
const upgradeForm = useForm({ plan_id: '' });

const upgradeTo = (planId) => {
    upgradeForm.plan_id = planId;
    upgradeForm.post(route('tenant.billing.upgrade'));
};

// ── Helpers ──────────────────────────────────────────────────────────────────
const usagePercent = (used, limit) => {
    if (!limit) { return 0; }
    return Math.min(100, Math.round((used / limit) * 100));
};

const usageColor = (pct) =>
    pct >= 90 ? 'bg-red-500' : pct >= 70 ? 'bg-amber-500' : 'bg-indigo-500';

const statusStyles = {
    active:    'bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400',
    trialing:  'bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400',
    canceled:  'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
    past_due:  'bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400',
    inactive:  'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
};

const subStatus  = computed(() => props.subscription?.status ?? 'inactive');
const statusLabel = computed(() => ({
    active: 'Active', trialing: 'Trialing', canceled: 'Canceled', past_due: 'Past Due', inactive: 'Inactive',
})[subStatus.value] ?? subStatus.value);

const userUsage    = computed(() => props.usage?.users    ?? { used: 0, limit: 0 });
const storageUsage = computed(() => props.usage?.storage  ?? { used: 0, limit: 0 });
</script>

<template>
    <div class="space-y-6">

        <!-- ── Page header ───────────────────────────────────────────── -->
        <div>
            <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Subscription & Billing</h1>
            <p class="mt-1 text-sm text-gray-500">Manage your plan, usage, and invoices.</p>
        </div>

        <!-- ── Current plan card ─────────────────────────────────────── -->
        <Card>
            <template #header>
                <div class="flex items-center gap-3 px-5 py-4">
                    <div class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-indigo-50 dark:bg-indigo-900/20">
                        <CreditCardIcon class="size-5 text-indigo-600 dark:text-indigo-400" />
                    </div>
                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">Current Plan</p>
                </div>
            </template>

            <div v-if="plan" class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3">
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ plan.name }}</p>
                        <span
                            class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold"
                            :class="statusStyles[subStatus] ?? statusStyles.inactive"
                        >
                            {{ statusLabel }}
                        </span>
                    </div>
                    <p class="mt-1 text-sm text-gray-500">
                        <template v-if="subscription?.renews_at">
                            Renews {{ subscription.renews_at }}
                        </template>
                        <template v-else-if="subscription?.ends_at">
                            Ends {{ subscription.ends_at }}
                        </template>
                        <template v-else>No active subscription period</template>
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <Button
                        v-if="subStatus === 'active'"
                        variant="secondary"
                        @click="showCancelModal = true"
                    >
                        Cancel Plan
                    </Button>
                    <Button v-if="subStatus !== 'active'">
                        <ArrowUpCircleIcon class="mr-1.5 size-4" />
                        Upgrade
                    </Button>
                </div>
            </div>

            <Alert v-else variant="info">
                You don't have an active subscription.
                <button class="font-medium underline" @click="() => {}">Browse plans</button>
                to get started.
            </Alert>

            <!-- Usage meters -->
            <div v-if="plan" class="mt-5 grid gap-4 sm:grid-cols-2">
                <!-- Users -->
                <div class="rounded-xl border border-gray-100 p-4 dark:border-gray-800">
                    <div class="mb-2 flex items-center justify-between text-xs">
                        <span class="font-medium text-gray-700 dark:text-gray-300">Team Members</span>
                        <span class="text-gray-400">{{ userUsage.used }} / {{ userUsage.limit ?? '∞' }}</span>
                    </div>
                    <div class="h-1.5 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                        <div
                            class="h-full rounded-full transition-all"
                            :class="usageColor(usagePercent(userUsage.used, userUsage.limit))"
                            :style="{ width: usagePercent(userUsage.used, userUsage.limit) + '%' }"
                        />
                    </div>
                </div>
                <!-- Storage -->
                <div class="rounded-xl border border-gray-100 p-4 dark:border-gray-800">
                    <div class="mb-2 flex items-center justify-between text-xs">
                        <span class="font-medium text-gray-700 dark:text-gray-300">Storage</span>
                        <span class="text-gray-400">{{ storageUsage.used ?? 0 }} MB / {{ storageUsage.limit ?? '∞' }} MB</span>
                    </div>
                    <div class="h-1.5 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                        <div
                            class="h-full rounded-full transition-all"
                            :class="usageColor(usagePercent(storageUsage.used, storageUsage.limit))"
                            :style="{ width: usagePercent(storageUsage.used, storageUsage.limit) + '%' }"
                        />
                    </div>
                </div>
            </div>
        </Card>

        <!-- ── Available plans ───────────────────────────────────────── -->
        <div v-if="plans.length">
            <h2 class="mb-3 text-sm font-semibold text-gray-700 dark:text-gray-300">Available Plans</h2>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="p in plans"
                    :key="p.id"
                    class="relative flex flex-col rounded-2xl border p-5 transition"
                    :class="plan?.id === p.id
                        ? 'border-indigo-400 bg-indigo-50/40 dark:border-indigo-600 dark:bg-indigo-900/10'
                        : 'border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800/50'"
                >
                    <div
                        v-if="p.is_popular"
                        class="absolute -top-3 left-1/2 -translate-x-1/2 rounded-full bg-indigo-600 px-3 py-0.5 text-xs font-semibold text-white"
                    >
                        Popular
                    </div>
                    <p class="font-bold text-gray-900 dark:text-gray-100">{{ p.name }}</p>
                    <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-gray-100">
                        ${{ p.price }}
                        <span class="text-sm font-normal text-gray-400">/ {{ p.interval ?? 'mo' }}</span>
                    </p>
                    <ul class="mt-4 grow space-y-1.5 text-sm text-gray-600 dark:text-gray-400">
                        <li
                            v-for="feature in (p.features ?? [])"
                            :key="feature"
                            class="flex items-center gap-2"
                        >
                            <CheckIcon class="size-4 shrink-0 text-indigo-500" />
                            {{ feature }}
                        </li>
                    </ul>
                    <Button
                        class="mt-5 w-full"
                        :variant="plan?.id === p.id ? 'secondary' : 'primary'"
                        :disabled="plan?.id === p.id || upgradeForm.processing"
                        @click="upgradeTo(p.id)"
                    >
                        {{ plan?.id === p.id ? 'Current Plan' : 'Upgrade' }}
                    </Button>
                </div>
            </div>
        </div>

        <!-- ── Invoices ──────────────────────────────────────────────── -->
        <Card v-if="invoices.length">
            <template #header>
                <p class="px-5 py-4 text-sm font-semibold text-gray-900 dark:text-gray-100">Billing History</p>
            </template>

            <div class="divide-y divide-gray-50 dark:divide-gray-800">
                <div
                    v-for="inv in invoices"
                    :key="inv.id"
                    class="flex items-center justify-between py-3 text-sm"
                >
                    <div>
                        <p class="font-medium text-gray-900 dark:text-gray-100">{{ inv.number ?? inv.id }}</p>
                        <p class="text-xs text-gray-400">{{ inv.date }}</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <span
                            class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium"
                            :class="inv.status === 'paid'
                                ? 'bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400'
                                : 'bg-amber-50 text-amber-700 dark:bg-amber-900/20 dark:text-amber-400'"
                        >
                            <CheckCircleIcon v-if="inv.status === 'paid'" class="size-3" />
                            <XCircleIcon v-else class="size-3" />
                            {{ inv.status }}
                        </span>
                        <span class="font-semibold text-gray-900 dark:text-gray-100">${{ inv.amount }}</span>
                        <a
                            v-if="inv.pdf_url"
                            :href="inv.pdf_url"
                            target="_blank"
                            class="text-indigo-500 transition hover:text-indigo-700"
                        >
                            <DocumentArrowDownIcon class="size-4" />
                        </a>
                    </div>
                </div>
            </div>
        </Card>
    </div>

    <!-- ── Cancel modal ─────────────────────────────────────────────────── -->
    <Modal :show="showCancelModal" @close="showCancelModal = false">
        <template #title>Cancel Subscription</template>

        <div class="space-y-3">
            <Alert variant="warning">
                Your plan will remain active until the end of the current billing period.
            </Alert>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Are you sure you want to cancel? You'll lose access to paid features when your subscription ends.
            </p>
        </div>

        <template #footer>
            <div class="flex justify-end gap-3">
                <Button variant="secondary" @click="showCancelModal = false">Keep Subscription</Button>
                <Button variant="danger" :loading="cancelForm.processing" @click="confirmCancel">
                    Yes, Cancel
                </Button>
            </div>
        </template>
    </Modal>
</template>
