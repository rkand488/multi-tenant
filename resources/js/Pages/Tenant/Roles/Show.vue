<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import Card from '@/Components/UI/Card.vue';
import Button from '@/Components/UI/Button.vue';
import Modal from '@/Components/UI/Modal.vue';
import Alert from '@/Components/UI/Alert.vue';
import { useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { ShieldCheckIcon, LockOpenIcon, LockClosedIcon, TrashIcon } from '@heroicons/vue/24/outline';

defineOptions({ layout: TenantLayout });

const props = defineProps({
    role:            { type: Object, required: true },
    allPermissions:  { type: Array,  default: () => [] },  // available permissions
    canEdit:         { type: Boolean, default: false },
    canDelete:       { type: Boolean, default: false },
});

// ── Permission sync ───────────────────────────────────────────────────────────
const permissionsForm = useForm({
    permissions: [...(props.role.permissions ?? [])],
});

const togglePermission = (perm) => {
    const idx = permissionsForm.permissions.indexOf(perm);
    if (idx === -1) {
        permissionsForm.permissions.push(perm);
    } else {
        permissionsForm.permissions.splice(idx, 1);
    }
};

const isGranted = (perm) => permissionsForm.permissions.includes(perm);

const submitPermissions = () => {
    permissionsForm.put(route('tenant.roles.update', props.role.id));
};

// ── Delete ────────────────────────────────────────────────────────────────────
const showDeleteModal = ref(false);
const deleteForm = useForm({});

const confirmDelete = () => {
    deleteForm.delete(route('tenant.roles.destroy', props.role.id), {
        onSuccess: () => router.visit(route('tenant.roles.index')),
    });
};

// ── Grouped permissions ───────────────────────────────────────────────────────
const permissionGroups = computed(() => {
    const groups = {};
    for (const perm of props.allPermissions) {
        const [group] = perm.split('.');
        if (!groups[group]) { groups[group] = []; }
        groups[group].push(perm);
    }
    return groups;
});
</script>

<template>
    <div class="space-y-5">

        <!-- Header -->
        <div class="flex items-start justify-between">
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-lg bg-indigo-100 dark:bg-indigo-900/30">
                    <ShieldCheckIcon class="size-5 text-indigo-600 dark:text-indigo-400" />
                </div>
                <div>
                    <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ role.name }}</h1>
                    <p v-if="role.description" class="mt-0.5 text-sm text-gray-500">{{ role.description }}</p>
                </div>
            </div>
            <Button
                v-if="canDelete && !role.is_system"
                variant="danger"
                @click="showDeleteModal = true"
                class="flex items-center gap-1.5"
            >
                <TrashIcon class="size-4" />
                Delete Role
            </Button>
        </div>

        <Alert
            v-if="role.is_system"
            type="info"
            message="This is a system role. Its permissions are managed by the platform and cannot be modified."
        />

        <!-- Permissions card -->
        <Card v-if="!role.is_system">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Permissions</h2>
                <Button size="sm" :loading="permissionsForm.processing" @click="submitPermissions">
                    Save permissions
                </Button>
            </div>

            <div v-if="Object.keys(permissionGroups).length" class="space-y-5">
                <div v-for="(perms, group) in permissionGroups" :key="group">
                    <h3 class="mb-2 text-xs font-medium uppercase tracking-wide text-gray-500 capitalize">{{ group }}</h3>
                    <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                        <label
                            v-for="perm in perms"
                            :key="perm"
                            class="flex cursor-pointer items-center gap-2.5 rounded-lg border border-gray-200 p-3 transition hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800"
                            :class="isGranted(perm) ? 'border-indigo-300 bg-indigo-50 dark:border-indigo-700 dark:bg-indigo-900/20' : ''"
                        >
                            <input
                                type="checkbox"
                                :checked="isGranted(perm)"
                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                @change="togglePermission(perm)"
                            />
                            <div class="flex items-center gap-1.5">
                                <component
                                    :is="isGranted(perm) ? LockOpenIcon : LockClosedIcon"
                                    class="size-3.5"
                                    :class="isGranted(perm) ? 'text-indigo-500' : 'text-gray-400'"
                                />
                                <span class="text-sm text-gray-700 dark:text-gray-300">{{ perm }}</span>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <p v-else class="text-sm text-gray-400">No permissions defined in the system.</p>
        </Card>

        <!-- System role: show current permissions as read-only -->
        <Card v-else>
            <h2 class="mb-4 text-sm font-semibold text-gray-700 dark:text-gray-300">Assigned Permissions</h2>
            <div class="flex flex-wrap gap-2">
                <span
                    v-for="perm in role.permissions"
                    :key="perm"
                    class="inline-flex items-center rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-medium text-indigo-700 dark:bg-indigo-900/20 dark:text-indigo-400"
                >{{ perm }}</span>
                <span v-if="!role.permissions?.length" class="text-sm text-gray-400">No permissions assigned.</span>
            </div>
        </Card>

        <!-- Delete modal -->
        <Modal :show="showDeleteModal" title="Delete Role" @close="showDeleteModal = false">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Are you sure you want to delete the role <strong>{{ role.name }}</strong>?
                Users with this role will need to be reassigned.
            </p>
            <div class="mt-5 flex justify-end gap-3">
                <Button variant="secondary" @click="showDeleteModal = false">Cancel</Button>
                <Button variant="danger" :loading="deleteForm.processing" @click="confirmDelete">Delete</Button>
            </div>
        </Modal>

    </div>
</template>
