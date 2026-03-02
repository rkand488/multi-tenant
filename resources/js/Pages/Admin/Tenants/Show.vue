<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Card from '@/Components/UI/Card.vue';
import Table from '@/Components/UI/Table.vue';
import Button from '@/Components/UI/Button.vue';
import Alert from '@/Components/UI/Alert.vue';
import Modal from '@/Components/UI/Modal.vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import {
    BuildingOfficeIcon,
    GlobeAltIcon,
    UsersIcon,
    CreditCardIcon,
    ServerIcon,
    ClockIcon,
} from '@heroicons/vue/24/outline';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    tenant: { type: Object, required: true },
    subscription: { type: Object, default: null },
    usageStats: { type: Object, default: () => ({}) },
    activityLog: { type: Array, default: () => [] },
    users: { type: Array, default: () => [] },
    invoices: { type: Array, default: () => [] },
});

const showSuspendModal = ref(false);
const showDeleteModal  = ref(false);

const suspendForm = useForm({});
const deleteForm  = useForm({});

const submitSuspend = () => {
    suspendForm.patch(route('admin.tenants.suspend', props.tenant.id), {
        onSuccess: () => (showSuspendModal.value = false),
    });
};

const submitDelete = () => {
    deleteForm.delete(route('admin.tenants.destroy', props.tenant.id), {
        onSuccess: () => router.visit(route('admin.tenants.index')),
    });
};

const userColumns = [
    { key: 'name',       label: 'Name' },
    { key: 'email',      label: 'Email' },
    { key: 'role',       label: 'Role',    class: 'w-28' },
    { key: 'last_login', label: 'Last Login', class: 'w-40' },
];

const invoiceColumns = [
    { key: 'number',     label: 'Invoice' },
    { key: 'amount',     label: 'Amount',  class: 'w-28 text-right' },
    { key: 'status',     label: 'Status',  class: 'w-28' },
    { key: 'date',       label: 'Date',    class: 'w-36' },
    { key: 'download',   label: '',        class: 'w-20 text-right' },
];

const activityColumns = [
    { key: 'event',       label: 'Event' },
    { key: 'causer',      label: 'By',    class: 'w-40' },
    { key: 'created_at',  label: 'Time',  class: 'w-44' },
];
</script>

<template>

    <div class="space-y-6">

        <!-- ── Breadcrumb ──────────────────────────────────────────────── -->
        <nav class="flex items-center gap-2 text-sm text-gray-400">
            <Link :href="route('admin.tenants.index')" class="hover:text-indigo-600">Tenants</Link>
            <span>/</span>
            <span class="text-gray-700 dark:text-gray-200">{{ tenant.name }}</span>
        </nav>

        <!-- ── Header card ─────────────────────────────────────────────── -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="flex items-center gap-4">
                <div class="flex size-14 shrink-0 items-center justify-center rounded-2xl bg-indigo-100 text-2xl font-bold text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300">
                    {{ tenant.name?.[0]?.toUpperCase() }}
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ tenant.name }}</h1>
                    <div class="mt-0.5 flex items-center gap-3 text-sm text-gray-400">
                        <span class="flex items-center gap-1">
                            <GlobeAltIcon class="size-4" />
                            {{ tenant.domain ?? 'No domain' }}
                        </span>
                        <span
                            class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium"
                            :class="{
                                'bg-emerald-100 text-emerald-700': tenant.status === 'active',
                                'bg-yellow-100 text-yellow-700': tenant.status === 'trial',
                                'bg-red-100 text-red-700': tenant.status === 'suspended',
                            }"
                        >
                            {{ tenant.status }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <Button
                    variant="secondary"
                    size="sm"
                    @click="showSuspendModal = true"
                >
                    {{ tenant.status === 'suspended' ? 'Unsuspend' : 'Suspend' }}
                </Button>
                <Button
                    variant="danger"
                    size="sm"
                    @click="showDeleteModal = true"
                >
                    Delete Tenant
                </Button>
            </div>
        </div>

        <!-- ── Stats row ───────────────────────────────────────────────── -->
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
            <Card :flat="false">
                <div class="flex items-center gap-3">
                    <UsersIcon class="size-8 text-indigo-400" />
                    <div>
                        <p class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ usageStats.users ?? 0 }}</p>
                        <p class="text-xs text-gray-400">Users</p>
                    </div>
                </div>
            </Card>
            <Card :flat="false">
                <div class="flex items-center gap-3">
                    <ServerIcon class="size-8 text-violet-400" />
                    <div>
                        <p class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ usageStats.storage_mb ?? 0 }} MB</p>
                        <p class="text-xs text-gray-400">Storage used</p>
                    </div>
                </div>
            </Card>
            <Card :flat="false">
                <div class="flex items-center gap-3">
                    <CreditCardIcon class="size-8 text-emerald-400" />
                    <div>
                        <p class="text-xl font-bold text-gray-900 dark:text-gray-100">
                            {{ subscription?.plan_name ?? 'Free' }}
                        </p>
                        <p class="text-xs text-gray-400">Current plan</p>
                    </div>
                </div>
            </Card>
            <Card :flat="false">
                <div class="flex items-center gap-3">
                    <ClockIcon class="size-8 text-amber-400" />
                    <div>
                        <p class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ tenant.created_at }}</p>
                        <p class="text-xs text-gray-400">Created</p>
                    </div>
                </div>
            </Card>
        </div>

        <!-- ── Subscription detail ─────────────────────────────────────── -->
        <Card title="Subscription">
            <div v-if="subscription" class="grid grid-cols-1 gap-4 text-sm sm:grid-cols-3">
                <div>
                    <p class="text-xs text-gray-400">Plan</p>
                    <p class="mt-0.5 font-medium text-gray-900 dark:text-gray-100">{{ subscription.plan_name }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400">Status</p>
                    <p class="mt-0.5 font-medium text-gray-900 dark:text-gray-100 capitalize">{{ subscription.status }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400">Renews / Ends</p>
                    <p class="mt-0.5 font-medium text-gray-900 dark:text-gray-100">{{ subscription.ends_at ?? '—' }}</p>
                </div>
            </div>
            <Alert v-else variant="info">This tenant has no active subscription.</Alert>
        </Card>

        <!-- ── Users ───────────────────────────────────────────────────── -->
        <Card title="Users" :padded="false">
            <Table :columns="userColumns" :rows="users">
                <template #row="{ row }">
                    <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100">{{ row.name }}</td>
                    <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ row.email }}</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">{{ row.role }}</span>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-400">{{ row.last_login ?? 'Never' }}</td>
                </template>
                <template #empty>No users in this tenant yet.</template>
            </Table>
        </Card>

        <!-- ── Invoices ────────────────────────────────────────────────── -->
        <Card title="Invoices" :padded="false">
            <Table :columns="invoiceColumns" :rows="invoices">
                <template #row="{ row }">
                    <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100">{{ row.number }}</td>
                    <td class="px-4 py-3 text-right text-sm font-medium text-gray-900 dark:text-gray-100">
                        ${{ ((row.amount_cents ?? 0) / 100).toFixed(2) }}
                    </td>
                    <td class="px-4 py-3">
                        <span
                            class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium"
                            :class="row.status === 'paid' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-yellow-50 text-yellow-700'"
                        >
                            {{ row.status }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-400">{{ row.date }}</td>
                    <td class="px-4 py-3 text-right">
                        <a
                            v-if="row.download_url"
                            :href="row.download_url"
                            target="_blank"
                            class="text-xs font-medium text-indigo-600 hover:underline dark:text-indigo-400"
                        >
                            PDF
                        </a>
                    </td>
                </template>
                <template #empty>No invoices found.</template>
            </Table>
        </Card>

        <!-- ── Activity log ────────────────────────────────────────────── -->
        <Card title="Activity Log" :padded="false">
            <Table :columns="activityColumns" :rows="activityLog">
                <template #row="{ row }">
                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">{{ row.description }}</td>
                    <td class="px-4 py-3 text-sm text-gray-400">{{ row.causer_name ?? 'System' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-400">{{ row.created_at }}</td>
                </template>
                <template #empty>No activity recorded.</template>
            </Table>
        </Card>
    </div>

    <!-- ── Suspend Modal ──────────────────────────────────────────────── -->
    <Modal :show="showSuspendModal" title="Suspend Tenant" max-width="md" @close="showSuspendModal = false">
        <p>
            Suspending <strong>{{ tenant.name }}</strong> will immediately block all users from accessing their workspace.
            You can unsuspend them at any time.
        </p>
        <template #footer>
            <Button variant="danger" :loading="suspendForm.processing" @click="submitSuspend">
                {{ tenant.status === 'suspended' ? 'Unsuspend' : 'Suspend' }} Tenant
            </Button>
            <Button variant="secondary" @click="showSuspendModal = false">Cancel</Button>
        </template>
    </Modal>

    <!-- ── Delete Modal ───────────────────────────────────────────────── -->
    <Modal :show="showDeleteModal" title="Delete Tenant" max-width="md" @close="showDeleteModal = false">
        <Alert variant="danger" title="This action is irreversible">
            All data, users, files, and databases belonging to <strong>{{ tenant.name }}</strong> will be permanently erased.
        </Alert>
        <template #footer>
            <Button variant="danger" :loading="deleteForm.processing" @click="submitDelete">Yes, delete permanently</Button>
            <Button variant="secondary" @click="showDeleteModal = false">Cancel</Button>
        </template>
    </Modal>
</template>
