<script setup>
/**
 * Table
 *
 * @example
 * <Table
 *     :columns="[
 *         { key: 'name',       label: 'Name',   sortable: true },
 *         { key: 'email',      label: 'Email' },
 *         { key: 'role',       label: 'Role',   class: 'w-32' },
 *         { key: 'created_at', label: 'Joined', sortable: true },
 *         { key: 'actions',    label: '',       class: 'w-20 text-right' },
 *     ]"
 *     :rows="users.data"
 *     :loading="isLoading"
 *     sort-key="name"
 *     sort-dir="asc"
 *     @sort="handleSort"
 * >
 *     <template #row="{ row }">
 *         <td class="px-4 py-3 font-medium">{{ row.name }}</td>
 *         <td class="px-4 py-3 text-gray-500">{{ row.email }}</td>
 *         <td class="px-4 py-3"><Badge :variant="row.role === 'admin' ? 'info' : 'default'">{{ row.role }}</Badge></td>
 *         <td class="px-4 py-3 text-gray-500">{{ formatDate(row.created_at) }}</td>
 *         <td class="px-4 py-3 text-right">
 *             <Button size="xs" variant="ghost">Edit</Button>
 *         </td>
 *     </template>
 *
 *     <template #empty>
 *         No users found. <a href="#">Invite someone</a>.
 *     </template>
 * </Table>
 */

defineProps({
    /** [{ key, label, sortable?, class?, headerClass? }] */
    columns: { type: Array,   required: true },
    rows:    { type: Array,   required: true },
    loading: { type: Boolean, default: false },
    /** Currently sorted column key */
    sortKey: { type: String,  default: null },
    /** 'asc' | 'desc' */
    sortDir: { type: String,  default: 'asc', validator: (v) => ['asc', 'desc'].includes(v) },
    /** Remove outer ring/shadow (useful when inside a Card with :padded="false") */
    borderless: { type: Boolean, default: false },
    /** Caption text for screen readers */
    caption: { type: String, default: null },
});

const emit = defineEmits(['sort']);

const onSort = (col) => {
    if (!col.sortable) { return; }
    emit('sort', col.key);
};
</script>

<template>
    <div
        class="overflow-hidden"
        :class="borderless ? '' : 'rounded-xl ring-1 ring-gray-200 shadow-sm dark:ring-gray-700'"
    >
        <!-- Loading bar -->
        <div
            class="h-0.5 w-full overflow-hidden bg-gray-100 dark:bg-gray-700"
            role="status"
            :aria-label="loading ? 'Loading' : undefined"
        >
            <div
                v-if="loading"
                class="h-full animate-pulse bg-indigo-500"
                style="width: 60%"
                aria-hidden="true"
            />
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <caption v-if="caption" class="sr-only">{{ caption }}</caption>

                <!-- Header -->
                <thead class="bg-gray-50 dark:bg-gray-800/60">
                    <tr>
                        <th
                            v-for="col in columns"
                            :key="col.key"
                            scope="col"
                            class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400"
                            :class="col.headerClass ?? col.class"
                        >
                            <button
                                v-if="col.sortable"
                                type="button"
                                class="inline-flex items-center gap-1.5 transition-colors hover:text-gray-900 dark:hover:text-gray-100"
                                :aria-sort="sortKey === col.key ? (sortDir === 'asc' ? 'ascending' : 'descending') : 'none'"
                                @click="onSort(col)"
                            >
                                {{ col.label }}
                                <span class="shrink-0 text-gray-400" aria-hidden="true">
                                    <template v-if="sortKey === col.key">
                                        {{ sortDir === 'asc' ? '↑' : '↓' }}
                                    </template>
                                    <template v-else>↕</template>
                                </span>
                            </button>
                            <span v-else>{{ col.label }}</span>
                        </th>
                    </tr>
                </thead>

                <!-- Body -->
                <tbody class="divide-y divide-gray-100 bg-white dark:divide-gray-700/60 dark:bg-gray-900">

                    <!-- Empty state -->
                    <tr v-if="!loading && rows.length === 0">
                        <td
                            :colspan="columns.length"
                            class="px-4 py-12 text-center text-sm text-gray-400 dark:text-gray-500"
                        >
                            <slot name="empty">No records found.</slot>
                        </td>
                    </tr>

                    <!-- Skeleton rows while loading -->
                    <template v-else-if="loading && rows.length === 0">
                        <tr v-for="n in 5" :key="n" aria-hidden="true">
                            <td v-for="col in columns" :key="col.key" class="px-4 py-3">
                                <div class="h-4 animate-pulse rounded bg-gray-100 dark:bg-gray-800" />
                            </td>
                        </tr>
                    </template>

                    <!-- Data rows -->
                    <template v-else>
                        <tr
                            v-for="(row, idx) in rows"
                            :key="row.id ?? idx"
                            class="transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/40"
                        >
                            <slot name="row" :row="row" :index="idx" />
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</template>
