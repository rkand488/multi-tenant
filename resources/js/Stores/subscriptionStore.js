import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { subscriptionService } from '@/Services';

/**
 * Subscription store.
 *
 * Manages subscription lifecycle, plan catalog, invoices, and feature usage
 * for the current tenant workspace.
 *
 * API base: /api/v1/billing/*  (requires auth:sanctum + tenant + tenant.active)
 * Public:   GET /api/v1/plans
 *
 * @typedef {{ id: string, name: string, price: number, interval: string, features: string[], is_popular?: boolean }} Plan
 * @typedef {{ status: string, plan_id: string, renews_at: string|null, ends_at: string|null, trial_ends_at: string|null }} Subscription
 * @typedef {{ id: string, number?: string, amount: string|number, status: string, date: string, pdf_url?: string }} Invoice
 * @typedef {{ used: number, limit: number|null }} UsageMetric
 */
export const useSubscriptionStore = defineStore('subscription', () => {
    // ── State ─────────────────────────────────────────────────────────────────

    /** @type {import('vue').Ref<Subscription|null>} */
    const subscription = ref(null);

    /** @type {import('vue').Ref<Plan|null>} Active plan of the current subscription */
    const currentPlan  = ref(null);

    /** @type {import('vue').Ref<Plan[]>} Full plan catalog (public) */
    const plans        = ref([]);

    /** @type {import('vue').Ref<Invoice[]>} */
    const invoices     = ref([]);

    /** @type {import('vue').Ref<Record<string, UsageMetric>>} Feature usage keyed by feature slug */
    const usage        = ref({});

    const loading = ref(false);
    const error   = ref(null);

    // Per-action loading flags
    const subscriptionLoading = ref(false);
    const plansLoading        = ref(false);
    const invoicesLoading     = ref(false);
    const usageLoading        = ref(false);

    // ── Getters ───────────────────────────────────────────────────────────────

    const status       = computed(() => subscription.value?.status ?? 'inactive');
    const isActive     = computed(() => status.value === 'active');
    const isTrialing   = computed(() => status.value === 'trialing');
    const isCanceled   = computed(() => status.value === 'canceled');
    const isPastDue    = computed(() => status.value === 'past_due');
    const hasSubscription = computed(() => !!subscription.value);

    /** Human-readable status label */
    const statusLabel = computed(() => ({
        active:   'Active',
        trialing: 'Trialing',
        canceled: 'Canceled',
        past_due: 'Past Due',
        inactive: 'Inactive',
    })[status.value] ?? status.value);

    /**
     * Tailwind colour classes for the status badge.
     * @returns {string}
     */
    const statusBadgeClass = computed(() => ({
        active:   'bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400',
        trialing: 'bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400',
        canceled: 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
        past_due: 'bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400',
        inactive: 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
    })[status.value] ?? 'bg-gray-100 text-gray-600');

    /** Plans available to upgrade to (excludes the currently active plan). */
    const availablePlans = computed(() =>
        plans.value.filter((p) => p.id !== currentPlan.value?.id),
    );

    /**
     * Compute usage percent for a given feature (clamped 0-100).
     * @param {string} feature  e.g. 'users' | 'storage'
     * @returns {number}
     */
    const usagePercent = computed(() => (feature) => {
        const m = usage.value[feature];
        if (!m || !m.limit) { return 0; }
        return Math.min(100, Math.round((m.used / m.limit) * 100));
    });

    /**
     * Suggested Tailwind class for a usage bar.
     * @param {string} feature
     * @returns {string}
     */
    const usageBarColor = computed(() => (feature) => {
        const pct = usagePercent.value(feature);
        return pct >= 90 ? 'bg-red-500' : pct >= 70 ? 'bg-amber-500' : 'bg-indigo-500';
    });

    /**
     * Whether the workspace is at or over the given feature limit.
     * @param {string} feature
     * @returns {boolean}
     */
    const isAtLimit = computed(() => (feature) => usagePercent.value(feature) >= 100);

    // ── Helpers ───────────────────────────────────────────────────────────────

    function clearError() {
        error.value = null;
    }

    /**
     * Seed billing data from Inertia page props.
     * Call in the Billing/Subscription page's `onMounted`.
     * @param {{ subscription?: object, plan?: object, plans?: object[], invoices?: object[], usage?: object }} props
     */
    function syncFromInertia(props = {}) {
        if (props.subscription) { subscription.value = props.subscription; }
        if (props.plan)         { currentPlan.value  = props.plan; }
        if (props.plans?.length) { plans.value       = props.plans; }
        if (props.invoices?.length) { invoices.value = props.invoices; }
        if (props.usage)        { usage.value        = props.usage; }
    }

    // ── Actions ───────────────────────────────────────────────────────────────

    /**
     * Fetch the current subscription.
     * @returns {Promise<Subscription|null>}
     */
    async function fetchSubscription() {
        subscriptionLoading.value = true;
        error.value               = null;
        try {
            subscription.value = await subscriptionService.getSubscription();
            return subscription.value;
        } catch (err) {
            if (err.status !== 404) {
                error.value = err.message ?? 'Failed to load subscription.';
            }
            return null;
        } finally {
            subscriptionLoading.value = false;
        }
    }

    /**
     * Subscribe to a plan.
     * @param {{ plan_id: string, payment_method?: string }} payload
     * @returns {Promise<boolean>}
     */
    async function subscribe(payload) {
        subscriptionLoading.value = true;
        error.value               = null;
        try {
            subscription.value = await subscriptionService.createSubscription(payload);
            return true;
        } catch (err) {
            error.value = err.message ?? 'Failed to create subscription.';
            return false;
        } finally {
            subscriptionLoading.value = false;
        }
    }

    /**
     * Upgrade or downgrade to a different plan.
     * @param {{ plan_id: string }} payload
     * @returns {Promise<boolean>}
     */
    async function changePlan(payload) {
        subscriptionLoading.value = true;
        error.value               = null;
        try {
            subscription.value = await subscriptionService.updateSubscription(payload);
            const updated = plans.value.find((p) => String(p.id) === String(payload.plan_id));
            if (updated) { currentPlan.value = updated; }
            return true;
        } catch (err) {
            error.value = err.message ?? 'Failed to change plan.';
            return false;
        } finally {
            subscriptionLoading.value = false;
        }
    }

    /**
     * Cancel the current subscription at period end.
     * @returns {Promise<boolean>}
     */
    async function cancelSubscription() {
        subscriptionLoading.value = true;
        error.value               = null;
        try {
            subscription.value = await subscriptionService.cancelSubscription();
            return true;
        } catch (err) {
            error.value = err.message ?? 'Failed to cancel subscription.';
            return false;
        } finally {
            subscriptionLoading.value = false;
        }
    }

    /**
     * Resume a canceled subscription (before the period ends).
     * @returns {Promise<boolean>}
     */
    async function resumeSubscription() {
        subscriptionLoading.value = true;
        error.value               = null;
        try {
            subscription.value = await subscriptionService.resumeSubscription();
            return true;
        } catch (err) {
            error.value = err.message ?? 'Failed to resume subscription.';
            return false;
        } finally {
            subscriptionLoading.value = false;
        }
    }

    // ── Plans ─────────────────────────────────────────────────────────────────

    /**
     * Fetch the public plan catalog.
     * @returns {Promise<Plan[]>}
     */
    async function fetchPlans() {
        plansLoading.value = true;
        error.value        = null;
        try {
            plans.value = await subscriptionService.getPlans();
            return plans.value;
        } catch (err) {
            error.value = err.message ?? 'Failed to load plans.';
            return [];
        } finally {
            plansLoading.value = false;
        }
    }

    /**
     * Fetch a single plan by id.
     * @param {string|number} planId
     * @returns {Promise<Plan|null>}
     */
    async function fetchPlan(planId) {
        plansLoading.value = true;
        error.value        = null;
        try {
            const plan = await subscriptionService.getPlan(planId);
            // Upsert into the plans list
            const idx = plans.value.findIndex((p) => String(p.id) === String(planId));
            if (idx >= 0) {
                plans.value.splice(idx, 1, plan);
            } else {
                plans.value.push(plan);
            }
            return plan;
        } catch (err) {
            error.value = err.message ?? 'Failed to load plan.';
            return null;
        } finally {
            plansLoading.value = false;
        }
    }

    // ── Invoices ──────────────────────────────────────────────────────────────

    /**
     * Fetch billing invoices.
     * @param {{ page?: number }} params
     * @returns {Promise<Invoice[]>}
     */
    async function fetchInvoices(params = {}) {
        invoicesLoading.value = true;
        error.value           = null;
        try {
            const result = await subscriptionService.getInvoices(params);
            invoices.value = result.data ?? result ?? [];
            return invoices.value;
        } catch (err) {
            error.value = err.message ?? 'Failed to load invoices.';
            return [];
        } finally {
            invoicesLoading.value = false;
        }
    }

    // ── Usage ─────────────────────────────────────────────────────────────────

    /**
     * Fetch usage for all features at once.
     * @returns {Promise<Record<string, UsageMetric>>}
     */
    async function fetchUsage() {
        usageLoading.value = true;
        error.value        = null;
        try {
            usage.value = await subscriptionService.getUsage();
            return usage.value;
        } catch (err) {
            error.value = err.message ?? 'Failed to load usage.';
            return {};
        } finally {
            usageLoading.value = false;
        }
    }

    /**
     * Fetch usage for a single feature.
     * @param {string} feature  e.g. 'users' | 'storage'
     * @returns {Promise<UsageMetric|null>}
     */
    async function fetchFeatureUsage(feature) {
        usageLoading.value = true;
        error.value        = null;
        try {
            const metric = await subscriptionService.getFeatureUsage(feature);
            usage.value = { ...usage.value, [feature]: metric };
            return metric;
        } catch (err) {
            error.value = err.message ?? `Failed to load ${feature} usage.`;
            return null;
        } finally {
            usageLoading.value = false;
        }
    }

    return {
        // state
        subscription,
        currentPlan,
        plans,
        invoices,
        usage,
        loading,
        error,
        subscriptionLoading,
        plansLoading,
        invoicesLoading,
        usageLoading,
        // getters
        status,
        isActive,
        isTrialing,
        isCanceled,
        isPastDue,
        hasSubscription,
        statusLabel,
        statusBadgeClass,
        availablePlans,
        usagePercent,
        usageBarColor,
        isAtLimit,
        // actions
        syncFromInertia,
        fetchSubscription,
        subscribe,
        changePlan,
        cancelSubscription,
        resumeSubscription,
        fetchPlans,
        fetchPlan,
        fetchInvoices,
        fetchUsage,
        fetchFeatureUsage,
        clearError,
    };
});
