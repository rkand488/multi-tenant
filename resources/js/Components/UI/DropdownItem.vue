<script setup>
/**
 * DropdownItem
 *
 * Must be used inside a Dropdown's #items slot.
 *
 * @example
 * <template #items>
 *     <DropdownItem @click="router.visit(route('users.edit', user))">Edit</DropdownItem>
 *     <DropdownItem variant="danger" @click="destroy(user)">Delete</DropdownItem>
 * </template>
 */

import { MenuItem } from '@headlessui/vue';

defineProps({
    /** Visual style */
    variant: {
        type: String,
        default: 'default',
        validator: (v) => ['default', 'danger'].includes(v),
    },
    disabled: { type: Boolean, default: false },
    /** Render as an anchor (`<a>`) instead of a `<button>` */
    href: { type: String, default: null },
});

const emit = defineEmits(['click']);
</script>

<template>
    <MenuItem v-slot="{ active }" :disabled="disabled">
        <component
            :is="href ? 'a' : 'button'"
            :href="href"
            :type="href ? undefined : 'button'"
            :disabled="!href && disabled"
            class="flex w-full items-center gap-2 px-4 py-2 text-sm transition-colors"
            :class="[
                disabled
                    ? 'cursor-not-allowed text-gray-300 dark:text-gray-600'
                    : [
                        variant === 'danger'
                            ? active
                                ? 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-400'
                                : 'text-red-600 dark:text-red-400'
                            : active
                                ? 'bg-gray-100 text-gray-900 dark:bg-gray-700 dark:text-gray-100'
                                : 'text-gray-700 dark:text-gray-200',
                    ],
            ]"
            @click="!disabled && emit('click', $event)"
        >
            <slot />
        </component>
    </MenuItem>
</template>
