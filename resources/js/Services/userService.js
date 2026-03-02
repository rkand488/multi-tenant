/**
 * User service.
 *
 * CRUD for workspace team members.
 * Routes: /api/v1/tenant/users  (auth:sanctum + tenant + tenant.active)
 */

import http from './http.js';

const userService = {

    // ── Team member list ──────────────────────────────────────────────────────

    /**
     * Paginated list of team members.
     * @param {{ page?: number, search?: string, role?: string, status?: string }} [params]
     * @returns {Promise<{ data: object[], meta: object, links: object[] }>}
     */
    async list(params = {}) {
        const { data } = await http.get('/tenant/users', { params });
        return {
            data:  data.data  ?? [],
            meta:  data.meta  ?? {},
            links: data.links ?? [],
        };
    },

    /**
     * Retrieve a single team member.
     * @param {string|number} id
     * @returns {Promise<object>}
     */
    async get(id) {
        const { data } = await http.get(`/tenant/users/${id}`);
        return data.data ?? data;
    },

    // ── Create ────────────────────────────────────────────────────────────────

    /**
     * Create a new user record directly (admin-initiated, not via invitation).
     * @param {{ name: string, email: string, password: string, role_id?: string|number }} payload
     * @returns {Promise<object>} Created user
     */
    async create(payload) {
        const { data } = await http.post('/tenant/users', payload);
        return data.data ?? data;
    },

    // ── Update ────────────────────────────────────────────────────────────────

    /**
     * Update a team member's details or role.
     * @param {string|number} id
     * @param {{ name?: string, email?: string, role_id?: string|number }} payload
     * @returns {Promise<object>} Updated user
     */
    async update(id, payload) {
        const { data } = await http.put(`/tenant/users/${id}`, payload);
        return data.data ?? data;
    },

    // ── Remove ────────────────────────────────────────────────────────────────

    /**
     * Remove a team member from the workspace.
     * @param {string|number} id
     * @returns {Promise<void>}
     */
    async remove(id) {
        await http.delete(`/tenant/users/${id}`);
    },

    // ── Invitations ───────────────────────────────────────────────────────────

    /**
     * Look up a pending invitation by its one-time token.
     * Unauthenticated endpoint.
     * @param {string} token
     * @returns {Promise<object>} Invitation details including workspace name
     */
    async getInvitation(token) {
        const { data } = await http.get(`/invitations/${token}`);
        return data.data ?? data;
    },

    /**
     * Send a workspace invitation email.
     * @param {{ email: string, role_id: string|number, message?: string }} payload
     * @returns {Promise<object>} Created invitation
     */
    async createInvitation(payload) {
        const { data } = await http.post('/invitations', payload);
        return data.data ?? data;
    },

    /**
     * Accept an invitation and register the new account.
     * Unauthenticated endpoint.
     * @param {{ token: string, name: string, password: string, password_confirmation: string }} payload
     * @returns {Promise<{ user: object, token: string }>}
     */
    async acceptInvitation(payload) {
        const { data } = await http.post('/invitations/accept', payload);
        return data.data ?? data;
    },

    /**
     * Revoke / delete a pending invitation.
     * @param {string|number} invitationId
     * @returns {Promise<void>}
     */
    async deleteInvitation(invitationId) {
        await http.delete(`/invitations/${invitationId}`);
    },
};

export default userService;
