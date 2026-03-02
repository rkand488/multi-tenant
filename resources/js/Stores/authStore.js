import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { authService, tokenStorage } from '@/Services';

/**
 * Authentication store.
 *
 * Manages the authenticated user, Sanctum API token, login/register/logout
 * flows, and profile mutations.
 *
 * For Inertia pages the canonical user comes from the shared `auth.user` prop
 * — this store layers API-level actions on top and keeps its own copy in sync
 * so non-Inertia contexts (e.g. mobile apps, API clients) can also use it.
 */
export const useAuthStore = defineStore('auth', () => {
    // ── State ─────────────────────────────────────────────────────────────────
    /** @type {import('vue').Ref<object|null>} */
    const user    = ref(null);
    /** Sanctum bearer token — persisted to localStorage */
    const token   = ref(localStorage.getItem('auth_token') ?? null);
    const loading = ref(false);
    const error   = ref(null);

    // ── Getters ───────────────────────────────────────────────────────────────
    const isAuthenticated = computed(() => !!user.value || !!token.value);
    const isSuperAdmin    = computed(() => user.value?.roles?.includes('super_admin') ?? false);

    /**
     * Role within the current tenant workspace.
     * @returns {string|null}
     */
    const tenantRole = computed(() => user.value?.tenant_role ?? null);

    const initials = computed(() => {
        const name = user.value?.name ?? '';
        return name
            .split(' ')
            .map((n) => n[0] ?? '')
            .slice(0, 2)
            .join('')
            .toUpperCase() || '?';
    });

    // ── Helpers ───────────────────────────────────────────────────────────────
    function setToken(newToken) {
        token.value = newToken;
        if (newToken) {
            tokenStorage.set(newToken);
            window.axios.defaults.headers.common['Authorization'] = `Bearer ${newToken}`;
        } else {
            tokenStorage.clear();
            delete window.axios.defaults.headers.common['Authorization'];
        }
    }

    function clearError() {
        error.value = null;
    }

    /** Seed state from Inertia shared props (call once on app boot). */
    function syncFromInertia() {
        try {
            const page      = usePage();
            const inertiaUser = page.props?.auth?.user;
            if (inertiaUser) {
                user.value = inertiaUser;
            }
        } catch {
            // usePage() may not be available outside component context — safe to ignore
        }
    }

    // ── Actions ───────────────────────────────────────────────────────────────

    /**
     * Authenticate via the API and store the bearer token.
     * @param {{ email: string, password: string, remember?: boolean }} credentials
     * @returns {Promise<boolean>}
     */
    async function login(credentials) {
        loading.value = true;
        error.value   = null;
        try {
            const result = await authService.login(credentials);
            user.value = result.user ?? result.data ?? null;
            setToken(result.token ?? result.data?.token ?? null);
            return true;
        } catch (err) {
            error.value = err.message ?? 'Login failed.';
            return false;
        } finally {
            loading.value = false;
        }
    }

    /**
     * Register a new account.
     * @param {{ name: string, email: string, password: string, password_confirmation: string }} payload
     * @returns {Promise<boolean>}
     */
    async function register(payload) {
        loading.value = true;
        error.value   = null;
        try {
            const result = await authService.register(payload);
            user.value = result.user ?? result.data ?? null;
            setToken(result.token ?? result.data?.token ?? null);
            return true;
        } catch (err) {
            error.value = err.message ?? 'Registration failed.';
            return false;
        } finally {
            loading.value = false;
        }
    }

    /**
     * Log out via API and clear local state.
     * @param {{ allDevices?: boolean }} options
     */
    async function logout({ allDevices = false } = {}) {
        loading.value = true;
        error.value   = null;
        try {
            if (allDevices) {
                await authService.logoutAll();
            } else {
                await authService.logout();
            }
        } catch {
            // Proceed with local cleanup even if the request fails
        } finally {
            user.value  = null;
            setToken(null);
            loading.value = false;
            router.visit(route('login'));
        }
    }

    /**
     * Refresh the authenticated user from the API.
     * @returns {Promise<object|null>}
     */
    async function fetchMe() {
        loading.value = true;
        error.value   = null;
        try {
            user.value = await authService.me();
            return user.value;
        } catch (err) {
            error.value = err.message ?? 'Failed to load user.';
            return null;
        } finally {
            loading.value = false;
        }
    }

    /**
     * Update the authenticated user's profile.
     * Form validation is handled by Inertia `useForm`; this method is
     * intended for direct API use (e.g. mobile / headless clients).
     * @param {{ name?: string, email?: string }} payload
     * @returns {Promise<boolean>}
     */
    async function updateProfile(payload) {
        loading.value = true;
        error.value   = null;
        try {
            const updated = await authService.updateProfile(payload);
            user.value = { ...user.value, ...updated };
            return true;
        } catch (err) {
            error.value = err.message ?? 'Failed to update profile.';
            return false;
        } finally {
            loading.value = false;
        }
    }

    /**
     * Update the authenticated user's password.
     * @param {{ current_password: string, password: string, password_confirmation: string }} payload
     * @returns {Promise<boolean>}
     */
    async function updatePassword(payload) {
        loading.value = true;
        error.value   = null;
        try {
            await authService.updatePassword(payload);
            return true;
        } catch (err) {
            error.value = err.message ?? 'Failed to update password.';
            return false;
        } finally {
            loading.value = false;
        }
    }

    // Restore token headers on store init (page refresh scenario)
    if (token.value) {
        tokenStorage.set(token.value);
        window.axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`;
    }

    return {
        // state
        user,
        token,
        loading,
        error,
        // getters
        isAuthenticated,
        isSuperAdmin,
        tenantRole,
        initials,
        // actions
        syncFromInertia,
        login,
        register,
        logout,
        fetchMe,
        updateProfile,
        updatePassword,
        clearError,
    };
});
