<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import Card from '@/Components/UI/Card.vue';
import Table from '@/Components/UI/Table.vue';
import Pagination from '@/Components/UI/Pagination.vue';
import Input from '@/Components/UI/Input.vue';
import Select from '@/Components/UI/Select.vue';
import { router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { useDebounceFn } from '@vueuse/core';
import { ClipboardDocumentCheckIcon, MagnifyingGlassIcon } from '@heroicons/vue/24/outline';

defineOptions({ layout: TenantLayout });

const props = defineProps({
    logs:        { type: Object, default: () => ({ data: [], meta: {} }) },
    filters:     { type: Object, default: () => ({}) },
    eventTypes:  { type: Array,  default: () => [] },
    teamMembers: { type: Array,  default: () => [] },
});

const search   = ref(props.filters.search   ?? '');
const event    = ref(props.filters.event    ?? '');
const userId   = ref(props.filters.user_id  ?? '');
const dateFrom = ref(props.filters.date_from ?? '');
const dateTo   = ref(props.filters.date_to   ?? '');

const applyFilters = (page = 1) => {
    router.get(
        route('tenant.audit-logs.index'),
        {
            search:    search.value    || undefined,
            event:     event.value     || undefined,
            user_id:   userId.value    || undefined,
            date_from: dateFrom.value  || undefined,
            date_to:   dateTo.value    || undefined,
            page,
        },
        { preserveState: true, replace: true },
    );
};

const debouncedSearch = useDebounceFn(() => applyFilters(), 350);
watch(search, debouncedSearch);
watch([event, userId, dateFrom, dateTo], () => applyFilters());

const handlePageChange = (page) => applyFilters(page);

const eventTypeOptions = props.eventTypes.map((e) => ({ value: e, label: e }));
const memberOptions    = props.teamMembers.map((m) => ({ value: m.id, label: m.name }));

const eventBadgeClass = (ev) => ({
    created:  'bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400',
    updated:  'bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400',
    deleted:  'bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400',
    restored: 'bg-amber-50 text-amber-700 dark:bg-amber-900/20 dark:text-amber-400',
}[ev] ?? 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400');

// Diff viewer state
const showDiffModal = ref(false);
const selectedLog   = ref(null);

const openDiff = (log) => {
    selectedLog.value = log;
    showDiffModal.value = true;
};

const columns = [
    { key: 'event',   label: 'Event',   class: 'w-28' },
    { key: 'model',   label: 'Model' },
    { key: 'actor',   label: 'By',      class: 'w-40' },
    { key: 'date',    label: 'Date',    class: 'w-36' },
    { key: 'actions', label: '',        class: 'w-16 text-right' },
];
</script>

<template>
    <div class="space-y-5">

        <!-- Header -->
        <div>
            <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Audit Log</h1>
            <p class="mt-1 text-sm text-gray-500">Immutable trail of all model-level changes.</p>
        </div>

        <!-- Filters -->
        <Card>
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <div class="relative">
                    <MagnifyingGlassIcon class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-gray-400" />
                    <Input v-model="search" placeholder="Search model…" class="pl-9" />
                </div>
                <Select v-model="event" :options="[{ value: '', label: 'All events' }, ...eventTypeOptions]" />
                <Select v-model="userId" :options="[{ value: '', label: 'All members' }, ...memberOptions]" />
                <div class="flex gap-2">
                    <Input v-model="dateFrom" type="date" placeholder="From" />
                    <Input v-model="dateTo"   type="date" placeholder="To" />
                </div>
            </div>
        </Card>

        <!-- Table -->
        <Card>
            <Table :columns="columns" :rows="logs.data">
                <template #cell-event="{ row }">
                    <span :class="[eventBadgeClass(row.event), 'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium capitalize']">
                        {{ row.event }}
                    </span>
                </template>
                <template #cell-model="{ row }">
                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                        {{ row.auditable_type?.split('\\').pop() }}
                    </span>
                    <span class="ml-1.5 font-mono text-xs text-gray-400">#{{ row.auditable_id }}</span>
                </template>
                <template #cell-actor="{ row }">
                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ row.user?.name ?? '—' }}</span>
                </template>
                <template #cell-date="{ row }">
                    <span class="text-sm text-gray-500">{{ new Date(row.created_at).toLocaleString() }}</span>
                </template>
                <template #cell-actions="{ row }">
                    <button
                        v-if="row.old_values || row.new_values"
                        class="rounded px-2 py-1 text-xs text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/20"
                        @click="openDiff(row)"
                    >
                        Diff
                    </button>
                </template>
                <template #empty>
                    <div class="py-12 text-center">
                        <ClipboardDocumentCheckIcon class="mx-auto size-10 text-gray-300 dark:text-gray-600" />
                        <p class="mt-3 text-sm text-gray-500">No audit log entries found.</p>
                    </div>
                </template>
            </Table>

            <div v-if="logs.meta?.last_page > 1" class="mt-4">
                <Pagination :meta="logs.meta" @change="handlePageChange" />
            </div>
        </Card>

        <!-- Diff modal -->
        <Teleport to="body">
            <Transition name="fade">
                <div
                    v-if="showDiffModal"
                    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
                    @click.self="showDiffModal = false"
                >
                    <div class="w-full max-w-2xl rounded-xl bg-white shadow-xl dark:bg-gray-900">
                        <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 capitalize">
                                {{ selectedLog?.event }} — {{ selectedLog?.auditable_type?.split('\\').pop() }}
                            </h3>
                            <button class="text-gray-400 hover:text-gray-600" @click="showDiffModal = false">✕</button>
                        </div>
                        <div class="grid grid-cols-2 gap-4 p-6">
                            <div>
                                <p class="mb-2 text-xs font-medium uppercase text-gray-500">Before</p>
                                <pre class="overflow-auto rounded-lg bg-gray-50 p-3 text-xs text-gray-700 dark:bg-gray-800 dark:text-gray-300">{{ JSON.stringify(selectedLog?.old_values, null, 2) ?? 'N/A' }}</pre>
                            </div>
                            <div>
                                <p class="mb-2 text-xs font-medium uppercase text-gray-500">After</p>
                                <pre class="overflow-auto rounded-lg bg-gray-50 p-3 text-xs text-gray-700 dark:bg-gray-800 dark:text-gray-300">{{ JSON.stringify(selectedLog?.new_values, null, 2) ?? 'N/A' }}</pre>
                            </div>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

    </div>
</template>
