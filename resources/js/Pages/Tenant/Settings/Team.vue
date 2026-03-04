<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import Card from '@/Components/UI/Card.vue';
import Input from '@/Components/UI/Input.vue';
import Select from '@/Components/UI/Select.vue';
import Button from '@/Components/UI/Button.vue';
import Alert from '@/Components/UI/Alert.vue';
import Modal from '@/Components/UI/Modal.vue';
import { useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { ArrowPathIcon, BuildingOffice2Icon, ExclamationTriangleIcon } from '@heroicons/vue/24/outline';

defineOptions({ layout: TenantLayout });

const props = defineProps({
    tenant:              { type: Object, required: true },
    timezones:           { type: Array,  default: () => [] },
    isOwner:             { type: Boolean, default: false },
    transferableMembers: { type: Array, default: () => [] },
});

// ── Team settings form ───────────────────────────────────────────────────────
const teamSaved = ref(false);

const teamForm = useForm({
    name:     props.tenant.name,
    timezone: props.tenant.timezone ?? '',
});

const updateTeam = () => {
    teamSaved.value = false;
    teamForm.put(route('tenant.settings.update-team'), {
        onSuccess: () => {
            teamSaved.value = true;
        },
    });
};

// ── Delete workspace modal ───────────────────────────────────────────────────
const showDeleteModal  = ref(false);
const deleteConfirmName = ref('');
const deleteForm       = useForm({});

const canConfirmDelete = () => deleteConfirmName.value === props.tenant.name;

const confirmDeleteWorkspace = () => {
    if (!canConfirmDelete()) { return; }
    deleteForm.delete(route('tenant.settings.destroy'), {
        onSuccess: () => {},
    });
};

const timezoneOptions = props.timezones.map((tz) => ({ value: tz, label: tz }));

// ── Transfer ownership modal ─────────────────────────────────────────────────
const showTransferModal = ref(false);
const transferForm      = useForm({ user_id: null });

const selectedMemberName = computed(
    () => props.transferableMembers.find((m) => m.id === transferForm.user_id)?.name ?? '',
);

const memberOptions = computed(() =>
    props.transferableMembers.map((m) => ({ value: m.id, label: `${m.name} (${m.email})` })),
);

const confirmTransfer = () => {
    if (!transferForm.user_id) { return; }
    transferForm.post(route('tenant.settings.transfer-ownership'), {
        onSuccess: () => { showTransferModal.value = false; },
    });
};
</script>

<template>
    <div class="mx-auto max-w-2xl space-y-6">

        <div>
            <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Team Settings</h1>
            <p class="mt-1 text-sm text-gray-500">Configure your workspace details and preferences.</p>
        </div>

        <!-- ── Workspace info ────────────────────────────────────────── -->
        <Card>
            <template #header>
                <div class="flex items-center gap-3 px-5 py-4">
                    <div class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-indigo-50 dark:bg-indigo-900/20">
                        <BuildingOffice2Icon class="size-5 text-indigo-600 dark:text-indigo-400" />
                    </div>
                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">Workspace Information</p>
                </div>
            </template>

            <div class="space-y-4">
                <Alert v-if="teamSaved" variant="success">Workspace settings saved.</Alert>

                <Input
                    id="team-name"
                    v-model="teamForm.name"
                    label="Workspace name"
                    :error="teamForm.errors.name"
                    required
                />

                <!-- Slug (read-only) -->
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Workspace slug</label>
                    <div class="flex items-center gap-2 rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-800/60 dark:text-gray-400">
                        <span class="text-gray-400">yourdomain.app/</span>
                        <span class="font-mono font-medium text-gray-700 dark:text-gray-200">{{ tenant.slug }}</span>
                        <span class="ml-auto text-xs text-gray-400">Cannot be changed</span>
                    </div>
                </div>

                <Select
                    v-if="timezoneOptions.length"
                    id="team-timezone"
                    v-model="teamForm.timezone"
                    label="Timezone"
                    placeholder="Select timezone…"
                    :options="timezoneOptions"
                    :error="teamForm.errors.timezone"
                />
            </div>

            <template #footer>
                <Button :loading="teamForm.processing" @click="updateTeam">
                    Save Settings
                </Button>
            </template>
        </Card>

        <!-- ── Workspace details (read-only) ────────────────────────── -->
        <Card>
            <template #header>
                <p class="px-5 py-4 text-sm font-semibold text-gray-900 dark:text-gray-100">Workspace Details</p>
            </template>

            <dl class="divide-y divide-gray-50 dark:divide-gray-800">
                <div
                    v-for="item in [
                        { label: 'Created', value: tenant.created_at },
                        { label: 'Plan', value: tenant.plan_name ?? 'Free' },
                        { label: 'Members', value: tenant.users_count ?? '—' },
                    ]"
                    :key="item.label"
                    class="flex justify-between py-3 text-sm"
                >
                    <dt class="text-gray-500">{{ item.label }}</dt>
                    <dd class="font-medium text-gray-900 dark:text-gray-100">{{ item.value }}</dd>
                </div>
            </dl>
        </Card>

        <!-- ── Transfer Ownership ───────────────────────────────────── -->
        <Card v-if="isOwner">
            <template #header>
                <div class="flex items-center gap-3 px-5 py-4">
                    <div class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-amber-50 dark:bg-amber-900/20">
                        <ArrowPathIcon class="size-5 text-amber-600 dark:text-amber-400" />
                    </div>
                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">Transfer Ownership</p>
                </div>
            </template>

            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Transfer to another member</p>
                    <p class="mt-0.5 text-xs text-gray-500">
                        The new owner will have full control. You will become a regular member.
                    </p>
                </div>
                <Button
                    variant="secondary"
                    class="shrink-0"
                    :disabled="transferableMembers.length === 0"
                    @click="showTransferModal = true"
                >
                    Transfer
                </Button>
            </div>
        </Card>

        <!-- ── Danger zone ───────────────────────────────────────────── -->
        <Card class="border border-red-100 dark:border-red-900/30">
            <template #header>
                <div class="flex items-center gap-3 px-5 py-4">
                    <ExclamationTriangleIcon class="size-5 text-red-500" />
                    <p class="text-sm font-semibold text-red-600 dark:text-red-400">Danger Zone</p>
                </div>
            </template>

            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Delete this workspace</p>
                    <p class="mt-0.5 text-xs text-gray-500">
                        Permanently deletes all data, members, subscriptions and files. This cannot be undone.
                    </p>
                </div>
                <Button variant="danger" class="shrink-0" @click="showDeleteModal = true">
                    Delete Workspace
                </Button>
            </div>
        </Card>
    </div>

    <!-- ── Delete confirm modal ─────────────────────────────────────────── -->
    <Modal :show="showDeleteModal" @close="showDeleteModal = false">
        <template #title>Delete Workspace</template>

        <div class="space-y-3">
            <Alert variant="error">
                This action is permanent and cannot be undone. All data will be destroyed.
            </Alert>

            <p class="text-sm text-gray-600 dark:text-gray-400">
                Type <span class="font-semibold text-gray-900 dark:text-gray-100">{{ tenant.name }}</span> to confirm:
            </p>

            <input
                v-model="deleteConfirmName"
                type="text"
                :placeholder="tenant.name"
                class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm outline-none focus:border-red-400 focus:ring-2 focus:ring-red-100 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
            />
        </div>

        <template #footer>
            <div class="flex justify-end gap-3">
                <Button variant="secondary" @click="showDeleteModal = false">Cancel</Button>
                <Button
                    variant="danger"
                    :disabled="!canConfirmDelete()"
                    :loading="deleteForm.processing"
                    @click="confirmDeleteWorkspace"
                >
                    Yes, Delete Workspace
                </Button>
            </div>
        </template>
    </Modal>

    <!-- ── Transfer ownership modal ──────────────────────────────────────── -->
    <Modal :show="showTransferModal" @close="showTransferModal = false">
        <template #title>Transfer Workspace Ownership</template>

        <div class="space-y-4">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Select a member to become the new workspace owner. This action cannot be undone.
            </p>

            <Select
                id="transfer-user"
                v-model="transferForm.user_id"
                label="New owner"
                placeholder="Select a member…"
                :options="memberOptions"
                :error="transferForm.errors.user_id"
            />

            <Alert v-if="selectedMemberName" variant="warning">
                <strong>{{ selectedMemberName }}</strong> will become the workspace owner. You will become a regular member.
            </Alert>
        </div>

        <template #footer>
            <div class="flex justify-end gap-3">
                <Button variant="secondary" @click="showTransferModal = false">Cancel</Button>
                <Button
                    variant="primary"
                    :disabled="!transferForm.user_id"
                    :loading="transferForm.processing"
                    @click="confirmTransfer"
                >
                    Transfer Ownership
                </Button>
            </div>
        </template>
    </Modal>
</template>
