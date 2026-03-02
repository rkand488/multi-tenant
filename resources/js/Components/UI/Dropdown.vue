<script setup>
/**
 * Dropdown
 *
 * A Headless UI Menu-based dropdown for action menus and context menus.
 * Provide the trigger and items through named slots.
 *
 * @example
 * <Dropdown align="right" width="48">
 *     <template #trigger>
 *         <Button variant="ghost" size="sm">
 *             Actions
 *             <ChevronDownIcon class="ml-1 size-4" />
 *         </Button>
 *     </template>
 *
 *     <template #items>
 *         <DropdownItem @click="edit(row)">Edit</DropdownItem>
 *         <DropdownItem variant="danger" @click="destroy(row)">Delete</DropdownItem>
 *     </template>
 * </Dropdown>
 */

import { Menu, MenuButton, MenuItems } from '@headlessui/vue';

defineProps({
    /** Which side the panel opens towards */
    align: {
        type: String,
        default: 'right',
        validator: (v) => ['left', 'right'].includes(v),
    },
    /** Panel width in Tailwind rem scale */
    width: {
        type: String,
        default: '48',
        validator: (v) => ['40', '48', '56', '64'].includes(v),
    },
});
</script>

<template>
    <Menu as="div" class="relative inline-block text-left">
        <!-- Trigger -->
        <MenuButton as="template">
            <slot name="trigger" />
        </MenuButton>

        <!-- Panel -->
        <Transition
            enter-active-class="transition ease-out duration-100"
            enter-from-class="scale-95 opacity-0"
            enter-to-class="scale-100 opacity-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="scale-100 opacity-100"
            leave-to-class="scale-95 opacity-0"
        >
            <MenuItems
                class="absolute z-50 mt-1 origin-top rounded-xl bg-white shadow-lg ring-1 ring-gray-200 focus:outline-none dark:bg-gray-800 dark:ring-gray-700"
                :class="[
                    align === 'right' ? 'right-0' : 'left-0',
                    `w-${width}`,
                ]"
            >
                <div class="py-1">
                    <slot name="items" />
                </div>
            </MenuItems>
        </Transition>
    </Menu>
</template>

