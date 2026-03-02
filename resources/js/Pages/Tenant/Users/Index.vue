<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import Card from '@/Components/UI/Card.vue';
import Table from '@/Components/UI/Table.vue';
import Pagination from '@/Components/UI/Pagination.vue';
import Button from '@/Components/UI/Button.vue';
import Modal from '@/Components/UI/Modal.vue';
import Alert from '@/Components/UI/Alert.vue';
import Select from '@/Components/UI/Select.vue';
import Dropdown from '@/Components/UI/Dropdown.vue';
import DropdownItem from '@/Components/UI/DropdownItem.vue';
import { Link, router, useForm, usePage } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { MagnifyingGlassIcon } from '@heroicons/vue/24/outline';
import { useDebounceFn } from '@vueuse/core';

defineOptions({ layout: TenantLayout });

const page = usePage();
const currentUserId = computed(() => page.props.auth?.user?.id);

const props = defineProps({
    users: { type: Object, required: true },      // paginator
    roles: { type: Array,  default: () => [] },
    filters: { type: Object, default: () => ({}) },
    canInvite: { type: Boolean, default: true },
});

// ── Filters ────────────────────────────────────────────────────────────────
const search = ref(props.filters.search ?? '');
const role   = ref(props.filters.role   ?? '');

const applyFilters = () => {
    router.get(route('tenant.users.index'), {
        search: search.value,
        role: role.value,
    }, { preserveState: true, replace: true });
};

const debouncedSearch = useDebounceFn(applyFilters, 350);
watch(search, debouncedSearch);
watch(role, applyFilters);

const handlePageChange = (p) => {
    router.get(route('tenant.users.index'), { ...props.filters, page: p }, { preserveState: true });
};

// ── Role change ────────────────────────────────────────────────────────────
const showRoleModal = ref(false);
const editingUser   = ref(null);
const roleForm = useForm({ role_id: '' });

const openRoleModal = (user) => {
    editingUser.value  = user;
    roleForm.role_id   = user.role_id ?? '';
    showRoleModal.value = true;
};

const submitRoleChange = () => {
    roleForm.patch(route('tenant.users.update', editingUser.value.id), {
        onSuccess: () => (showRoleModal.value = false),
    });
};

// ── Remove user ────────────────────────────────────────────────────────────
const showRemoveModal  = ref(false);
const removingUser     = ref(null);
const removeForm       = useForm({});

const openRemoveModal = (user) => {
    removingUser.value  = user;
    showRemoveModal.value = true;
};

const confirmRemove = () => {
    removeForm.delete(route('tenant.users.destroy', removingUser.value.id), {
        onSuccess: () => (showRemoveModal.value = false),
    });
};

// ── Resend invite ──────────────────────────────────────────────────────────
const resendInvite = (user) => {
    router.post(route('tenant.users.resend-invite', user.id));
};

const roleOptions = computed(() => props.roles.map((r) => ({ value: r.id, label: r.name })));

const columns = [
    { key: 'name',       label: 'Member' },
    { key: 'role',       label: 'Role',       class: 'w-36' },
    { key: 'status',     label: 'Status',     class: 'w-28' },
    { key: 'joined',     label: 'Joined',     class: 'w-36' },
    { key: 'last_seen',  label: 'Last seen',  class: 'w-36' },
    { key: 'actions',    label: '',           class: 'w-16 text-right' },
];
</script>

<template>
    <div class="space-y-5">

        <!-- ── Page heading ────────────────────────────────────────────── -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">Team Members</h1>
                <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                    Manage who has access to your workspace.
                </p>
            </div>
            <Link v-if="canInvite" :href="route('tenant.users.create')">
                <Button size="sm">+ Invite member</Button>
            </Link>
        </div>

        <!-- ── Toolbar ─────────────────────────────────────────────────── -->
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="relative w-full max-w-xs">
                <MagnifyingGlassIcon class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-gray-400" />
                <input
                    v-model="search"
                    type="search"
                    placeholder="Search members…"
                    class="h-9 w-full rounded-lg border border-gray-200 bg-white pl-9 pr-3 text-sm placeholder-gray-400 shadow-sm focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500"
                />
            </div>

            <select
                v-model="role"
                class="h-9 rounded-lg border border-gray-200 bg-white px-3 text-sm shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
            >
                <option value="">All Roles</option>
                <option v-for="r in roles" :key="r.id" :value="r.id">{{ r.name }}</option>
            </select>
        </div>

        <!-- ── Table ───────────────────────────────────────────────────── -->
        <Card :padded="false">
            <Table :columns="columns" :rows="users.data ?? []">
                <template #row="{ row }">
                    <!-- Member -->
                    <td class="whitespace-nowrap px-4 py-3">
                        <div class="flex items-center gap-3">
                            <div class="flex size-8 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-xs font-bold text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300">
                                {{ row.name?.[0]?.toUpperCase() }}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ row.name }}
                                    <span v-if="row.id === currentUserId" class="ml-1.5 text-xs font-normal text-gray-400">(you)</span>
                                </p>
                                <p class="text-xs text-gray-400">{{ row.email }}</p>
                            </div>
                        </div>
                    </td>

                    <!-- Role -->
                    <td class="whitespace-nowrap px-4 py-3">
                        <span class="inline-flex items-center rounded-full bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300">
                            {{ row.role_name ?? 'Member' }}
                        </span>
                    </td>

                    <!-- Status -->
                    <td class="whitespace-nowrap px-4 py-3">
                        <span
                            class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium"
                            :class="row.status === 'active'
                                ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400'
                                : 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400'"
                        >
                            {{ row.status === 'active' ? 'Active' : 'Invited' }}
                        </span>
                    </td>

                    <!-- Joined -->
                    <td class="px-4 py-3 text-sm text-gray-400">{{ row.joined_at ?? '—' }}</td>

                    <!-- Last seen -->
                    <td class="px-4 py-3 text-sm text-gray-400">{{ row.last_seen_at ?? 'Never' }}</td>

                    <!-- Actions -->
                    <td class="px-4 py-3 text-right">
                        <Dropdown v-if="row.id !== currentUserId" align="right" width="48">
                            <template #trigger>
                                <button
                                    type="button"
                                    class="inline-flex items-center rounded px-2 py-0.5 text-xs text-gray-500 ring-1 ring-inset ring-gray-200 hover:bg-gray-50 dark:ring-gray-700 dark:hover:bg-gray-800"
                                >
                                    ⋯
                                </button>
                            </template>
                            <template #items>
                                <DropdownItem @click="openRoleModal(row)">Change role</DropdownItem>
                                <DropdownItem
                                    v-if="row.status === 'invited'"
                                    @click="resendInvite(row)"
                                >
                                    Resend invite
                                </DropdownItem>
                                <DropdownItem variant="danger" @click="openRemoveModal(row)">
                                    Remove from workspace
                                </DropdownItem>
                            </template>
                        </Dropdown>
                    </td>
                </template>

                <template #empty>
                    No team members found. <Link :href="route('tenant.users.create')" class="text-indigo-600 hover:underline">Invite someone</Link>.
                </template>
            </Table>
        </Card>

        <!-- Pagination -->
        <Pagination
            v-if="users.meta && users.meta.last_page > 1"
            :links="users.links ?? []"
            :meta="users.meta"
            @change="handlePageChange"
        />
    </div>

    <!-- ── Change role modal ──────────────────────────────────────────── -->
    <Modal :show="showRoleModal" title="Change Role" max-width="sm" @close="showRoleModal = false">
        <form class="space-y-4" @submit.prevent="submitRoleChange">
            <p class="text-sm text-gray-600 dark:text-gray-300">
                Updating role for <strong>{{ editingUser?.name }}</strong>.
            </p>
            <Select
                id="user-role"
                v-model="roleForm.role_id"
                label="Role"
                :options="roleOptions"
                :error="roleForm.errors.role_id"
                required
            />
        </form>
        <template #footer>
            <Button :loading="roleForm.processing" @click="submitRoleChange">Save</Button>
            <Button variant="secondary" @click="showRoleModal = false">Cancel</Button>
        </template>
    </Modal>

    <!-- ── Remove user modal ─────────────────────────────────────────── -->
    <Modal :show="showRemoveModal" title="Remove Member" max-width="sm" @close="showRemoveModal = false">
        <p class="text-sm text-gray-600 dark:text-gray-300">
            Remove <strong>{{ removingUser?.name }}</strong> from this workspace?
            They will lose access immediately but their data will remain.
        </p>
        <template #footer>
            <Button variant="danger" :loading="removeForm.processing" @click="confirmRemove">Remove</Button>
            <Button variant="secondary" @click="showRemoveModal = false">Cancel</Button>
        </template>
    </Modal>
</template>
