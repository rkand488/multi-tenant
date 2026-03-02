<script setup>
/**
 * Alert
 *
 * @example
 * <Alert variant="success">Your changes have been saved.</Alert>
 *
 * <Alert variant="danger" title="Something went wrong" dismissible @dismiss="showError = false">
 *     Please try again or contact support.
 * </Alert>
 *
 * <Alert variant="warning" title="Subscription expiring">
 *     Your plan expires in 3 days.
 *     <template #action>
 *         <Button size="xs" variant="secondary">Renew now</Button>
 *     </template>
 * </Alert>
 *
 * <Alert variant="info">This feature is in beta.</Alert>
 */

defineProps({
    variant: {
        type: String,
        default: 'info',
        validator: (v) => ['success', 'warning', 'danger', 'info'].includes(v),
    },
    title:       { type: String,  default: null },
    dismissible: { type: Boolean, default: false },
});

defineEmits(['dismiss']);

const config = {
    success: {
        wrapper: 'bg-green-50 border-green-200 dark:bg-green-900/20 dark:border-green-800',
        icon:    'text-green-500 dark:text-green-400',
        title:   'text-green-800 dark:text-green-300',
        body:    'text-green-700 dark:text-green-400',
        dismiss: 'text-green-500 hover:bg-green-100 dark:hover:bg-green-800',
        svg: `<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z" />`,
    },
    warning: {
        wrapper: 'bg-yellow-50 border-yellow-200 dark:bg-yellow-900/20 dark:border-yellow-800',
        icon:    'text-yellow-500 dark:text-yellow-400',
        title:   'text-yellow-800 dark:text-yellow-300',
        body:    'text-yellow-700 dark:text-yellow-400',
        dismiss: 'text-yellow-500 hover:bg-yellow-100 dark:hover:bg-yellow-800',
        svg: `<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />`,
    },
    danger: {
        wrapper: 'bg-red-50 border-red-200 dark:bg-red-900/20 dark:border-red-800',
        icon:    'text-red-500 dark:text-red-400',
        title:   'text-red-800 dark:text-red-300',
        body:    'text-red-700 dark:text-red-400',
        dismiss: 'text-red-500 hover:bg-red-100 dark:hover:bg-red-800',
        svg: `<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />`,
    },
    info: {
        wrapper: 'bg-blue-50 border-blue-200 dark:bg-blue-900/20 dark:border-blue-800',
        icon:    'text-blue-500 dark:text-blue-400',
        title:   'text-blue-800 dark:text-blue-300',
        body:    'text-blue-700 dark:text-blue-400',
        dismiss: 'text-blue-500 hover:bg-blue-100 dark:hover:bg-blue-800',
        svg: `<path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />`,
    },
};
</script>

<template>
    <div
        class="flex items-start gap-3 rounded-lg border p-4 text-sm"
        :class="config[variant].wrapper"
        role="alert"
        :aria-live="variant === 'danger' ? 'assertive' : 'polite'"
    >
        <!-- Icon -->
        <svg
            class="mt-0.5 size-5 shrink-0"
            :class="config[variant].icon"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke-width="1.5"
            stroke="currentColor"
            aria-hidden="true"
            v-html="config[variant].svg"
        />

        <!-- Content -->
        <div class="min-w-0 flex-1">
            <p v-if="title" class="font-semibold" :class="config[variant].title">{{ title }}</p>
            <div :class="[config[variant].body, title ? 'mt-1' : '']">
                <slot />
            </div>
            <div v-if="$slots.action" class="mt-3">
                <slot name="action" />
            </div>
        </div>

        <!-- Dismiss button -->
        <button
            v-if="dismissible"
            type="button"
            class="shrink-0 rounded-md p-0.5 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-1"
            :class="config[variant].dismiss"
            aria-label="Dismiss"
            @click="$emit('dismiss')"
        >
            <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
</template>
