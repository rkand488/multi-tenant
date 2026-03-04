<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Card from '@/Components/UI/Card.vue';
import Table from '@/Components/UI/Table.vue';
import Pagination from '@/Components/UI/Pagination.vue';
import Dropdown from '@/Components/UI/Dropdown.vue';
import DropdownItem from '@/Components/UI/DropdownItem.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { MagnifyingGlassIcon, FunnelIcon } from '@heroicons/vue/24/outline';
import { useDebounceFn } from '@vueuse/core';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    subscriptions: { type: Object, required: true }, // paginator
    filters: { type: Object, default: () => ({}) },
    summary: { type: Object, default: () => ({}) },
});

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');
const plan   = ref(props.filters.plan   ?? '');

const applyFilters = () => {
    router.get(route('admin.subscriptions.index'), {
        search: search.value,
        status: status.value,
        plan: plan.value,
    }, { preserveState: true, replace: true });
};

const debouncedSearch = useDebounceFn(applyFilters, 350);

watch(search, debouncedSearch);
watch([status, plan], applyFilters);

const handlePageChange = (page) => {
    router.get(route('admin.subscriptions.index'), { ...props.filters, page }, { preserveState: true });
};

const cancelSubscription = (id, tenantName) => {
    if (confirm(`Cancel the subscription for "${tenantName}"?\n\nThe tenant will retain access until the end of their current billing period.`)) {
        router.patch(route('admin.subscriptions.cancel', id), {}, {
            preserveScroll: true,
            onSuccess: () => {},
        });
    }
};

const columns = [
    { key: 'tenant',     label: 'Tenant' },
    { key: 'plan',       label: 'Plan',       class: 'w-32' },
    { key: 'interval',   label: 'Billing',    class: 'w-28' },
    { key: 'amount',     label: 'Amount',     class: 'w-28 text-right' },
    { key: 'status',     label: 'Status',     class: 'w-28' },
    { key: 'trial_ends', label: 'Trial ends', class: 'w-36' },
    { key: 'renews_at',  label: 'Renews',     class: 'w-36' },
    { key: 'actions',    label: '',           class: 'w-16 text-right' },
];

const statusClass = (s) => ({
    active:    'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
    trialing:  'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
    cancelled: 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400',
    suspended: 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400',
    past_due:  'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-400',
}[s] ?? 'bg-gray-100 text-gray-500');
</script>

<template>

    <div class="space-y-5">

        <!-- ── Summary cards ───────────────────────────────────────────── -->
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
            <div
                v-for="(item, label) in {
                    'Active': summary.active,
                    'Trialing': summary.trialing,
                    'Past Due': summary.past_due,
                    'Canceled (30d)': summary.canceled_30d,
                }"
                :key="label"
                class="rounded-2xl bg-white p-4 ring-1 ring-gray-100 shadow-sm dark:bg-gray-900 dark:ring-gray-800"
            >
                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ item ?? 0 }}</p>
                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ label }}</p>
            </div>
        </div>

        <!-- ── Toolbar ─────────────────────────────────────────────────── -->
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="relative flex-1 max-w-xs">
                <MagnifyingGlassIcon class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-gray-400" />
                <input
                    v-model="search"
                    type="search"
                    placeholder="Search tenant or email…"
                    class="h-9 w-full rounded-lg border border-gray-200 bg-white pl-9 pr-3 text-sm placeholder-gray-400 shadow-sm focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500"
                />
            </div>

            <div class="relative">
                <FunnelIcon class="pointer-events-none absolute left-2.5 top-1/2 size-4 -translate-y-1/2 text-gray-400" />
                <select
                    v-model="status"
                    class="h-9 rounded-lg border border-gray-200 bg-white pl-8 pr-3 text-sm shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                >
                    <option value="">All Statuses</option>
                    <option value="active">Active</option>
                    <option value="trialing">Trialing</option>
                    <option value="past_due">Past Due</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
        </div>

        <!-- ── Table ───────────────────────────────────────────────────── -->
        <Card :padded="false">
            <Table :columns="columns" :rows="subscriptions.data ?? []">
                <template #row="{ row }">
                    <!-- Tenant -->
                    <td class="whitespace-nowrap px-4 py-3">
                        <Link
                            :href="route('admin.tenants.show', row.tenant_id)"
                            class="text-sm font-medium text-gray-900 hover:underline dark:text-gray-100"
                        >
                            {{ row.tenant_name }}
                        </Link>
                        <p class="text-xs text-gray-400">{{ row.tenant_domain }}</p>
                    </td>

                    <!-- Plan -->
                    <td class="whitespace-nowrap px-4 py-3">
                        <span class="inline-flex items-center rounded-full bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300">
                            {{ row.plan_name ?? '—' }}
                        </span>
                    </td>

                    <!-- Billing interval -->
                    <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 capitalize">{{ row.billing_interval ?? '—' }}</td>

                    <!-- Amount -->
                    <td class="px-4 py-3 text-right text-sm font-medium text-gray-800 dark:text-gray-200">
                        ${{ ((row.amount_cents ?? 0) / 100).toFixed(2) }}
                    </td>

                    <!-- Status -->
                    <td class="whitespace-nowrap px-4 py-3">
                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium" :class="statusClass(row.status)">
                            {{ row.status }}
                        </span>
                    </td>

                    <!-- Trial end -->
                    <td class="px-4 py-3 text-sm text-gray-400">{{ row.trial_ends_at ?? '—' }}</td>

                    <!-- Renews -->
                    <td class="px-4 py-3 text-sm text-gray-400">{{ row.renews_at ?? '—' }}</td>

                    <!-- Actions -->
                    <td class="px-4 py-3 text-right">
                        <Dropdown align="right" width="40">
                            <template #trigger>
                                <button
                                    type="button"
                                    class="inline-flex items-center rounded px-2 py-0.5 text-xs text-gray-500 ring-1 ring-inset ring-gray-200 hover:bg-gray-50 dark:ring-gray-700 dark:hover:bg-gray-800"
                                >
                                    ⋯
                                </button>
                            </template>
                            <template #items>
                                <DropdownItem @click="router.visit(route('admin.tenants.show', row.tenant_id))">
                                    View tenant
                                </DropdownItem>
                                <DropdownItem
                                    v-if="row.status === 'active' || row.status === 'trialing'"
                                    variant="danger"
                                    @click="cancelSubscription(row.id, row.tenant_name)"
                                >
                                    Cancel subscription
                                </DropdownItem>
                            </template>
                        </Dropdown>
                    </td>
                </template>
                <template #empty>No subscriptions match your filters.</template>
            </Table>
        </Card>

        <!-- ── Pagination ──────────────────────────────────────────────── -->
        <Pagination
            v-if="subscriptions.meta && subscriptions.meta.last_page > 1"
            :links="subscriptions.links ?? []"
            :meta="subscriptions.meta"
            @change="handlePageChange"
        />
    </div>
</template>
