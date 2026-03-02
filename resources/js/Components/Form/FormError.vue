<script setup>
/**
 * FormError
 *
 * Animated inline validation error message. Renders a small icon + text
 * with a smooth slide-in/out transition whenever the `message` prop changes.
 *
 * Used automatically inside FormField — but can also be placed standalone
 * for custom layouts.
 *
 * @example
 * <!-- Standalone -->
 * <FormError :message="form.errors.email" />
 *
 * <!-- With explicit id matching the input's aria-describedby -->
 * <input aria-describedby="email-error" ... />
 * <FormError id="email-error" :message="form.errors.email" />
 */

defineProps({
    /** Error string from `form.errors.fieldName` or any reactive string. */
    message: { type: String, default: null },
    /** Matches the `id` attribute used in the parent input's aria-describedby. */
    id:      { type: String, default: null },
});
</script>

<template>
    <Transition
        enter-active-class="transition duration-150 ease-out"
        enter-from-class="opacity-0 translate-y-0.5"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition duration-100 ease-in"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 translate-y-0.5"
    >
        <p
            v-if="message"
            :id="id"
            role="alert"
            aria-live="polite"
            class="flex items-center gap-1.5 text-xs text-red-600 dark:text-red-400"
        >
            <!-- Exclamation-circle icon (inline, no external dep) -->
            <svg
                class="size-3.5 shrink-0"
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 20 20"
                fill="currentColor"
                aria-hidden="true"
            >
                <path
                    fill-rule="evenodd"
                    d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-8-5a.75.75 0 0 1 .75.75v4.5a.75.75 0 0 1-1.5 0v-4.5A.75.75 0 0 1 10 5Zm0 10a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z"
                    clip-rule="evenodd"
                />
            </svg>

            {{ message }}
        </p>
    </Transition>
</template>
