<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import Card from '@/Components/UI/Card.vue';
import Button from '@/Components/UI/Button.vue';
import Table from '@/Components/UI/Table.vue';
import Modal from '@/Components/UI/Modal.vue';
import Alert from '@/Components/UI/Alert.vue';
import Input from '@/Components/UI/Input.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { KeyIcon, TrashIcon, PlusIcon, ClipboardDocumentIcon } from '@heroicons/vue/24/outline';

defineOptions({ layout: TenantLayout });

const props = defineProps({
    tokens: { type: Array, default: () => [] },
});

// ── Create token ──────────────────────────────────────────────────────────────
const showCreateModal = ref(false);
const newToken        = ref(null);
const createForm = useForm({
    name:             '',
    expires_in_days:  '',
});

const submitCreate = () => {
    createForm.post(route('tokens.store'), {
        onSuccess: (page) => {
            newToken.value = page.props.flash?.token ?? null;
            showCreateModal.value = false;
            createForm.reset();
        },
    });
};

// ── Revoke token ──────────────────────────────────────────────────────────────
const showRevokeModal = ref(false);
const revokingToken   = ref(null);
const revokeForm      = useForm({});

const openRevokeModal = (token) => {
    revokingToken.value  = token;
    showRevokeModal.value = true;
};

const confirmRevoke = () => {
    revokeForm.delete(route('tokens.destroy', revokingToken.value.id), {
        onSuccess: () => (showRevokeModal.value = false),
    });
};

// ── Copy to clipboard ─────────────────────────────────────────────────────────
const copiedToken = ref(false);
const copyToken = async () => {
    await navigator.clipboard.writeText(newToken.value);
    copiedToken.value = true;
    setTimeout(() => (copiedToken.value = false), 2000);
};

const columns = [
    { key: 'name',       label: 'Token name' },
    { key: 'abilities',  label: 'Scopes',      class: 'w-48' },
    { key: 'last_used',  label: 'Last used',   class: 'w-36' },
    { key: 'expires',    label: 'Expires',     class: 'w-36' },
    { key: 'actions',    label: '',            class: 'w-16 text-right' },
];
</script>

<template>
    <div class="space-y-5">

        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">API Tokens</h1>
                <p class="mt-1 text-sm text-gray-500">Manage personal access tokens for programmatic API access.</p>
            </div>
            <Button @click="showCreateModal = true" class="flex items-center gap-2">
                <PlusIcon class="size-4" />
                New token
            </Button>
        </div>

        <!-- New token value alert -->
        <Alert
            v-if="newToken"
            type="success"
            title="Token created — copy it now"
            message="This token will not be shown again. Store it securely."
        >
            <template #action>
                <div class="mt-2 flex items-center gap-2">
                    <code class="flex-1 overflow-auto rounded bg-green-100 px-3 py-1.5 font-mono text-xs text-green-800 dark:bg-green-900/30 dark:text-green-300">
                        {{ newToken }}
                    </code>
                    <button
                        class="shrink-0 rounded p-1.5 hover:bg-green-200 dark:hover:bg-green-900/40"
                        @click="copyToken"
                    >
                        <ClipboardDocumentIcon class="size-4 text-green-700 dark:text-green-400" />
                    </button>
                    <span v-if="copiedToken" class="text-xs text-green-600">Copied!</span>
                </div>
            </template>
        </Alert>

        <!-- Token table -->
        <Card>
            <Table :columns="columns" :rows="tokens">
                <template #cell-name="{ row }">
                    <div class="flex items-center gap-2">
                        <KeyIcon class="size-4 shrink-0 text-gray-400" />
                        <span class="font-medium text-gray-900 dark:text-gray-100">{{ row.name }}</span>
                    </div>
                </template>
                <template #cell-abilities="{ row }">
                    <div class="flex flex-wrap gap-1">
                        <span
                            v-for="ability in (row.abilities ?? ['*'])"
                            :key="ability"
                            class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs text-gray-600 dark:bg-gray-800 dark:text-gray-400"
                        >{{ ability }}</span>
                    </div>
                </template>
                <template #cell-last_used="{ row }">
                    <span class="text-sm text-gray-500">
                        {{ row.last_used_at ? new Date(row.last_used_at).toLocaleDateString() : 'Never' }}
                    </span>
                </template>
                <template #cell-expires="{ row }">
                    <span class="text-sm" :class="row.expires_at && new Date(row.expires_at) < new Date() ? 'text-red-500' : 'text-gray-500'">
                        {{ row.expires_at ? new Date(row.expires_at).toLocaleDateString() : 'Never' }}
                    </span>
                </template>
                <template #cell-actions="{ row }">
                    <button
                        class="rounded p-1.5 text-gray-400 hover:bg-gray-100 hover:text-red-600 dark:hover:bg-gray-700"
                        title="Revoke"
                        @click="openRevokeModal(row)"
                    >
                        <TrashIcon class="size-4" />
                    </button>
                </template>
                <template #empty>
                    <div class="py-12 text-center">
                        <KeyIcon class="mx-auto size-10 text-gray-300 dark:text-gray-600" />
                        <p class="mt-3 text-sm text-gray-500">No API tokens yet. Create one to get started.</p>
                    </div>
                </template>
            </Table>
        </Card>

        <!-- Create modal -->
        <Modal :show="showCreateModal" title="Create API Token" @close="showCreateModal = false">
            <form @submit.prevent="submitCreate" class="space-y-4">
                <Input
                    v-model="createForm.name"
                    label="Token name"
                    placeholder="e.g. CI/CD pipeline"
                    :error="createForm.errors.name"
                />
                <Input
                    v-model="createForm.expires_in_days"
                    label="Expires in (days)"
                    type="number"
                    placeholder="Leave blank for no expiry"
                    :error="createForm.errors.expires_in_days"
                />
                <div class="flex justify-end gap-3">
                    <Button type="button" variant="secondary" @click="showCreateModal = false">Cancel</Button>
                    <Button type="submit" :loading="createForm.processing">Create token</Button>
                </div>
            </form>
        </Modal>

        <!-- Revoke confirm modal -->
        <Modal :show="showRevokeModal" title="Revoke Token" @close="showRevokeModal = false">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Revoking <strong>{{ revokingToken?.name }}</strong> will immediately invalidate it.
                Any apps using it will stop working.
            </p>
            <div class="mt-5 flex justify-end gap-3">
                <Button variant="secondary" @click="showRevokeModal = false">Cancel</Button>
                <Button variant="danger" :loading="revokeForm.processing" @click="confirmRevoke">Revoke</Button>
            </div>
        </Modal>

    </div>
</template>
