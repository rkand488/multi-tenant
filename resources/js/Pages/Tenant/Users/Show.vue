<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import Card from '@/Components/UI/Card.vue';
import Button from '@/Components/UI/Button.vue';
import Alert from '@/Components/UI/Alert.vue';
import Modal from '@/Components/UI/Modal.vue';
import Select from '@/Components/UI/Select.vue';
import { useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { UserIcon, ShieldCheckIcon, TrashIcon, EnvelopeIcon } from '@heroicons/vue/24/outline';

defineOptions({ layout: TenantLayout });

const props = defineProps({
    user:      { type: Object, required: true },
    roles:     { type: Array,  default: () => [] },
    canEdit:   { type: Boolean, default: false },
    canDelete: { type: Boolean, default: false },
});

// ── Custom role assignment ───────────────────────────────────────────────────
const showRoleModal = ref(false);
const roleForm = useForm({ role_id: props.user.role_id ?? '' });

const submitRoleChange = () => {
    roleForm.patch(route('tenant.users.update', props.user.id), {
        onSuccess: () => (showRoleModal.value = false),
    });
};

// ── Remove user ───────────────────────────────────────────────────────────────
const showRemoveModal = ref(false);
const removeForm = useForm({});

const confirmRemove = () => {
    removeForm.delete(route('tenant.users.destroy', props.user.id), {
        onSuccess: () => router.visit(route('tenant.users.index')),
    });
};

// ── Resend invite ─────────────────────────────────────────────────────────────
const resendInvite = () => {
    router.post(route('tenant.users.resend-invite', props.user.id));
};

const roleBadgeClass = (role) => ({
    tenant_owner: 'bg-purple-50 text-purple-700 dark:bg-purple-900/20 dark:text-purple-400',
    tenant_user:  'bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400',
}[role] ?? 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400');

const roleOptions = computed(() => [
    { value: '', label: '— No custom role —' },
    ...props.roles.map((r) => ({ value: r.id, label: r.name })),
]);
</script>

<template>
    <div class="space-y-5">

        <!-- Header -->
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ user.name }}</h1>
                <p class="mt-1 flex items-center gap-1.5 text-sm text-gray-500">
                    <EnvelopeIcon class="size-4" />
                    {{ user.email }}
                </p>
            </div>
            <div v-if="canEdit || canDelete" class="flex gap-2">
                <Button v-if="canEdit" variant="secondary" @click="showRoleModal = true" class="flex items-center gap-1.5">
                    <ShieldCheckIcon class="size-4" />
                    Change Role
                </Button>
                <Button v-if="canDelete" variant="danger" @click="showRemoveModal = true" class="flex items-center gap-1.5">
                    <TrashIcon class="size-4" />
                    Remove
                </Button>
            </div>
        </div>

        <!-- User details card -->
        <Card>
            <dl class="grid gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Role</dt>
                    <dd class="mt-1">
                        <span :class="[roleBadgeClass(user.role), 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium']">
                            {{ user.role_label ?? user.role }}
                        </span>
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Member since</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                        {{ new Date(user.created_at).toLocaleDateString() }}
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Email verified</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                        {{ user.email_verified_at ? new Date(user.email_verified_at).toLocaleDateString() : 'Not yet verified' }}
                    </dd>
                </div>
            </dl>
        </Card>

        <!-- Resend invite (if not yet verified) -->
        <Alert
            v-if="!user.email_verified_at && canEdit"
            type="warning"
            title="Invitation pending"
            message="This user has not yet accepted their invitation."
        >
            <template #action>
                <Button size="sm" variant="secondary" @click="resendInvite">
                    Resend invitation
                </Button>
            </template>
        </Alert>

        <!-- Custom role modal -->
        <Modal :show="showRoleModal" title="Change Custom Role" @close="showRoleModal = false">
            <form @submit.prevent="submitRoleChange" class="space-y-4">
                <Select
                    v-model="roleForm.role_id"
                    label="Custom Role"
                    :options="roleOptions"
                    :error="roleForm.errors.role_id"
                />
                <div class="flex justify-end gap-3">
                    <Button type="button" variant="secondary" @click="showRoleModal = false">Cancel</Button>
                    <Button type="submit" :loading="roleForm.processing">Save</Button>
                </div>
            </form>
        </Modal>

        <!-- Remove confirm modal -->
        <Modal :show="showRemoveModal" title="Remove User" @close="showRemoveModal = false">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Are you sure you want to remove <strong>{{ user.name }}</strong> from the workspace?
                This action cannot be undone.
            </p>
            <div class="mt-5 flex justify-end gap-3">
                <Button variant="secondary" @click="showRemoveModal = false">Cancel</Button>
                <Button variant="danger" :loading="removeForm.processing" @click="confirmRemove">Remove</Button>
            </div>
        </Modal>

    </div>
</template>
