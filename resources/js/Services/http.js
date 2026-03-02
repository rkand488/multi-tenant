/**
 * Shared Axios HTTP client.
 *
 * Creates a single axios instance used by every service module.
 * Centralises:
 *   - Base URL / headers
 *   - Bearer token injection
 *   - CSRF refresh for Sanctum
 *   - Global loading counter (reactive)
 *   - Normalised error objects
 *   - 401 / 419 / 429 / 5xx handling
 */

import axios from 'axios';
import { ref } from 'vue';

// ── Global loading state ───────────────────────────────────────────────────────
/**
 * Reactive counter: > 0 means at least one request is in flight.
 * Bind to a top-level progress bar component like NProgress.
 *
 * @example
 * import { loadingCount } from '@/Services/http';
 * const isLoading = computed(() => loadingCount.value > 0);
 */
export const loadingCount = ref(0);

// ── Error class ────────────────────────────────────────────────────────────────
/**
 * Normalised API error thrown by every service method on failure.
 *
 * @property {string}              message   Human-readable summary
 * @property {number|null}         status    HTTP status code
 * @property {Record<string,string[]>} errors  Laravel validation errors (422)
 * @property {any}                 raw       Original axios error
 */
export class ApiError extends Error {
    /**
     * @param {string} message
     * @param {number|null} status
     * @param {Record<string,string[]>} errors
     * @param {any} raw
     */
    constructor(message, status = null, errors = {}, raw = null) {
        super(message);
        this.name    = 'ApiError';
        this.status  = status;
        this.errors  = errors;  // keyed validation error bag
        this.raw     = raw;
    }

    /** True when the server returned field-level validation errors. */
    get isValidation() {
        return this.status === 422;
    }

    /** True when the bearer token is missing or expired. */
    get isUnauthorized() {
        return this.status === 401;
    }

    /** True when the CSRF token has expired (Sanctum SPA). */
    get isCsrfExpired() {
        return this.status === 419;
    }

    /** True when the client has been rate-limited. */
    get isRateLimited() {
        return this.status === 429;
    }
}

// ── Token helpers ─────────────────────────────────────────────────────────────
const TOKEN_KEY = 'auth_token';

export const tokenStorage = {
    get: ()          => localStorage.getItem(TOKEN_KEY),
    set: (token)     => {
        localStorage.setItem(TOKEN_KEY, token);
        http.defaults.headers.common['Authorization'] = `Bearer ${token}`;
    },
    clear: ()        => {
        localStorage.removeItem(TOKEN_KEY);
        delete http.defaults.headers.common['Authorization'];
    },
};

// ── Axios instance ─────────────────────────────────────────────────────────────
const http = axios.create({
    baseURL:         '/api/v1',
    withCredentials: true,  // send cookies for Sanctum SPA
    headers:         {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept':           'application/json',
    },
});

// ── Request interceptor ───────────────────────────────────────────────────────
http.interceptors.request.use(
    (config) => {
        loadingCount.value++;

        // Inject bearer token when present
        const token = tokenStorage.get();
        if (token) {
            config.headers['Authorization'] = `Bearer ${token}`;
        }

        return config;
    },
    (error) => {
        loadingCount.value = Math.max(0, loadingCount.value - 1);
        return Promise.reject(error);
    },
);

// ── Response interceptor ──────────────────────────────────────────────────────
/** Tracks whether a CSRF refresh is already in flight (avoids loops). */
let csrfRefreshing = false;

http.interceptors.response.use(
    (response) => {
        loadingCount.value = Math.max(0, loadingCount.value - 1);
        return response;
    },

    async (error) => {
        loadingCount.value = Math.max(0, loadingCount.value - 1);

        const status   = error.response?.status ?? null;
        const data     = error.response?.data   ?? {};
        const original = error.config;

        // ── 419: CSRF token expired — refresh cookie and retry once ───────────
        if (status === 419 && !original._csrfRetried) {
            if (!csrfRefreshing) {
                csrfRefreshing = true;
                try {
                    await axios.get('/sanctum/csrf-cookie', { withCredentials: true });
                } finally {
                    csrfRefreshing = false;
                }
            }
            original._csrfRetried = true;
            return http(original);
        }

        // ── 401: Token missing or expired — clear storage ─────────────────────
        if (status === 401) {
            tokenStorage.clear();
            // Redirect to login if we're inside a browser context
            if (typeof window !== 'undefined' && !window.location.pathname.startsWith('/login')) {
                window.dispatchEvent(new CustomEvent('auth:expired'));
            }
        }

        // ── Normalise into ApiError ───────────────────────────────────────────
        const message = statusMessage(status, data);
        const errors  = data.errors ?? {};

        return Promise.reject(new ApiError(message, status, errors, error));
    },
);

/** Build a user-facing message from HTTP status + response body. */
function statusMessage(status, data) {
    if (data?.message) { return data.message; }

    return {
        400: 'Bad request.',
        401: 'Session expired. Please log in again.',
        403: 'You do not have permission to do that.',
        404: 'The requested resource was not found.',
        409: 'A conflict occurred. Please try again.',
        419: 'Session expired. Please refresh the page.',
        422: 'Please check your input and try again.',
        429: 'Too many requests. Please slow down.',
        500: 'Server error. Please try again later.',
        502: 'Service unavailable. Please try again later.',
        503: 'The server is temporarily unavailable.',
    }[status] ?? 'An unexpected error occurred.';
}

// Restore token header on module load (handles page refresh)
const existingToken = tokenStorage.get();
if (existingToken) {
    http.defaults.headers.common['Authorization'] = `Bearer ${existingToken}`;
}

export default http;
