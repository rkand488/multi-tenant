import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { tenantService, userService } from '@/Services';

/**
 * Tenant store.
 *
 * Manages the current workspace context — tenant identity, team members,
 * roles & permissions, team settings, and activity logs.
 *
 * API base: /api/v1/tenant/*  (requires auth:sanctum + tenant + tenant.active)
 */
export const useTenantStore = defineStore('tenant', () => {
    // ── State ─────────────────────────────────────────────────────────────────

    /** @type {import('vue').Ref<object|null>} Current workspace */
    const tenant   = ref(null);

    /**
     * Team members paginator.
     * @type {import('vue').Ref<{ data: object[], meta: object, links: object[] }>}
     */
    const users    = ref({ data: [], meta: {}, links: [] });

    /** @type {import('vue').Ref<object[]>} Role/permission records */
    const roles    = ref([]);

    /** @type {import('vue').Ref<object|null>} Workspace settings */
    const settings = ref(null);

    /**
     * Activity log paginator.
     * @type {import('vue').Ref<{ data: object[], meta: object, links: object[] }>}
     */
    const activityLogs = ref({ data: [], meta: {}, links: [] });

    const loading = ref(false);
    const error   = ref(null);

    // ── Per-action loading flags ───────────────────────────────────────────────
    const usersLoading    = ref(false);
    const rolesLoading    = ref(false);
    const settingsLoading = ref(false);
    const logsLoading     = ref(false);

    // ── Getters ───────────────────────────────────────────────────────────────

    const tenantName = computed(() => tenant.value?.name ?? 'My Workspace');
    const tenantSlug = computed(() => tenant.value?.slug ?? '');
    const planName   = computed(() => tenant.value?.plan_name ?? null);

    /** @returns {number} */
    const memberCount = computed(() => users.value.meta?.total ?? users.value.data.length);

    /** Find a user in the loaded list by id. */
    const findUser = computed(() => (id) =>
        users.value.data.find((u) => String(u.id) === String(id)) ?? null,
    );

    /** Check whether the store has fetched at least one page of users. */
    const hasUsers = computed(() => users.value.data.length > 0);

    // ── Helpers ───────────────────────────────────────────────────────────────

    function clearError() {
        error.value = null;
    }

    /** Seed tenant identity from Inertia shared props. */
    function syncFromInertia() {
        try {
            const page = usePage();
            if (page.props?.tenant) {
                tenant.value = page.props.tenant;
            }
        } catch {
            // Safe to ignore outside component context
        }
    }

    // ── Actions ───────────────────────────────────────────────────────────────

    /**
     * Fetch the current workspace details.
     * (The API returns the tenant record scoped to the caller's session.)
     * @returns {Promise<object|null>}
     */
    async function fetchTenant() {
        loading.value = true;
        error.value   = null;
        try {
            settings.value = await tenantService.getSettings();
            tenant.value   = { ...tenant.value, ...(settings.value?.tenant ?? {}) };
            return tenant.value;
        } catch (err) {
            error.value = err.message ?? 'Failed to load workspace.';
            return null;
        } finally {
            loading.value = false;
        }
    }

    /**
     * Update tenant/team settings.
     * @param {object} payload  e.g. { name, timezone }
     * @returns {Promise<boolean>}
     */
    async function updateSettings(payload) {
        settingsLoading.value = true;
        error.value           = null;
        try {
            const settingsId = settings.value?.id ?? 1;
            settings.value = await tenantService.updateSettings(settingsId, payload);
            // Reflect name change in the tenant object used by the sidebar
            if (payload.name) {
                tenant.value = { ...tenant.value, name: payload.name };
            }
            return true;
        } catch (err) {
            error.value = err.message ?? 'Failed to update settings.';
            return false;
        } finally {
            settingsLoading.value = false;
        }
    }

    // ── User management ───────────────────────────────────────────────────────

    /**
     * Fetch paginated team members.
     * @param {{ page?: number, search?: string, role?: string }} params
     * @returns {Promise<void>}
     */
    async function fetchUsers(params = {}) {
        usersLoading.value = true;
        error.value        = null;
        try {
            users.value = await userService.list(params);
        } catch (err) {
            error.value = err.message ?? 'Failed to load team members.';
        } finally {
            usersLoading.value = false;
        }
    }

    /**
     * Invite a new user (POST /api/v1/invitations).
     * @param {{ email: string, role_id: string|number, message?: string }} payload
     * @returns {Promise<boolean>}
     */
    async function inviteUser(payload) {
        usersLoading.value = true;
        error.value        = null;
        try {
            await tenantService.createInvitation(payload);
            return true;
        } catch (err) {
            error.value = err.message ?? 'Failed to send invitation.';
            return false;
        } finally {
            usersLoading.value = false;
        }
    }

    /**
     * Update a team member's role.
     * @param {string|number} userId
     * @param {{ role_id: string|number }} payload
     * @returns {Promise<boolean>}
     */
    async function updateUser(userId, payload) {
        usersLoading.value = true;
        error.value        = null;
        try {
            const updated = await tenantService.updateUserRole(userId, payload);
            // Patch updated user in the local list
            const idx = users.value.data.findIndex((u) => String(u.id) === String(userId));
            if (idx >= 0) {
                users.value.data.splice(idx, 1, updated);
            }
            return true;
        } catch (err) {
            error.value = err.message ?? 'Failed to update member.';
            return false;
        } finally {
            usersLoading.value = false;
        }
    }

    /**
     * Remove a team member.
     * @param {string|number} userId
     * @returns {Promise<boolean>}
     */
    async function removeUser(userId) {
        usersLoading.value = true;
        error.value        = null;
        try {
            await userService.remove(userId);
            users.value.data = users.value.data.filter(
                (u) => String(u.id) !== String(userId),
            );
            return true;
        } catch (err) {
            error.value = err.message ?? 'Failed to remove member.';
            return false;
        } finally {
            usersLoading.value = false;
        }
    }

    // ── Roles & permissions ───────────────────────────────────────────────────

    /**
     * Fetch  roles/permissions for the current tenant.
     * @returns {Promise<void>}
     */
    async function fetchRoles() {
        rolesLoading.value = true;
        error.value        = null;
        try {
            const result = await tenantService.getRoles();
            roles.value = result.data ?? result ?? [];
        } catch (err) {
            error.value = err.message ?? 'Failed to load roles.';
        } finally {
            rolesLoading.value = false;
        }
    }

    // ── Activity logs ─────────────────────────────────────────────────────────

    /**
     * Fetch paginated activity log entries.
     * @param {{ page?: number, search?: string, event?: string, causer_id?: string, date_from?: string, date_to?: string }} params
     * @returns {Promise<void>}
     */
    async function fetchActivityLogs(params = {}) {
        logsLoading.value = true;
        error.value       = null;
        try {
            activityLogs.value = await tenantService.getActivityLogs(params);
        } catch (err) {
            error.value = err.message ?? 'Failed to load activity log.';
        } finally {
            logsLoading.value = false;
        }
    }

    return {
        // state
        tenant,
        users,
        roles,
        settings,
        activityLogs,
        loading,
        error,
        usersLoading,
        rolesLoading,
        settingsLoading,
        logsLoading,
        // getters
        tenantName,
        tenantSlug,
        planName,
        memberCount,
        findUser,
        hasUsers,
        // actions
        syncFromInertia,
        fetchTenant,
        updateSettings,
        fetchUsers,
        inviteUser,
        updateUser,
        removeUser,
        fetchRoles,
        fetchActivityLogs,
        clearError,
    };
});
