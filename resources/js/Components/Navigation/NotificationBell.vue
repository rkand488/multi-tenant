<script setup>
import { ref, computed } from 'vue';
import { onClickOutside } from '@vueuse/core';
import { usePage, router } from '@inertiajs/vue3';
import {
    BellIcon,
    BellAlertIcon,
    CheckCircleIcon,
    XMarkIcon,
    InformationCircleIcon,
    ExclamationTriangleIcon,
} from '@heroicons/vue/24/outline';

// ── Props / emits ─────────────────────────────────────────────────────────────
const props = defineProps({
    /** [{ id, title, body, type, read, created_at }] */
    notifications: { type: Array, default: () => [] },
    unreadCount:   { type: Number, default: 0 },
});

const emit = defineEmits(['mark-read', 'mark-all-read']);

// ── Panel state ───────────────────────────────────────────────────────────────
const open     = ref(false);
const panelRef = ref(null);

onClickOutside(panelRef, () => (open.value = false));

// ── Helpers ───────────────────────────────────────────────────────────────────
const hasUnread = computed(() => props.unreadCount > 0);
const badge     = computed(() => props.unreadCount > 99 ? '99+' : props.unreadCount);

const typeIcon = (type) => ({
    warning: ExclamationTriangleIcon,
    error:   ExclamationTriangleIcon,
    success: CheckCircleIcon,
})[type] ?? InformationCircleIcon;

const typeIconClass = (type) => ({
    warning: 'text-amber-500',
    error:   'text-red-500',
    success: 'text-green-500',
})[type] ?? 'text-indigo-500';

const markRead    = (id) => emit('mark-read', id);
const markAllRead = () => emit('mark-all-read');
</script>

<template>
    <div ref="panelRef" class="relative">
        <!-- ── Bell button ──────────────────────────────────────────────── -->
        <button
            class="relative flex size-9 items-center justify-center rounded-xl text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-800 dark:hover:text-gray-200"
            :class="open ? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-200' : ''"
            :aria-label="`${unreadCount} unread notifications`"
            @click="open = !open"
        >
            <BellAlertIcon v-if="hasUnread" class="size-5" />
            <BellIcon v-else class="size-5" />

            <!-- Unread badge -->
            <span
                v-if="hasUnread"
                class="absolute -right-0.5 -top-0.5 flex min-w-[1.1rem] items-center justify-center rounded-full bg-red-500 px-1 py-0.5 text-[9px] font-bold leading-none text-white ring-2 ring-white dark:ring-gray-900"
            >
                {{ badge }}
            </span>
        </button>

        <!-- ── Dropdown panel ───────────────────────────────────────────── -->
        <Transition
            enter-active-class="transition duration-150 ease-out"
            enter-from-class="-translate-y-1 scale-95 opacity-0"
            enter-to-class="translate-y-0 scale-100 opacity-100"
            leave-active-class="transition duration-100 ease-in"
            leave-from-class="translate-y-0 scale-100 opacity-100"
            leave-to-class="-translate-y-1 scale-95 opacity-0"
        >
            <div
                v-if="open"
                class="absolute right-0 top-11 z-50 w-80 origin-top-right rounded-2xl bg-white shadow-xl ring-1 ring-gray-200 dark:bg-gray-900 dark:ring-gray-700 sm:w-96"
            >
                <!-- Panel header -->
                <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3 dark:border-gray-800">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-semibold text-gray-900 dark:text-white">Notifications</span>
                        <span
                            v-if="hasUnread"
                            class="inline-flex items-center rounded-full bg-indigo-50 px-2 py-0.5 text-xs font-semibold text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400"
                        >
                            {{ unreadCount }} new
                        </span>
                    </div>

                    <div class="flex items-center gap-1">
                        <button
                            v-if="hasUnread"
                            class="rounded-lg px-2.5 py-1 text-xs font-medium text-indigo-600 transition hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-900/20"
                            @click="markAllRead"
                        >
                            Mark all read
                        </button>
                        <button
                            class="rounded-lg p-1 text-gray-400 transition hover:text-gray-600 dark:hover:text-gray-200"
                            @click="open = false"
                        >
                            <XMarkIcon class="size-4" />
                        </button>
                    </div>
                </div>

                <!-- Notification list -->
                <div class="max-h-96 overflow-y-auto">
                    <!-- Empty state -->
                    <div
                        v-if="!notifications.length"
                        class="flex flex-col items-center py-12 text-center"
                    >
                        <BellIcon class="size-10 text-gray-200 dark:text-gray-700" />
                        <p class="mt-2 text-sm font-medium text-gray-400 dark:text-gray-500">All caught up!</p>
                        <p class="mt-0.5 text-xs text-gray-300 dark:text-gray-600">No new notifications.</p>
                    </div>

                    <!-- Item list -->
                    <div
                        v-for="notif in notifications"
                        :key="notif.id"
                        class="group relative flex gap-3 border-b border-gray-50 px-4 py-3 transition last:border-0 dark:border-gray-800/60"
                        :class="!notif.read ? 'bg-indigo-50/40 dark:bg-indigo-900/5' : 'hover:bg-gray-50 dark:hover:bg-gray-800/40'"
                    >
                        <!-- Type icon -->
                        <div class="mt-0.5 shrink-0">
                            <component
                                :is="typeIcon(notif.type)"
                                class="size-4.5"
                                :class="typeIconClass(notif.type)"
                            />
                        </div>

                        <div class="min-w-0 flex-1">
                            <p
                                class="text-sm leading-snug"
                                :class="!notif.read
                                    ? 'font-semibold text-gray-900 dark:text-white'
                                    : 'font-medium text-gray-700 dark:text-gray-300'"
                            >
                                {{ notif.title }}
                            </p>
                            <p
                                v-if="notif.body"
                                class="mt-0.5 line-clamp-2 text-xs text-gray-500 dark:text-gray-400"
                            >
                                {{ notif.body }}
                            </p>
                            <p class="mt-1 text-[10px] text-gray-400 dark:text-gray-500">{{ notif.created_at }}</p>
                        </div>

                        <!-- Unread dot -->
                        <div
                            v-if="!notif.read"
                            class="mt-1.5 size-2 shrink-0 rounded-full bg-indigo-500"
                        />

                        <!-- Per-item mark-read on hover -->
                        <button
                            v-if="!notif.read"
                            class="absolute right-3 top-3 hidden rounded-lg p-1 text-gray-400 transition hover:bg-white hover:text-indigo-600 group-hover:flex dark:hover:bg-gray-800"
                            title="Mark as read"
                            @click.stop="markRead(notif.id)"
                        >
                            <CheckCircleIcon class="size-3.5" />
                        </button>
                    </div>
                </div>

                <!-- Footer -->
                <div
                    v-if="notifications.length"
                    class="border-t border-gray-100 px-4 py-2.5 text-center dark:border-gray-800"
                >
                    <button class="text-xs font-medium text-indigo-600 transition hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">
                        View all notifications
                    </button>
                </div>
            </div>
        </Transition>
    </div>
</template>
