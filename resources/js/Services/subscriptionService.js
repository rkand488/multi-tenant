/**
 * Subscription service.
 *
 * Covers the public plan catalog, subscription lifecycle, invoices,
 * and feature-usage metering.
 *
 * Public routes:   /api/v1/plans
 * Billing routes:  /api/v1/billing/*  (auth:sanctum + tenant + tenant.active)
 * Invoice / usage: additionally require the `subscription` middleware
 */

import http from './http.js';

const subscriptionService = {

    // ── Plans (public) ────────────────────────────────────────────────────────

    /**
     * List all publicly available plans.
     * @param {object} [params]
     * @returns {Promise<object[]>}
     */
    async getPlans(params = {}) {
        const { data } = await http.get('/plans', { params });
        return data.data ?? data ?? [];
    },

    /**
     * Get a single plan by id.
     * @param {string|number} planId
     * @returns {Promise<object>}
     */
    async getPlan(planId) {
        const { data } = await http.get(`/plans/${planId}`);
        return data.data ?? data;
    },

    // ── Subscription lifecycle ────────────────────────────────────────────────

    /**
     * Retrieve the current workspace's active subscription.
     * @returns {Promise<object|null>}
     */
    async getSubscription() {
        const { data } = await http.get('/billing/subscription');
        return data.data ?? data ?? null;
    },

    /**
     * Create (subscribe) for the first time or after a full cancellation.
     * @param {{ plan_id: string, payment_method?: string }} payload
     * @returns {Promise<object>} Created subscription
     */
    async createSubscription(payload) {
        const { data } = await http.post('/billing/subscription', payload);
        return data.data ?? data;
    },

    /**
     * Upgrade / downgrade to another plan (mid-cycle swap).
     * @param {{ plan_id: string }} payload
     * @returns {Promise<object>} Updated subscription
     */
    async updateSubscription(payload) {
        const { data } = await http.patch('/billing/subscription', payload);
        return data.data ?? data;
    },

    /**
     * Schedule cancellation at the end of the current billing period.
     * @returns {Promise<object>} Updated subscription (status = canceled)
     */
    async cancelSubscription() {
        const { data } = await http.delete('/billing/subscription');
        return data.data ?? data;
    },

    /**
     * Reactivate a subscription that was canceled but hasn't expired yet.
     * @returns {Promise<object>} Updated subscription (status = active)
     */
    async resumeSubscription() {
        const { data } = await http.post('/billing/subscription/resume');
        return data.data ?? data;
    },

    // ── Invoices ──────────────────────────────────────────────────────────────

    /**
     * List billing invoices (paginated).
     * Requires an active or recently canceled subscription.
     * @param {{ page?: number }} [params]
     * @returns {Promise<{ data: object[], meta: object, links: object[] }>}
     */
    async getInvoices(params = {}) {
        const { data } = await http.get('/billing/invoices', { params });
        return {
            data:  data.data  ?? [],
            meta:  data.meta  ?? {},
            links: data.links ?? [],
        };
    },

    /**
     * Retrieve a single invoice.
     * @param {string|number} invoiceId
     * @returns {Promise<object>}
     */
    async getInvoice(invoiceId) {
        const { data } = await http.get(`/billing/invoices/${invoiceId}`);
        return data.data ?? data;
    },

    // ── Usage metering ────────────────────────────────────────────────────────

    /**
     * Retrieve current usage for all metered features.
     * @returns {Promise<Record<string, { used: number, limit: number|null }>>}
     */
    async getUsage() {
        const { data } = await http.get('/billing/usage');
        return data.data ?? data ?? {};
    },

    /**
     * Retrieve usage for a specific feature.
     * @param {string} feature  e.g. 'users' | 'storage'
     * @returns {Promise<{ used: number, limit: number|null }>}
     */
    async getFeatureUsage(feature) {
        const { data } = await http.get(`/billing/usage/${feature}`);
        return data.data ?? data;
    },
};

export default subscriptionService;
