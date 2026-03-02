<script setup>
/**
 * Button
 *
 * @example
 * <Button>Save</Button>
 * <Button variant="secondary" size="sm">Cancel</Button>
 * <Button variant="danger" :loading="deleting" @click="remove">Delete</Button>
 * <Button variant="ghost" size="xs" :disabled="true">Disabled</Button>
 * <Button as="a" :href="route('dashboard')">Go to Dashboard</Button>
 */

const props = defineProps({
    /** Visual style */
    variant: {
        type: String,
        default: 'primary',
        validator: (v) => ['primary', 'secondary', 'danger', 'ghost', 'link'].includes(v),
    },
    /** Size token */
    size: {
        type: String,
        default: 'md',
        validator: (v) => ['xs', 'sm', 'md', 'lg'].includes(v),
    },
    /** Show spinner and disable interactions */
    loading: { type: Boolean, default: false },
    /** Disable button */
    disabled: { type: Boolean, default: false },
    /** Native button type OR render as anchor */
    type: { type: String, default: 'button' },
    /** Render a different element (e.g. "a") */
    as: { type: String, default: 'button' },
    /** href when as="a" */
    href: { type: String, default: null },
});

const variantClasses = {
    primary:   'bg-indigo-600 text-white shadow-sm hover:bg-indigo-700 focus-visible:ring-indigo-500 dark:bg-indigo-500 dark:hover:bg-indigo-600',
    secondary: 'bg-white text-gray-700 ring-1 ring-inset ring-gray-300 shadow-sm hover:bg-gray-50 focus-visible:ring-indigo-500 dark:bg-gray-800 dark:text-gray-200 dark:ring-gray-600 dark:hover:bg-gray-700',
    danger:    'bg-red-600 text-white shadow-sm hover:bg-red-700 focus-visible:ring-red-500 dark:bg-red-500 dark:hover:bg-red-600',
    ghost:     'text-gray-600 hover:bg-gray-100 hover:text-gray-900 focus-visible:ring-indigo-500 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-100',
    link:      'text-indigo-600 underline-offset-4 hover:underline focus-visible:ring-indigo-500 dark:text-indigo-400',
};

const sizeClasses = {
    xs: 'h-7  px-2.5 text-xs  rounded',
    sm: 'h-8  px-3   text-sm  rounded-md',
    md: 'h-9  px-4   text-sm  rounded-md',
    lg: 'h-11 px-5   text-base rounded-lg',
};
</script>

<template>
    <component
        :is="as"
        v-bind="as === 'a' ? { href } : { type }"
        :disabled="as === 'button' && (disabled || loading)"
        :aria-disabled="disabled || loading"
        :aria-busy="loading"
        class="inline-flex items-center justify-center gap-2 font-medium whitespace-nowrap
               transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2
               disabled:pointer-events-none disabled:opacity-50 select-none"
        :class="[variantClasses[variant], sizeClasses[size]]"
    >
        <!-- Spinner -->
        <svg
            v-if="loading"
            class="shrink-0 animate-spin"
            :class="size === 'xs' || size === 'sm' ? 'size-3.5' : 'size-4'"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            aria-hidden="true"
        >
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
        </svg>

        <slot />
    </component>
</template>
