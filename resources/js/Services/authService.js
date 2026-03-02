/**
 * Auth service.
 *
 * Wraps every authentication-related API call.
 * Routes: /api/v1/auth/*
 */

import http, { tokenStorage } from './http.js';

const authService = {

    // ── CSRF ──────────────────────────────────────────────────────────────────

    /**
     * Prime the Sanctum CSRF cookie before login / register.
     * Must be called before the first state-mutation request on a fresh session.
     * @returns {Promise<void>}
     */
    async csrf() {
        // Uses raw axios (not our baseURL instance) to hit the cookie endpoint
        const { default: axios } = await import('axios');
        await axios.get('/sanctum/csrf-cookie', { withCredentials: true });
    },

    // ── Login / register ──────────────────────────────────────────────────────

    /**
     * Log in with email + password.
     * Stores the returned bearer token automatically.
     *
     * @param {{ email: string, password: string, remember?: boolean }} credentials
     * @returns {Promise<{ user: object, token: string }>}
     */
    async login(credentials) {
        await authService.csrf();
        const { data } = await http.post('/auth/login', credentials);
        const payload  = data.data ?? data;
        if (payload.token) { tokenStorage.set(payload.token); }
        return payload;
    },

    /**
     * Register a new account.
     * @param {{ name: string, email: string, password: string, password_confirmation: string }} payload
     * @returns {Promise<{ user: object, token: string }>}
     */
    async register(payload) {
        await authService.csrf();
        const { data } = await http.post('/auth/register', payload);
        const result   = data.data ?? data;
        if (result.token) { tokenStorage.set(result.token); }
        return result;
    },

    // ── Current user ──────────────────────────────────────────────────────────

    /**
     * Fetch the authenticated user's profile.
     * @returns {Promise<object>}
     */
    async me() {
        const { data } = await http.get('/auth/me');
        return data.data ?? data;
    },

    // ── Logout ────────────────────────────────────────────────────────────────

    /**
     * Invalidate the current session token.
     * @returns {Promise<void>}
     */
    async logout() {
        await http.post('/auth/logout');
        tokenStorage.clear();
    },

    /**
     * Invalidate all tokens on all devices.
     * @returns {Promise<void>}
     */
    async logoutAll() {
        await http.post('/auth/logout-all');
        tokenStorage.clear();
    },

    // ── Profile mutations ─────────────────────────────────────────────────────

    /**
     * Update the authenticated user's display name and / or email.
     * @param {{ name?: string, email?: string }} payload
     * @returns {Promise<object>} Updated user
     */
    async updateProfile(payload) {
        const { data } = await http.put('/auth/me', payload);
        return data.data ?? data;
    },

    /**
     * Change the authenticated user's password.
     * @param {{ current_password: string, password: string, password_confirmation: string }} payload
     * @returns {Promise<void>}
     */
    async updatePassword(payload) {
        await http.put('/auth/password', payload);
    },

    // ── Invitations ───────────────────────────────────────────────────────────

    /**
     * Look up an invitation by its one-time token (unauthenticated).
     * @param {string} token
     * @returns {Promise<object>} Invitation details
     */
    async getInvitation(token) {
        const { data } = await http.get(`/invitations/${token}`);
        return data.data ?? data;
    },

    /**
     * Accept an invitation and create an account (unauthenticated).
     * @param {{ token: string, name: string, password: string, password_confirmation: string }} payload
     * @returns {Promise<{ user: object, token: string }>}
     */
    async acceptInvitation(payload) {
        await authService.csrf();
        const { data } = await http.post('/invitations/accept', payload);
        const result   = data.data ?? data;
        if (result.token) { tokenStorage.set(result.token); }
        return result;
    },
};

export default authService;
