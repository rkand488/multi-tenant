<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import Card from '@/Components/UI/Card.vue';
import Table from '@/Components/UI/Table.vue';
import Pagination from '@/Components/UI/Pagination.vue';
import { DocumentArrowDownIcon, ReceiptPercentIcon } from '@heroicons/vue/24/outline';

defineOptions({ layout: TenantLayout });

const props = defineProps({
    invoices: { type: Object, required: true }, // paginator
});

const statusClass = (status) => ({
    paid:          'bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400',
    open:          'bg-yellow-50 text-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-400',
    draft:         'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
    void:          'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-500',
    uncollectible: 'bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400',
}[status] ?? 'bg-gray-100 text-gray-600');

const columns = [
    { key: 'number',   label: 'Invoice #',   class: 'w-36' },
    { key: 'period',   label: 'Period' },
    { key: 'amount',   label: 'Amount',      class: 'w-32 text-right' },
    { key: 'status',   label: 'Status',      class: 'w-28' },
    { key: 'paid_at',  label: 'Paid',        class: 'w-36' },
    { key: 'actions',  label: '',            class: 'w-12 text-right' },
];
</script>

<template>
    <div class="space-y-5">

        <!-- Header -->
        <div>
            <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Invoices</h1>
            <p class="mt-1 text-sm text-gray-500">Your billing history and downloadable invoices.</p>
        </div>

        <!-- Table -->
        <Card>
            <Table :columns="columns" :rows="invoices.data">
                <template #cell-number="{ row }">
                    <span class="font-mono text-sm font-medium text-gray-900 dark:text-gray-100">{{ row.number }}</span>
                </template>
                <template #cell-period="{ row }">
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        {{ row.period_start ? new Date(row.period_start).toLocaleDateString() : '—' }}
                        –
                        {{ row.period_end ? new Date(row.period_end).toLocaleDateString() : '—' }}
                    </span>
                </template>
                <template #cell-amount="{ row }">
                    <span class="text-sm font-semibold text-gray-900 dark:text-gray-100 tabular-nums">
                        {{ row.formatted_total }}
                    </span>
                </template>
                <template #cell-status="{ row }">
                    <span :class="[statusClass(row.status), 'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium capitalize']">
                        {{ row.status_label ?? row.status }}
                    </span>
                </template>
                <template #cell-paid_at="{ row }">
                    <span class="text-sm text-gray-500">
                        {{ row.paid_at ? new Date(row.paid_at).toLocaleDateString() : '—' }}
                    </span>
                </template>
                <template #cell-actions="{ row }">
                    <a
                        v-if="row.status === 'paid'"
                        :href="route('tenant.billing.invoices.show', row.id)"
                        target="_blank"
                        class="rounded p-1.5 text-gray-400 hover:bg-gray-100 hover:text-indigo-600 dark:hover:bg-gray-700"
                        title="Download"
                    >
                        <DocumentArrowDownIcon class="size-4" />
                    </a>
                </template>
                <template #empty>
                    <div class="py-12 text-center">
                        <ReceiptPercentIcon class="mx-auto size-10 text-gray-300 dark:text-gray-600" />
                        <p class="mt-3 text-sm text-gray-500">No invoices yet.</p>
                    </div>
                </template>
            </Table>

            <div v-if="invoices.meta?.last_page > 1" class="mt-4">
                <Pagination :meta="invoices.meta" />
            </div>
        </Card>

    </div>
</template>
