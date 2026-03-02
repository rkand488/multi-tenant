<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import Card from '@/Components/UI/Card.vue';
import Input from '@/Components/UI/Input.vue';
import Select from '@/Components/UI/Select.vue';
import Pagination from '@/Components/UI/Pagination.vue';
import { router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { useDebounceFn } from '@vueuse/core';
import { ClipboardDocumentListIcon, MagnifyingGlassIcon } from '@heroicons/vue/24/outline';

defineOptions({ layout: TenantLayout });

const props = defineProps({
    logs:         { type: Object, default: () => ({ data: [], links: [], meta: {} }) },
    filters:      { type: Object, default: () => ({}) },
    eventTypes:   { type: Array,  default: () => [] },
    teamMembers:  { type: Array,  default: () => [] },
});

// ── Filter state ─────────────────────────────────────────────────────────────
const search    = ref(props.filters.search    ?? '');
const eventType = ref(props.filters.event     ?? '');
const causerId  = ref(props.filters.causer_id ?? '');
const dateFrom  = ref(props.filters.date_from ?? '');
const dateTo    = ref(props.filters.date_to   ?? '');

const applyFilters = (page = 1) => {
    router.get(
        route('tenant.activity-log.index'),
        {
            search:    search.value    || undefined,
            event:     eventType.value || undefined,
            causer_id: causerId.value  || undefined,
            date_from: dateFrom.value  || undefined,
            date_to:   dateTo.value    || undefined,
            page,
        },
        { preserveState: true, replace: true },
    );
};

const debouncedSearch = useDebounceFn(() => applyFilters(), 350);

watch(search, debouncedSearch);
watch([eventType, causerId, dateFrom, dateTo], () => applyFilters());

const handlePageChange = (page) => applyFilters(page);

// ── Options ──────────────────────────────────────────────────────────────────
const eventTypeOptions = props.eventTypes.map((e) => ({ value: e, label: e }));
const memberOptions    = props.teamMembers.map((m) => ({ value: m.id, label: m.name }));

// ── Log level badge ───────────────────────────────────────────────────────────
const eventBadgeClass = (event) => {
    if (!event) { return 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400'; }
    if (/delete|destroy|remov/i.test(event))  { return 'bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400'; }
    if (/creat|invit|add/i.test(event)) { return 'bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400'; }
    if (/updat|edit|chang/i.test(event)) { return 'bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400'; }
    return 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400';
};
</script>

<template>
    <div class="space-y-5">

        <!-- ── Page header ───────────────────────────────────────────── -->
        <div>
            <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Activity Log</h1>
            <p class="mt-1 text-sm text-gray-500">Track all actions taken in your workspace.</p>
        </div>

        <!-- ── Filters ──────────────────────────────────────────────── -->
        <Card>
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Search -->
                <div class="relative">
                    <MagnifyingGlassIcon
                        class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-gray-400"
                    />
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search activity…"
                        class="w-full rounded-xl border border-gray-200 py-2.5 pl-9 pr-4 text-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:placeholder-gray-500"
                    />
                </div>

                <Select
                    id="filter-event"
                    v-model="eventType"
                    placeholder="All events"
                    :options="eventTypeOptions"
                />

                <Select
                    id="filter-member"
                    v-model="causerId"
                    placeholder="All members"
                    :options="memberOptions"
                />

                <!-- Date range -->
                <div class="flex items-center gap-2">
                    <input
                        v-model="dateFrom"
                        type="date"
                        class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                    />
                    <span class="shrink-0 text-xs text-gray-400">to</span>
                    <input
                        v-model="dateTo"
                        type="date"
                        class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                    />
                </div>
            </div>
        </Card>

        <!-- ── Log table ─────────────────────────────────────────────── -->
        <Card>
            <!-- Empty state -->
            <div
                v-if="!logs.data?.length"
                class="flex flex-col items-center py-16 text-center"
            >
                <ClipboardDocumentListIcon class="size-12 text-gray-300 dark:text-gray-600" />
                <p class="mt-3 font-medium text-gray-500 dark:text-gray-400">No activity found</p>
                <p class="mt-1 text-sm text-gray-400">
                    {{ search || eventType || causerId ? 'Try adjusting your filters.' : 'Actions will appear here as your team uses the workspace.' }}
                </p>
            </div>

            <!-- Table -->
            <div v-else class="-mx-5 -my-2 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-400">Event</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-400">Description</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-400">By</th>
                            <th class="hidden px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-400 md:table-cell">IP</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-400">When</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-800/60">
                        <tr
                            v-for="log in logs.data"
                            :key="log.id"
                            class="transition hover:bg-gray-50/60 dark:hover:bg-gray-800/30"
                        >
                            <!-- Event badge -->
                            <td class="whitespace-nowrap px-5 py-3">
                                <span
                                    class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold"
                                    :class="eventBadgeClass(log.event)"
                                >
                                    {{ log.event ?? 'action' }}
                                </span>
                            </td>

                            <!-- Description -->
                            <td class="max-w-xs truncate px-5 py-3 text-gray-700 dark:text-gray-300">
                                {{ log.description }}
                            </td>

                            <!-- Causer -->
                            <td class="whitespace-nowrap px-5 py-3">
                                <div v-if="log.causer" class="flex items-center gap-2">
                                    <div class="flex size-6 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-indigo-400 to-purple-500 text-xs font-bold text-white">
                                        {{ log.causer.name?.charAt(0)?.toUpperCase() ?? '?' }}
                                    </div>
                                    <span class="text-gray-700 dark:text-gray-300">{{ log.causer.name }}</span>
                                </div>
                                <span v-else class="text-gray-400">System</span>
                            </td>

                            <!-- IP -->
                            <td class="hidden whitespace-nowrap px-5 py-3 font-mono text-xs text-gray-400 md:table-cell">
                                {{ log.ip_address ?? '—' }}
                            </td>

                            <!-- Date -->
                            <td class="whitespace-nowrap px-5 py-3 text-right text-gray-400">
                                {{ log.created_at }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <Pagination
                v-if="(logs.meta?.last_page ?? 1) > 1"
                :links="logs.links ?? []"
                :meta="logs.meta ?? {}"
                class="mt-4"
                @change="handlePageChange"
            />
        </Card>
    </div>
</template>
