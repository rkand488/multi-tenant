<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import Card from '@/Components/UI/Card.vue';
import Input from '@/Components/UI/Input.vue';
import Button from '@/Components/UI/Button.vue';
import Modal from '@/Components/UI/Modal.vue';
import Alert from '@/Components/UI/Alert.vue';
import { useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import {
    PlusIcon,
    PencilSquareIcon,
    TrashIcon,
    ShieldCheckIcon,
} from '@heroicons/vue/24/outline';

defineOptions({ layout: TenantLayout });

const props = defineProps({
    roles:                { type: Array, default: () => [] },
    availablePermissions: { type: Array, default: () => [] },
});

// ── Create / Edit modal ──────────────────────────────────────────────────────
const editingRole = ref(null);
const showFormModal = ref(false);

const form = useForm({
    name:        '',
    description: '',
    permissions: [],
});

const openCreate = () => {
    editingRole.value = null;
    form.name        = '';
    form.description = '';
    form.permissions = [];
    form.clearErrors();
    showFormModal.value = true;
};

const openEdit = (role) => {
    editingRole.value = role;
    form.name         = role.name;
    form.description  = role.description ?? '';
    form.permissions  = [...(role.permissions ?? [])];
    form.clearErrors();
    showFormModal.value = true;
};

const closeFormModal = () => {
    showFormModal.value = false;
    editingRole.value = null;
};

const submitForm = () => {
    if (editingRole.value) {
        form.put(route('tenant.roles.update', editingRole.value.id), {
            onSuccess: closeFormModal,
        });
    } else {
        form.post(route('tenant.roles.store'), {
            onSuccess: closeFormModal,
        });
    }
};

// ── Delete modal ─────────────────────────────────────────────────────────────
const showDeleteModal = ref(false);
const deletingRole    = ref(null);
const deleteForm      = useForm({});

const openDelete = (role) => {
    deletingRole.value = role;
    showDeleteModal.value = true;
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    deletingRole.value = null;
};

const confirmDelete = () => {
    deleteForm.delete(route('tenant.roles.destroy', deletingRole.value.id), {
        onSuccess: closeDeleteModal,
    });
};

// ── Permission toggle ────────────────────────────────────────────────────────
const togglePermission = (perm) => {
    const idx = form.permissions.indexOf(perm);
    if (idx >= 0) {
        form.permissions.splice(idx, 1);
    } else {
        form.permissions.push(perm);
    }
};

// ── Group permissions by prefix ─────────────────────────────────────────────
const permissionGroups = computed(() => {
    const groups = {};
    props.availablePermissions.forEach((perm) => {
        const [group] = perm.split('.');
        if (!groups[group]) { groups[group] = []; }
        groups[group].push(perm);
    });
    return groups;
});
</script>

<template>
    <div class="space-y-6">

        <!-- ── Page header ───────────────────────────────────────────── -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Roles & Permissions</h1>
                <p class="mt-1 text-sm text-gray-500">Manage what team members can do in your workspace.</p>
            </div>
            <Button @click="openCreate">
                <PlusIcon class="mr-1.5 size-4" />
                New Role
            </Button>
        </div>

        <!-- ── Empty state ───────────────────────────────────────────── -->
        <div
            v-if="!roles.length"
            class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-gray-200 py-16 dark:border-gray-700"
        >
            <ShieldCheckIcon class="size-12 text-gray-300 dark:text-gray-600" />
            <p class="mt-3 font-medium text-gray-500 dark:text-gray-400">No roles yet</p>
            <p class="mt-1 text-sm text-gray-400">Create a role to control access for your team members.</p>
            <Button class="mt-5" @click="openCreate">Create first role</Button>
        </div>

        <!-- ── Roles grid ────────────────────────────────────────────── -->
        <div v-else class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            <Card
                v-for="role in roles"
                :key="role.id"
                class="flex flex-col"
            >
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-indigo-50 dark:bg-indigo-900/20">
                            <ShieldCheckIcon class="size-5 text-indigo-600 dark:text-indigo-400" />
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900 dark:text-gray-100">{{ role.name }}</p>
                            <p class="text-xs text-gray-400">{{ role.users_count ?? 0 }} member{{ (role.users_count ?? 0) !== 1 ? 's' : '' }}</p>
                        </div>
                    </div>
                    <div class="flex shrink-0 gap-1">
                        <button
                            class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-indigo-600 dark:hover:bg-gray-800"
                            @click="openEdit(role)"
                        >
                            <PencilSquareIcon class="size-4" />
                        </button>
                        <button
                            class="rounded-lg p-1.5 text-gray-400 transition hover:bg-red-50 hover:text-red-500 dark:hover:bg-red-900/20"
                            @click="openDelete(role)"
                        >
                            <TrashIcon class="size-4" />
                        </button>
                    </div>
                </div>

                <p v-if="role.description" class="mt-3 text-sm text-gray-500 dark:text-gray-400">
                    {{ role.description }}
                </p>

                <!-- Permissions chips -->
                <div v-if="role.permissions?.length" class="mt-4 flex flex-wrap gap-1.5">
                    <span
                        v-for="perm in role.permissions.slice(0, 6)"
                        :key="perm"
                        class="inline-flex rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300"
                    >
                        {{ perm }}
                    </span>
                    <span
                        v-if="role.permissions.length > 6"
                        class="inline-flex rounded-full bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-600 dark:bg-indigo-900/20 dark:text-indigo-400"
                    >
                        +{{ role.permissions.length - 6 }} more
                    </span>
                </div>
                <p v-else class="mt-4 text-xs text-gray-400">No permissions assigned.</p>
            </Card>
        </div>
    </div>

    <!-- ── Create / Edit modal ──────────────────────────────────────────── -->
    <Modal :show="showFormModal" @close="closeFormModal">
        <template #title>{{ editingRole ? 'Edit Role' : 'Create Role' }}</template>

        <div class="space-y-4">
            <Input
                id="role-name"
                v-model="form.name"
                label="Role name"
                placeholder="e.g. Editor"
                :error="form.errors.name"
                required
            />

            <Input
                id="role-description"
                v-model="form.description"
                label="Description (optional)"
                placeholder="What can this role do?"
                multiline
                :rows="2"
                :error="form.errors.description"
            />

            <!-- Permissions -->
            <div v-if="availablePermissions.length">
                <p class="mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Permissions</p>
                <div class="max-h-56 space-y-4 overflow-y-auto rounded-xl border border-gray-100 p-3 dark:border-gray-800">
                    <div
                        v-for="(perms, group) in permissionGroups"
                        :key="group"
                    >
                        <p class="mb-1.5 text-xs font-semibold uppercase tracking-wide text-gray-400">{{ group }}</p>
                        <div class="grid grid-cols-2 gap-1.5">
                            <label
                                v-for="perm in perms"
                                :key="perm"
                                class="flex cursor-pointer items-center gap-2 rounded-lg px-2.5 py-1.5 text-xs transition"
                                :class="form.permissions.includes(perm)
                                    ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/20 dark:text-indigo-300'
                                    : 'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800'"
                            >
                                <input
                                    type="checkbox"
                                    :checked="form.permissions.includes(perm)"
                                    class="size-3.5 rounded accent-indigo-600"
                                    @change="togglePermission(perm)"
                                />
                                {{ perm.split('.')[1] ?? perm }}
                            </label>
                        </div>
                    </div>
                </div>
                <Alert v-if="form.errors.permissions" variant="error" class="mt-2">{{ form.errors.permissions }}</Alert>
            </div>
        </div>

        <template #footer>
            <div class="flex justify-end gap-3">
                <Button variant="secondary" @click="closeFormModal">Cancel</Button>
                <Button :loading="form.processing" @click="submitForm">
                    {{ editingRole ? 'Save Changes' : 'Create Role' }}
                </Button>
            </div>
        </template>
    </Modal>

    <!-- ── Delete confirm modal ─────────────────────────────────────────── -->
    <Modal :show="showDeleteModal" @close="closeDeleteModal">
        <template #title>Delete Role</template>

        <p class="text-sm text-gray-600 dark:text-gray-400">
            Are you sure you want to delete
            <span class="font-semibold text-gray-900 dark:text-gray-100">{{ deletingRole?.name }}</span>?
            Members with this role will lose their permissions.
        </p>

        <template #footer>
            <div class="flex justify-end gap-3">
                <Button variant="secondary" @click="closeDeleteModal">Cancel</Button>
                <Button variant="danger" :loading="deleteForm.processing" @click="confirmDelete">Delete Role</Button>
            </div>
        </template>
    </Modal>
</template>
