/**
 * useFormHandler
 *
 * Thin wrapper around Inertia's `useForm` that adds:
 *
 *  - `recentlySuccessful` — a reactive ref that becomes `true` on success
 *     and auto-resets to `false` after `successTimeout` ms. Use it to show
 *     a brief inline confirmation next to the submit button.
 *
 *  - `submitForm(method, url, options)` — a convenience helper that
 *     merges your callbacks with the Inertia request options.
 *
 *  - Automatic toast pushed to the notification store on success.
 *     Pass `successMessage: false` to suppress the toast.
 *
 * The raw `form` object is returned unchanged so every Inertia property
 * (`form.errors`, `form.processing`, `form.reset()`, etc.) still works.
 *
 * @example
 * // Basic
 * const { form, submitForm, recentlySuccessful } = useFormHandler({
 *     name:  '',
 *     email: '',
 * });
 *
 * const save = () =>
 *     submitForm('put', route('profile.update'), {
 *         successMessage: 'Profile updated.',
 *         onSuccess: () => form.reset('password'),
 *     });
 *
 * // Template
 * <SubmitButton :processing="form.processing" />
 * <Transition ...>
 *     <span v-if="recentlySuccessful" class="text-sm text-green-600">Saved!</span>
 * </Transition>
 *
 * @example
 * // Suppress toast, handle success manually
 * submitForm('post', route('invitations.store'), {
 *     successMessage: false,
 *     onSuccess: () => { modalOpen.value = false; },
 * });
 */

import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useNotificationStore } from '@/Stores/notificationStore';

/**
 * @template {Record<string, any>} T
 * @param {T} initialValues  Initial form field values, passed to Inertia's useForm.
 * @param {{ successTimeout?: number }} [config]
 * @returns {{
 *   form: ReturnType<typeof import('@inertiajs/vue3').useForm>,
 *   recentlySuccessful: import('vue').Ref<boolean>,
 *   submitForm: (method: 'get'|'post'|'put'|'patch'|'delete', url: string, options?: object) => void,
 * }}
 */
export function useFormHandler(initialValues, config = {}) {
    const { successTimeout = 3000 } = config;

    const form               = useForm(initialValues);
    const notifStore         = useNotificationStore();
    const recentlySuccessful = ref(false);

    /** @type {ReturnType<typeof setTimeout>|null} */
    let resetTimer = null;

    /**
     * Submit the form via the given HTTP method.
     *
     * @param {'get'|'post'|'put'|'patch'|'delete'} method
     * @param {string} url
     * @param {{
     *   successMessage?: string | false,
     *   onSuccess?:      (page: object) => void,
     *   onError?:        (errors: Record<string, string>) => void,
     *   onFinish?:       () => void,
     *   [key: string]:   any,
     * }} [options]
     */
    function submitForm(method, url, options = {}) {
        const {
            successMessage = 'Changes saved.',
            onSuccess,
            onError,
            onFinish,
            ...inertiaOptions
        } = options;

        form[method](url, {
            ...inertiaOptions,

            onSuccess(page) {
                // Flip the recently-successful flag
                recentlySuccessful.value = true;
                clearTimeout(resetTimer);
                resetTimer = setTimeout(() => {
                    recentlySuccessful.value = false;
                }, successTimeout);

                // Push a toast to the notification store
                if (successMessage !== false && successMessage) {
                    notifStore.push({
                        id:         `form-success-${Date.now()}`,
                        title:      successMessage,
                        type:       'success',
                        read:       false,
                        created_at: new Date().toISOString(),
                    });
                }

                onSuccess?.(page);
            },

            onError(errors) {
                onError?.(errors);
            },

            onFinish() {
                onFinish?.();
            },
        });
    }

    return { form, submitForm, recentlySuccessful };
}
