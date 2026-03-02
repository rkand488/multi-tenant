/**
 * Tenant service.
 *
 * Covers workspace-scoped resources: team settings, roles/permissions,
 * activity logs, and file storage.
 *
 * All routes require: auth:sanctum + tenant + tenant.active middleware.
 * Routes: /api/v1/tenant/*
 */

import http from './http.js';

const tenantService = {

    // ── Team settings ─────────────────────────────────────────────────────────

    /**
     * Retrieve the current workspace's team settings record.
     * The first (and only) team-settings record is returned.
     * @returns {Promise<object>}
     */
    async getSettings() {
        const { data } = await http.get('/tenant/team-settings');
        // Laravel apiResource index returns { data: [...] }
        const list = data.data ?? data;
        return Array.isArray(list) ? (list[0] ?? null) : list;
    },

    /**
     * Update the workspace's team settings.
     * @param {string|number} id  Team setting record id
     * @param {{ name?: string, timezone?: string }} payload
     * @returns {Promise<object>} Updated settings
     */
    async updateSettings(id, payload) {
        const { data } = await http.put(`/tenant/team-settings/${id}`, payload);
        return data.data ?? data;
    },

    // ── Roles & permissions ───────────────────────────────────────────────────

    /**
     * List all role/permission assignments for the workspace.
     * @param {object} [params]  Query string params (page, search, …)
     * @returns {Promise<{ data: object[], meta: object, links: object[] }>}
     */
    async getRoles(params = {}) {
        const { data } = await http.get('/tenant/roles-permissions', { params });
        return {
            data:  data.data  ?? [],
            meta:  data.meta  ?? {},
            links: data.links ?? [],
        };
    },

    /**
     * Update a user's role/permissions within the workspace.
     * @param {string|number} userId
     * @param {{ role_id: string|number, permissions?: string[] }} payload
     * @returns {Promise<object>} Updated user record
     */
    async updateUserRole(userId, payload) {
        const { data } = await http.put(`/tenant/roles-permissions/${userId}`, payload);
        return data.data ?? data;
    },

    // ── Activity logs ─────────────────────────────────────────────────────────

    /**
     * List activity log entries (paginated).
     * @param {{ page?: number, search?: string, event?: string, causer_id?: string, date_from?: string, date_to?: string }} [params]
     * @returns {Promise<{ data: object[], meta: object, links: object[] }>}
     */
    async getActivityLogs(params = {}) {
        const { data } = await http.get('/tenant/activity-logs', { params });
        return {
            data:  data.data  ?? [],
            meta:  data.meta  ?? {},
            links: data.links ?? [],
        };
    },

    /**
     * Retrieve a single activity log entry.
     * @param {string|number} id
     * @returns {Promise<object>}
     */
    async getActivityLog(id) {
        const { data } = await http.get(`/tenant/activity-logs/${id}`);
        return data.data ?? data;
    },

    // ── File storage ──────────────────────────────────────────────────────────

    /**
     * List uploaded files.
     * @param {{ page?: number, search?: string }} [params]
     * @returns {Promise<{ data: object[], meta: object, links: object[] }>}
     */
    async getFiles(params = {}) {
        const { data } = await http.get('/tenant/files', { params });
        return {
            data:  data.data  ?? [],
            meta:  data.meta  ?? {},
            links: data.links ?? [],
        };
    },

    /**
     * Upload a file.
     * @param {File|FormData} fileOrFormData  Pass a `File` for simple uploads,
     *   or a `FormData` instance for multi-field payloads.
     * @param {Function} [onUploadProgress]  Axios progress callback
     * @returns {Promise<object>} Created file record
     */
    async uploadFile(fileOrFormData, onUploadProgress) {
        const formData = fileOrFormData instanceof FormData
            ? fileOrFormData
            : (() => { const fd = new FormData(); fd.append('file', fileOrFormData); return fd; })();

        const { data } = await http.post('/tenant/files', formData, {
            headers:            { 'Content-Type': 'multipart/form-data' },
            onUploadProgress,
        });
        return data.data ?? data;
    },

    /**
     * Get metadata for a single file.
     * @param {string|number} id
     * @returns {Promise<object>}
     */
    async getFile(id) {
        const { data } = await http.get(`/tenant/files/${id}`);
        return data.data ?? data;
    },

    /**
     * Delete a file.
     * @param {string|number} id
     * @returns {Promise<void>}
     */
    async deleteFile(id) {
        await http.delete(`/tenant/files/${id}`);
    },

    /**
     * Get a signed download URL / stream for a file.
     * Returns the raw axios response so the caller can create an object URL.
     * @param {string|number} id
     * @returns {Promise<import('axios').AxiosResponse>}
     */
    async downloadFile(id) {
        return http.get(`/tenant/files/${id}/download`, { responseType: 'blob' });
    },

    // ── Invitations (tenant context) ──────────────────────────────────────────

    /**
     * Send a workspace invitation.
     * @param {{ email: string, role_id: string|number, message?: string }} payload
     * @returns {Promise<object>} Created invitation record
     */
    async createInvitation(payload) {
        const { data } = await http.post('/invitations', payload);
        return data.data ?? data;
    },

    /**
     * Revoke a pending invitation.
     * @param {string|number} invitationId
     * @returns {Promise<void>}
     */
    async deleteInvitation(invitationId) {
        await http.delete(`/invitations/${invitationId}`);
    },
};

export default tenantService;
