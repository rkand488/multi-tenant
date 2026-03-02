<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Card from '@/Components/UI/Card.vue';
import Table from '@/Components/UI/Table.vue';
import Button from '@/Components/UI/Button.vue';
import Input from '@/Components/UI/Input.vue';
import Checkbox from '@/Components/UI/Checkbox.vue';
import Modal from '@/Components/UI/Modal.vue';
import Alert from '@/Components/UI/Alert.vue';
import { useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { PencilSquareIcon, TrashIcon, PlusIcon } from '@heroicons/vue/24/outline';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    plans: { type: Array, default: () => [] },
});

const showCreateModal = ref(false);
const showEditModal   = ref(false);
const showDeleteModal = ref(false);
const editingPlan     = ref(null);
const deletingPlan    = ref(null);

// ── Create / Edit form ─────────────────────────────────────────────────────
const makeForm = (source = {}) => useForm({
    name:                  source.name ?? '',
    slug:                  source.slug ?? '',
    description:           source.description ?? '',
    price_monthly:         source.price_monthly != null ? source.price_monthly / 100 : '',
    price_yearly:          source.price_yearly  != null ? source.price_yearly  / 100 : '',
    trial_days:            source.trial_days ?? 14,
    is_active:             source.is_active ?? true,
    sort_order:            source.sort_order ?? 0,
    features: {
        max_users:     source.features?.max_users ?? '',
        max_storage_mb: source.features?.max_storage_mb ?? '',
        api_access:    source.features?.api_access ?? false,
        sso:           source.features?.sso ?? false,
        custom_domain: source.features?.custom_domain ?? false,
    },
});

let createForm = makeForm();

const openCreate = () => {
    createForm = makeForm();
    showCreateModal.value = true;
};

const submitCreate = () => {
    createForm.post(route('admin.plans.store'), {
        onSuccess: () => (showCreateModal.value = false),
    });
};

const openEdit = (plan) => {
    editingPlan.value = plan;
    // eslint-disable-next-line vue/no-setup-props-destructure
    createForm = makeForm(plan);
    showEditModal.value = true;
};

const submitEdit = () => {
    createForm.put(route('admin.plans.update', editingPlan.value.id), {
        onSuccess: () => (showEditModal.value = false),
    });
};

const openDelete = (plan) => {
    deletingPlan.value = plan;
    showDeleteModal.value = true;
};

const confirmDelete = () => {
    router.delete(route('admin.plans.destroy', deletingPlan.value.id), {
        onSuccess: () => (showDeleteModal.value = false),
    });
};

const columns = [
    { key: 'name',          label: 'Plan',          sortable: true },
    { key: 'price_monthly', label: 'Monthly',        class: 'w-28 text-right' },
    { key: 'price_yearly',  label: 'Yearly',         class: 'w-28 text-right' },
    { key: 'trial_days',    label: 'Trial',          class: 'w-20 text-center' },
    { key: 'subscribers',   label: 'Subscribers',    class: 'w-28 text-center' },
    { key: 'status',        label: 'Status',         class: 'w-24' },
    { key: 'actions',       label: '',               class: 'w-24 text-right' },
];
</script>

<template>

    <div class="space-y-5">

        <!-- ── Toolbar ─────────────────────────────────────────────────── -->
        <div class="flex justify-end">
            <Button size="sm" @click="openCreate">
                <PlusIcon class="mr-1.5 size-4" />
                New Plan
            </Button>
        </div>

        <!-- ── Plans table ─────────────────────────────────────────────── -->
        <Card :padded="false">
            <Table :columns="columns" :rows="plans">
                <template #row="{ row }">
                    <!-- Name -->
                    <td class="px-4 py-3">
                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ row.name }}</p>
                        <p class="text-xs text-gray-400">{{ row.description }}</p>
                    </td>

                    <!-- Monthly price -->
                    <td class="px-4 py-3 text-right text-sm font-medium text-gray-800 dark:text-gray-200">
                        ${{ ((row.price_monthly ?? 0) / 100).toFixed(2) }}
                    </td>

                    <!-- Yearly price -->
                    <td class="px-4 py-3 text-right text-sm font-medium text-gray-800 dark:text-gray-200">
                        ${{ ((row.price_yearly ?? 0) / 100).toFixed(2) }}
                    </td>

                    <!-- Trial days -->
                    <td class="px-4 py-3 text-center text-sm text-gray-500 dark:text-gray-400">
                        {{ row.trial_days ?? 0 }}d
                    </td>

                    <!-- Subscribers -->
                    <td class="px-4 py-3 text-center text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ row.subscriptions_count ?? 0 }}
                    </td>

                    <!-- Status -->
                    <td class="px-4 py-3">
                        <span
                            class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium"
                            :class="row.is_active
                                ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400'
                                : 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400'"
                        >
                            {{ row.is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>

                    <!-- Actions -->
                    <td class="px-4 py-3 text-right">
                        <div class="inline-flex items-center gap-1">
                            <button
                                type="button"
                                class="rounded p-1 text-gray-400 hover:bg-gray-100 hover:text-indigo-600 dark:hover:bg-gray-800"
                                title="Edit"
                                @click="openEdit(row)"
                            >
                                <PencilSquareIcon class="size-4" />
                            </button>
                            <button
                                type="button"
                                class="rounded p-1 text-gray-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20 dark:hover:text-red-400"
                                title="Delete"
                                @click="openDelete(row)"
                            >
                                <TrashIcon class="size-4" />
                            </button>
                        </div>
                    </td>
                </template>
                <template #empty>No plans yet. Create your first plan above.</template>
            </Table>
        </Card>
    </div>

    <!-- ── Plan Form Modal (shared for create + edit) ─────────────────── -->
    <Modal
        :show="showCreateModal || showEditModal"
        :title="showEditModal ? 'Edit Plan' : 'New Plan'"
        max-width="lg"
        @close="showCreateModal = showEditModal = false"
    >
        <form class="space-y-4" @submit.prevent="showEditModal ? submitEdit() : submitCreate()">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <Input
                    id="plan-name"
                    v-model="createForm.name"
                    label="Name"
                    placeholder="Starter"
                    :error="createForm.errors.name"
                    required
                />
                <Input
                    id="plan-slug"
                    v-model="createForm.slug"
                    label="Slug"
                    placeholder="starter"
                    :error="createForm.errors.slug"
                    required
                />
            </div>

            <Input
                id="plan-description"
                v-model="createForm.description"
                label="Description"
                placeholder="Perfect for small teams…"
                :error="createForm.errors.description"
            />

            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                <Input
                    id="plan-price-monthly"
                    v-model="createForm.price_monthly"
                    label="Monthly ($)"
                    type="number"
                    placeholder="29"
                    :error="createForm.errors.price_monthly"
                />
                <Input
                    id="plan-price-yearly"
                    v-model="createForm.price_yearly"
                    label="Yearly ($)"
                    type="number"
                    placeholder="290"
                    :error="createForm.errors.price_yearly"
                />
                <Input
                    id="plan-trial"
                    v-model="createForm.trial_days"
                    label="Trial days"
                    type="number"
                    placeholder="14"
                />
                <Input
                    id="plan-order"
                    v-model="createForm.sort_order"
                    label="Sort order"
                    type="number"
                    placeholder="1"
                />
            </div>

            <fieldset>
                <legend class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500">Features</legend>
                <div class="grid grid-cols-2 gap-3">
                    <Input
                        id="feat-users"
                        v-model="createForm.features.max_users"
                        label="Max users"
                        type="number"
                        placeholder="Unlimited"
                    />
                    <Input
                        id="feat-storage"
                        v-model="createForm.features.max_storage_mb"
                        label="Storage (MB)"
                        type="number"
                        placeholder="1024"
                    />
                </div>
                <div class="mt-3 flex flex-wrap gap-4">
                    <Checkbox id="feat-api"    v-model="createForm.features.api_access"    label="API access" />
                    <Checkbox id="feat-sso"    v-model="createForm.features.sso"            label="SSO" />
                    <Checkbox id="feat-domain" v-model="createForm.features.custom_domain" label="Custom domain" />
                </div>
            </fieldset>

            <Checkbox id="plan-active" v-model="createForm.is_active" label="Active (visible to new customers)" />
        </form>

        <template #footer>
            <Button :loading="createForm.processing" @click="showEditModal ? submitEdit() : submitCreate()">
                {{ showEditModal ? 'Save Changes' : 'Create Plan' }}
            </Button>
            <Button variant="secondary" @click="showCreateModal = showEditModal = false">Cancel</Button>
        </template>
    </Modal>

    <!-- ── Delete confirm ─────────────────────────────────────────────── -->
    <Modal :show="showDeleteModal" title="Delete Plan" max-width="sm" @close="showDeleteModal = false">
        <p class="text-sm text-gray-600 dark:text-gray-300">
            Delete <strong>{{ deletingPlan?.name }}</strong>?
            Existing subscriptions will not be affected but no new subscribers can sign up for this plan.
        </p>
        <template #footer>
            <Button variant="danger" @click="confirmDelete">Delete</Button>
            <Button variant="secondary" @click="showDeleteModal = false">Cancel</Button>
        </template>
    </Modal>
</template>
