/**
 * Admin service.
 *
 * All endpoints require authentication + super_admin middleware.
 * Base URL: /api/v1 (set in http.js)
 *
 * Covers:
 *   - Tenant management       /admin/tenants
 *   - Plan management (CRUD)  /admin/plans
 *   - Invoice read-only       /admin/invoices
 *   - System analytics        /admin/system-analytics
 *   - Usage statistics        /admin/usage-statistics
 */

import http from './http.js';

const adminService = {

    // ── Tenants (index, show, update — no create/delete via API) ─────────────

    /**
     * List all tenants (paginated, filterable).
     * @param {object} [params]  e.g. { search, status, page }
     * @returns {Promise<{ data: object[], meta: object, links: object[] }>}
     */
    async getTenants(params = {}) {
        const { data } = await http.get('/admin/tenants', { params });
        return {
            data:  data.data  ?? [],
            meta:  data.meta  ?? {},
            links: data.links ?? [],
        };
    },

    /**
     * Retrieve a single tenant.
     * @param {string|number} tenantId
     * @returns {Promise<object>}
     */
    async getTenant(tenantId) {
        const { data } = await http.get(`/admin/tenants/${tenantId}`);
        return data.data ?? data;
    },

    /**
     * Update a tenant's details.
     * @param {string|number} tenantId
     * @param {object} payload
     * @returns {Promise<object>} Updated tenant
     */
    async updateTenant(tenantId, payload) {
        const { data } = await http.put(`/admin/tenants/${tenantId}`, payload);
        return data.data ?? data;
    },

    // ── Plans (full CRUD) ─────────────────────────────────────────────────────

    /**
     * List all plans (paginated).
     * @param {object} [params]
     * @returns {Promise<{ data: object[], meta: object, links: object[] }>}
     */
    async getPlans(params = {}) {
        const { data } = await http.get('/admin/plans', { params });
        return {
            data:  data.data  ?? [],
            meta:  data.meta  ?? {},
            links: data.links ?? [],
        };
    },

    /**
     * Create a new billing plan.
     * @param {object} payload
     * @returns {Promise<object>} Created plan
     */
    async createPlan(payload) {
        const { data } = await http.post('/admin/plans', payload);
        return data.data ?? data;
    },

    /**
     * Retrieve a single billing plan.
     * @param {string|number} planId
     * @returns {Promise<object>}
     */
    async getPlan(planId) {
        const { data } = await http.get(`/admin/plans/${planId}`);
        return data.data ?? data;
    },

    /**
     * Update an existing billing plan.
     * @param {string|number} planId
     * @param {object} payload
     * @returns {Promise<object>} Updated plan
     */
    async updatePlan(planId, payload) {
        const { data } = await http.put(`/admin/plans/${planId}`, payload);
        return data.data ?? data;
    },

    /**
     * Delete a billing plan.
     * @param {string|number} planId
     * @returns {Promise<void>}
     */
    async deletePlan(planId) {
        await http.delete(`/admin/plans/${planId}`);
    },

    // ── Invoices (read-only) ──────────────────────────────────────────────────

    /**
     * List all invoices across every tenant (paginated).
     * @param {object} [params]  e.g. { tenant_id, status, page }
     * @returns {Promise<{ data: object[], meta: object, links: object[] }>}
     */
    async getInvoices(params = {}) {
        const { data } = await http.get('/admin/invoices', { params });
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
        const { data } = await http.get(`/admin/invoices/${invoiceId}`);
        return data.data ?? data;
    },

    // ── System analytics ──────────────────────────────────────────────────────

    /**
     * Retrieve system-wide analytics data.
     * @param {object} [params]  e.g. { period, from, to }
     * @returns {Promise<object>}
     */
    async getAnalytics(params = {}) {
        const { data } = await http.get('/admin/system-analytics', { params });
        return data.data ?? data ?? {};
    },

    // ── Usage statistics ──────────────────────────────────────────────────────

    /**
     * Retrieve platform-wide usage statistics.
     * @param {object} [params]  e.g. { tenant_id, feature, period }
     * @returns {Promise<object>}
     */
    async getUsageStatistics(params = {}) {
        const { data } = await http.get('/admin/usage-statistics', { params });
        return data.data ?? data ?? {};
    },
};

export default adminService;
