import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import http from '@/Services/http.js';

/**
 * Notification store.
 *
 * Manages in-app notifications surfaced from either:
 *  a) Inertia shared props (`page.props.notifications`)
 *  b) A dedicated API endpoint `/api/v1/notifications` (when implemented)
 *
 * Provides mark-read/mark-all-read optimistic updates that can be
 * confirmed by a server round-trip.
 *
 * @typedef {{ id: string|number, title: string, body?: string, type?: 'info'|'success'|'warning'|'error', read: boolean, created_at: string, action_url?: string }} Notification
 */
export const useNotificationStore = defineStore('notifications', () => {
    // ── State ─────────────────────────────────────────────────────────────────

    /** @type {import('vue').Ref<Notification[]>} */
    const items   = ref([]);
    const loading = ref(false);
    const error   = ref(null);

    // ── Getters ───────────────────────────────────────────────────────────────

    const unreadCount     = computed(() => items.value.filter((n) => !n.read).length);
    const hasUnread       = computed(() => unreadCount.value > 0);
    const unreadBadge     = computed(() => unreadCount.value > 99 ? '99+' : unreadCount.value || null);
    const unreadItems     = computed(() => items.value.filter((n) => !n.read));
    const recentItems     = computed(() => [...items.value].slice(0, 20));

    /** @returns {Notification|null} */
    const findById = computed(() => (id) =>
        items.value.find((n) => String(n.id) === String(id)) ?? null,
    );

    // ── Helpers ───────────────────────────────────────────────────────────────

    function clearError() {
        error.value = null;
    }

    /**
     * Seed notifications from Inertia shared props.
     * Called once in the layout on mount.
     */
    function syncFromInertia() {
        try {
            const page = usePage();
            const fromProps = page.props?.notifications;
            if (Array.isArray(fromProps) && fromProps.length) {
                // Merge without duplicating
                const existingIds = new Set(items.value.map((n) => String(n.id)));
                const fresh = fromProps.filter((n) => !existingIds.has(String(n.id)));
                items.value.unshift(...fresh);
            }
        } catch {
            // Safe to ignore outside component context
        }
    }

    /**
     * Add a single notification (e.g. from a WebSocket broadcast).
     * @param {Notification} notification
     */
    function push(notification) {
        // Avoid duplicates
        if (!items.value.some((n) => String(n.id) === String(notification.id))) {
            items.value.unshift({ read: false, ...notification });
        }
    }

    // ── Actions ───────────────────────────────────────────────────────────────

    /**
     * Fetch unread notifications from the API.
     * Falls back gracefully if the endpoint doesn't exist yet.
     * @returns {Promise<void>}
     */
    async function fetch() {
        loading.value = true;
        error.value   = null;
        try {
            const { data } = await http.get('/notifications');
            const incoming = data.data ?? data ?? [];
            // Replace list while preserving any local optimistic reads
            const locallyRead = new Set(
                items.value.filter((n) => n.read).map((n) => String(n.id)),
            );
            items.value = incoming.map((n) => ({
                ...n,
                read: locallyRead.has(String(n.id)) ? true : !!n.read,
            }));
        } catch (err) {
            if (err.status !== 404) {
                error.value = err.message ?? 'Failed to load notifications.';
            }
            // 404 = endpoint not yet implemented — silently skip
        } finally {
            loading.value = false;
        }
    }

    /**
     * Mark a single notification as read (optimistic + server confirm).
     * @param {string|number} id
     * @returns {Promise<void>}
     */
    async function markRead(id) {
        const notification = items.value.find((n) => String(n.id) === String(id));
        if (!notification || notification.read) { return; }

        // Optimistic update
        notification.read = true;

        try {
            await http.patch(`/notifications/${id}/read`);
        } catch {
            // Roll back if request fails
            notification.read = false;
        }
    }

    /**
     * Mark all notifications as read (optimistic + server confirm).
     * @returns {Promise<void>}
     */
    async function markAllRead() {
        const unread = items.value.filter((n) => !n.read);
        if (!unread.length) { return; }

        // Optimistic update
        unread.forEach((n) => { n.read = true; });

        try {
            await http.post('/notifications/read-all');
        } catch {
            // Roll back
            unread.forEach((n) => { n.read = false; });
        }
    }

    /**
     * Delete a notification.
     * @param {string|number} id
     * @returns {Promise<void>}
     */
    async function dismiss(id) {
        const idx = items.value.findIndex((n) => String(n.id) === String(id));
        const removed = idx >= 0 ? items.value.splice(idx, 1)[0] : null;

        try {
            await http.delete(`/notifications/${id}`);
        } catch {
            // Re-insert if server rejects
            if (removed) { items.value.splice(idx, 0, removed); }
        }
    }

    /**
     * Clear all notifications locally (no API call).
     */
    function clearAll() {
        items.value = [];
    }

    /**
     * Hook into Laravel Echo for real-time notifications.
     * Call this once after Echo has been initialised with the tenant channel.
     *
     * @example
     * import { useNotificationStore } from '@/Stores/notificationStore';
     * window.Echo.private(`tenant.${tenantId}`).notification((n) => {
     *     useNotificationStore().push(n);
     * });
     *
     * @param {string|number} tenantId
     */
    function listenForBroadcasts(tenantId) {
        if (!window.Echo) { return; }
        window.Echo
            .private(`tenant.${tenantId}`)
            .notification((notification) => push(notification));
    }

    return {
        // state
        items,
        loading,
        error,
        // getters
        unreadCount,
        hasUnread,
        unreadBadge,
        unreadItems,
        recentItems,
        findById,
        // actions
        syncFromInertia,
        push,
        fetch,
        markRead,
        markAllRead,
        dismiss,
        clearAll,
        listenForBroadcasts,
        clearError,
    };
});
