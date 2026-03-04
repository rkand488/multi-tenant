<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Card from '@/Components/UI/Card.vue';
import Table from '@/Components/UI/Table.vue';
import Pagination from '@/Components/UI/Pagination.vue';
import Button from '@/Components/UI/Button.vue';
import Input from '@/Components/UI/Input.vue';
import Dropdown from '@/Components/UI/Dropdown.vue';
import DropdownItem from '@/Components/UI/DropdownItem.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { MagnifyingGlassIcon, FunnelIcon, BuildingOfficeIcon } from '@heroicons/vue/24/outline';
import { useDebounceFn } from '@vueuse/core';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    tenants: { type: Object, required: true }, // paginator
    filters: { type: Object, default: () => ({}) },
});

const search  = ref(props.filters.search ?? '');
const status  = ref(props.filters.status ?? '');

const columns = [
    { key: 'name',       label: 'Tenant',       sortable: true },
    { key: 'plan',       label: 'Plan',         class: 'w-32' },
    { key: 'users',      label: 'Users',        class: 'w-24 text-center' },
    { key: 'storage',    label: 'Storage',      class: 'w-32' },
    { key: 'status',     label: 'Status',       class: 'w-28' },
    { key: 'created_at', label: 'Created',      class: 'w-36', sortable: true },
    { key: 'actions',    label: '',             class: 'w-20 text-right' },
];

const sortKey = ref(props.filters.sort ?? '');
const sortDir = ref(props.filters.direction ?? 'asc');

const applyFilters = () => {
    router.get(route('admin.tenants.index'), {
        search: search.value,
        status: status.value,
        sort: sortKey.value,
        direction: sortDir.value,
    }, { preserveState: true, replace: true });
};

const debouncedSearch = useDebounceFn(applyFilters, 350);

watch(search, debouncedSearch);
watch(status, applyFilters);

const handleSort = (key) => {
    if (sortKey.value === key) {
        sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortKey.value = key;
        sortDir.value = 'asc';
    }
    applyFilters();
};

const handlePageChange = (page) => {
    router.get(route('admin.tenants.index'), { ...props.filters, page }, { preserveState: true });
};

const suspendTenant = (id) => {
    if (confirm('Suspend this tenant? Their users will lose access.')) {
        router.patch(route('admin.tenants.suspend', id));
    }
};

const deleteTenant = (id) => {
    if (confirm('Permanently delete this tenant and all their data? This cannot be undone.')) {
        router.delete(route('admin.tenants.destroy', id));
    }
};
</script>

<template>

    <div class="space-y-5">

        <!-- ── Toolbar ─────────────────────────────────────────────────── -->
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-1 items-center gap-2">
                <div class="relative w-full max-w-xs">
                    <MagnifyingGlassIcon class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-gray-400" />
                    <input
                        v-model="search"
                        type="search"
                        placeholder="Search tenants…"
                        class="h-9 w-full rounded-lg border border-gray-200 bg-white pl-9 pr-3 text-sm placeholder-gray-400 shadow-sm focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500"
                    />
                </div>

                <!-- Status filter -->
                <div class="relative">
                    <FunnelIcon class="pointer-events-none absolute left-2.5 top-1/2 size-4 -translate-y-1/2 text-gray-400" />
                    <select
                        v-model="status"
                        class="h-9 rounded-lg border border-gray-200 bg-white pl-8 pr-3 text-sm shadow-sm focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                    >
                        <option value="">All Statuses</option>
                        <option value="active">Active</option>
                        <option value="suspended">Suspended</option>
                        <option value="trial">Trial</option>
                    </select>
                </div>
            </div>

            <Link :href="route('admin.tenants.create')">
                <Button size="sm">+ New Tenant</Button>
            </Link>
        </div>

        <!-- ── Table ───────────────────────────────────────────────────── -->
        <Card :padded="false">
            <Table
                :columns="columns"
                :rows="tenants.data ?? []"
                :sort-key="sortKey"
                :sort-dir="sortDir"
                @sort="handleSort"
            >
                <template #row="{ row }">
                    <!-- Name + domain -->
                    <td class="whitespace-nowrap px-4 py-3">
                        <div class="flex items-center gap-3">
                            <div class="flex size-9 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-sm font-bold text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300">
                                {{ row.name?.[0]?.toUpperCase() }}
                            </div>
                            <div>
                                <Link
                                    :href="route('admin.tenants.show', row.id)"
                                    class="text-sm font-medium text-gray-900 hover:underline dark:text-gray-100"
                                >
                                    {{ row.name }}
                                </Link>
                                <p class="text-xs text-gray-400">{{ row.domain }}</p>
                            </div>
                        </div>
                    </td>

                    <!-- Plan -->
                    <td class="whitespace-nowrap px-4 py-3">
                        <span class="inline-flex items-center rounded-full bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300">
                            {{ row.plan ?? '—' }}
                        </span>
                    </td>

                    <!-- Users -->
                    <td class="px-4 py-3 text-center text-sm text-gray-600 dark:text-gray-300">{{ row.users_count ?? 0 }}</td>

                    <!-- Storage -->
                    <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                        {{ row.storage_used_mb != null ? `${row.storage_used_mb} MB` : '—' }}
                    </td>

                    <!-- Status -->
                    <td class="whitespace-nowrap px-4 py-3">
                        <span
                            class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium"
                            :class="{
                                'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400': row.status === 'active',
                                'bg-yellow-50 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400': row.status === 'trial',
                                'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-400': row.status === 'suspended',
                                'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400': !['active','trial','suspended'].includes(row.status),
                            }"
                        >
                            {{ row.status }}
                        </span>
                    </td>

                    <!-- Created -->
                    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-400">{{ row.created_at }}</td>

                    <!-- Actions -->
                    <td class="px-4 py-3 text-right">
                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <button
                                    type="button"
                                    class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium text-gray-500 ring-1 ring-inset ring-gray-200 transition-colors hover:bg-gray-50 dark:ring-gray-700 dark:hover:bg-gray-800"
                                >
                                    Actions ▾
                                </button>
                            </template>
                            <template #items>
                                <DropdownItem @click="router.visit(route('admin.tenants.show', row.id))">View details</DropdownItem>
                                <DropdownItem @click="suspendTenant(row.id)">Suspend</DropdownItem>
                                <DropdownItem variant="danger" @click="deleteTenant(row.id)">Delete</DropdownItem>
                            </template>
                        </Dropdown>
                    </td>
                </template>

                <template #empty>
                    <div class="py-4">
                        <BuildingOfficeIcon class="mx-auto mb-2 size-8 text-gray-300" />
                        No tenants found. Try adjusting your filters.
                    </div>
                </template>
            </Table>
        </Card>

        <!-- ── Pagination ──────────────────────────────────────────────── -->
        <Pagination
            v-if="tenants.meta && tenants.meta.last_page > 1"
            :links="tenants.links ?? []"
            :meta="tenants.meta"
            @change="handlePageChange"
        />
    </div>
</template>
