<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { Bars3Icon, ChevronLeftIcon, ChevronRightIcon } from '@heroicons/vue/24/outline';
import NotificationBell from '@/Components/Navigation/NotificationBell.vue';
import UserMenu from '@/Components/Navigation/UserMenu.vue';
import Breadcrumb from '@/Components/Navigation/Breadcrumb.vue';

// ── Props / emits ─────────────────────────────────────────────────────────────
const props = defineProps({
    /** Whether the desktop sidebar is in collapsed (icon-only) mode */
    collapsed:      { type: Boolean, default: false },
    /** Breadcrumb items passed through to Breadcrumb component */
    breadcrumbs:    { type: Array,   default: null },
    /** Notifications list forwarded to NotificationBell */
    notifications:  { type: Array,   default: () => [] },
    /** Unread notification count forwarded to NotificationBell */
    unreadCount:    { type: Number,  default: 0 },
});

const emit = defineEmits([
    /** Fired when mobile hamburger is pressed */
    'toggle-sidebar',
    /** Fired when desktop collapse icon is pressed */
    'toggle-collapsed',
    /** Bubbled from NotificationBell */
    'mark-read',
    /** Bubbled from NotificationBell */
    'mark-all-read',
]);
</script>

<template>
    <header
        class="flex h-16 shrink-0 items-center gap-3 border-b border-gray-200 bg-white px-4 dark:border-gray-800 dark:bg-gray-900"
    >
        <!-- Mobile hamburger -->
        <button
            class="flex size-9 items-center justify-center rounded-xl text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-800 dark:hover:text-gray-200 lg:hidden"
            aria-label="Open navigation"
            @click="emit('toggle-sidebar')"
        >
            <Bars3Icon class="size-5" />
        </button>

        <!-- Desktop collapse toggle -->
        <button
            class="hidden size-9 items-center justify-center rounded-xl text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-800 dark:hover:text-gray-200 lg:flex"
            :aria-label="collapsed ? 'Expand sidebar' : 'Collapse sidebar'"
            @click="emit('toggle-collapsed')"
        >
            <ChevronLeftIcon v-if="!collapsed" class="size-4" />
            <ChevronRightIcon v-else class="size-4" />
        </button>

        <!-- Divider -->
        <div class="h-6 w-px bg-gray-200 dark:bg-gray-700" />

        <!-- Breadcrumb -->
        <div class="flex-1 overflow-hidden">
            <slot name="breadcrumb">
                <Breadcrumb :items="breadcrumbs" />
            </slot>
        </div>

        <!-- Right-side actions -->
        <div class="flex shrink-0 items-center gap-1">
            <!-- Notification bell -->
            <NotificationBell
                :notifications="notifications"
                :unread-count="unreadCount"
                @mark-read="emit('mark-read', $event)"
                @mark-all-read="emit('mark-all-read')"
            />

            <!-- Divider -->
            <div class="mx-1 h-6 w-px bg-gray-200 dark:bg-gray-700" />

            <!-- User menu -->
            <UserMenu />
        </div>
    </header>
</template>
