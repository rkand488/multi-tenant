<script setup>
import { ref, computed } from 'vue';
import { onClickOutside } from '@vueuse/core';
import { Link, router, usePage } from '@inertiajs/vue3';
import {
    HomeIcon,
    BuildingOfficeIcon,
    CreditCardIcon,
    ChartBarIcon,
    CogIcon,
    Bars3Icon,
    BellIcon,
    ChevronDownIcon,
    UserCircleIcon,
    ArrowRightOnRectangleIcon,
    WrenchScrewdriverIcon,
    ClipboardDocumentListIcon,
} from '@heroicons/vue/24/outline';
import { CheckCircleIcon } from '@heroicons/vue/20/solid';
import { Menu, MenuButton, MenuItems, MenuItem } from '@headlessui/vue';

const page = usePage();
const auth  = computed(() => page.props.auth);
const flash = computed(() => page.props.flash);

const sidebarOpen       = ref(false);
const notifPanelOpen    = ref(false);
const notifRef          = ref(null);

onClickOutside(notifRef, () => (notifPanelOpen.value = false));

const navigation = [
    { label: 'Dashboard',     href: route('admin.dashboard'),           icon: HomeIcon,                    exact: true },
    { label: 'Tenants',       href: route('admin.tenants.index'),        icon: BuildingOfficeIcon },
    { label: 'Plans',         href: route('admin.plans.index'),          icon: CreditCardIcon },
    { label: 'Subscriptions', href: route('admin.subscriptions.index'),  icon: ClipboardDocumentListIcon },
    { label: 'Analytics',     href: route('admin.analytics.dashboard'),  icon: ChartBarIcon },
    { label: 'Settings',      href: route('admin.settings.index'),       icon: CogIcon },
];

const userMenu = [
    { label: 'Your Profile',  icon: UserCircleIcon, action: () => router.visit(route('admin.dashboard')) },
    { label: 'System Health', icon: WrenchScrewdriverIcon, action: () => router.visit(route('admin.settings.index')) },
];

// Demo notifications — in production these would come from page.props.notifications
const notifications = ref([
    { id: 1, title: 'New tenant registered', body: 'Acme Corp just signed up on the Starter plan.', time: '2m ago', read: false },
    { id: 2, title: 'Plan limit reached',    body: 'Globex Ltd. has hit their user quota.',          time: '1h ago', read: false },
    { id: 3, title: 'Invoice paid',          body: 'Invoice #1042 from Wayne Enterprises paid.',     time: '3h ago', read: true },
]);

const unreadCount = computed(() => notifications.value.filter((n) => !n.read).length);

const markAllRead  = () => notifications.value.forEach((n) => (n.read = true));
const markRead     = (n) => (n.read = true);
const toggleNotif  = () => (notifPanelOpen.value = !notifPanelOpen.value);

const signOut = () => router.post(route('logout'));

const isActive = (href, exact = false) => {
    const path = href.replace(window.location.origin, '');
    return exact ? page.url === path : page.url.startsWith(path);
};
</script>

<template>
    <div class="flex h-screen overflow-hidden bg-gray-100 dark:bg-gray-950">

        <!-- Mobile sidebar backdrop -->
        <div
            v-if="sidebarOpen"
            class="fixed inset-0 z-20 bg-black/50 lg:hidden"
            @click="sidebarOpen = false"
        />

        <!-- ─── Sidebar ────────────────────────────────────────────────── -->
        <aside
            class="fixed inset-y-0 left-0 z-30 flex w-64 flex-col bg-primary-800 transition-transform duration-200 ease-in-out lg:static lg:translate-x-0"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <!-- Logo -->
            <div class="flex h-16 shrink-0 items-center gap-3 border-b border-white/10 px-5">
                <img src="/logo.png" alt="Tenantrix" class="size-8 shrink-0 object-contain" />
                <span class="text-sm font-semibold tracking-wide text-white">Super Admin</span>
            </div>

            <!-- Nav -->
            <nav class="flex-1 space-y-0.5 overflow-y-auto px-3 py-4">
                <Link
                    v-for="item in navigation"
                    :key="item.label"
                    :href="item.href"
                    class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors"
                    :class="isActive(item.href, item.exact)
                        ? 'bg-white/15 text-white shadow-sm ring-1 ring-white/20'
                        : 'text-blue-100/80 hover:bg-white/10 hover:text-white'"
                >
                    <component :is="item.icon" class="size-5 shrink-0" />
                    {{ item.label }}
                </Link>
            </nav>

            <!-- Sidebar footer -->
            <div class="shrink-0 border-t border-white/10 px-4 py-3">
                <div class="flex items-center gap-3">
                    <div class="flex size-8 shrink-0 items-center justify-center rounded-full bg-white/20 text-xs font-semibold text-white ring-1 ring-white/30">
                        {{ auth?.user?.name?.[0]?.toUpperCase() ?? 'A' }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-xs font-medium text-white">{{ auth?.user?.name }}</p>
                        <p class="truncate text-xs text-gray-400">{{ auth?.user?.email }}</p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- ─── Main area ──────────────────────────────────────────────── -->
        <div class="flex flex-1 flex-col overflow-hidden">
            <!-- Top navbar -->
            <header class="flex h-16 shrink-0 items-center gap-4 border-b border-gray-200 bg-white px-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">

                <!-- Mobile menu toggle -->
                <button
                    class="rounded-md p-1.5 text-gray-500 transition-colors hover:bg-gray-100 hover:text-gray-700 lg:hidden dark:hover:bg-gray-800"
                    aria-label="Open sidebar"
                    @click="sidebarOpen = true"
                >
                    <Bars3Icon class="size-5" />
                </button>

                <!-- Mobile logo -->
                <img src="/logo.png" alt="Tenantrix" class="size-7 shrink-0 object-contain lg:hidden" />

                <!-- Page title slot (optional — filled via named slot from page) -->
                <div class="hidden flex-1 text-sm font-semibold text-gray-700 md:block dark:text-gray-200">
                    <slot name="heading" />
                </div>

                <div class="flex items-center justify-end gap-2">

                    <!-- ── Notifications ─────────────────────────────── -->
                    <div ref="notifRef" class="relative">
                        <button
                            type="button"
                            class="relative rounded-full p-1.5 text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-300"
                            aria-label="Notifications"
                            @click="toggleNotif"
                        >
                            <BellIcon class="size-5" />
                            <span
                                v-if="unreadCount > 0"
                                class="absolute right-0.5 top-0.5 flex size-4 items-center justify-center rounded-full bg-red-500 text-[9px] font-bold text-white"
                            >
                                {{ unreadCount > 9 ? '9+' : unreadCount }}
                            </span>
                        </button>

                        <!-- Notification panel -->
                        <Transition
                            enter-active-class="transition ease-out duration-100"
                            enter-from-class="scale-95 opacity-0"
                            enter-to-class="scale-100 opacity-100"
                            leave-active-class="transition ease-in duration-75"
                            leave-from-class="scale-100 opacity-100"
                            leave-to-class="scale-95 opacity-0"
                        >
                            <div
                                v-if="notifPanelOpen"
                                class="absolute right-0 z-50 mt-2 w-80 origin-top-right rounded-xl bg-white shadow-xl ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-gray-700"
                            >
                                <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3 dark:border-gray-700">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">Notifications</p>
                                    <button
                                        v-if="unreadCount > 0"
                                        type="button"
                                        class="text-xs text-primary-600 hover:underline dark:text-primary-400"
                                        @click="markAllRead"
                                    >
                                        Mark all read
                                    </button>
                                </div>

                                <ul class="max-h-72 overflow-y-auto divide-y divide-gray-50 dark:divide-gray-700/60">
                                    <li
                                        v-for="notif in notifications"
                                        :key="notif.id"
                                        class="flex gap-3 px-4 py-3 transition-colors hover:bg-gray-50 dark:hover:bg-gray-700/40"
                                        :class="!notif.read ? 'bg-primary-50/60 dark:bg-primary-900/10' : ''"
                                    >
                                        <div class="mt-0.5 shrink-0">
                                            <CheckCircleIcon
                                                v-if="notif.read"
                                                class="size-4 text-gray-300 dark:text-gray-600"
                                            />
                                            <span
                                                v-else
                                                class="block size-2 rounded-full bg-primary-500 mt-1"
                                            />
                                        </div>
                                        <div class="min-w-0 flex-1" @click="markRead(notif)">
                                            <p class="text-xs font-semibold text-gray-800 dark:text-gray-100">{{ notif.title }}</p>
                                            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ notif.body }}</p>
                                            <p class="mt-1 text-[10px] text-gray-400 dark:text-gray-500">{{ notif.time }}</p>
                                        </div>
                                    </li>
                                    <li v-if="notifications.length === 0" class="px-4 py-8 text-center text-sm text-gray-400">
                                        No notifications
                                    </li>
                                </ul>
                            </div>
                        </Transition>
                    </div>

                    <!-- ── User menu ──────────────────────────────────── -->
                    <Menu as="div" class="relative">
                        <MenuButton class="flex items-center gap-2 rounded-full py-1 pl-1 pr-2 text-sm text-gray-700 transition-colors hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-800">
                            <div class="flex size-8 items-center justify-center rounded-full bg-gradient-primary text-xs font-semibold text-white">
                                {{ auth?.user?.name?.[0]?.toUpperCase() ?? 'A' }}
                            </div>
                            <span class="hidden text-sm font-medium sm:block">{{ auth?.user?.name }}</span>
                            <ChevronDownIcon class="size-4 shrink-0 text-gray-400" />
                        </MenuButton>

                        <Transition
                            enter-active-class="transition ease-out duration-100"
                            enter-from-class="scale-95 opacity-0"
                            enter-to-class="scale-100 opacity-100"
                            leave-active-class="transition ease-in duration-75"
                            leave-from-class="scale-100 opacity-100"
                            leave-to-class="scale-95 opacity-0"
                        >
                            <MenuItems class="absolute right-0 z-10 mt-2 w-52 origin-top-right rounded-xl bg-white py-1 shadow-lg ring-1 ring-gray-200 focus:outline-none dark:bg-gray-800 dark:ring-gray-700">
                                <div class="border-b border-gray-100 px-4 py-2.5 dark:border-gray-700">
                                    <p class="text-xs font-semibold text-gray-800 dark:text-gray-100">{{ auth?.user?.name }}</p>
                                    <p class="mt-0.5 text-xs text-gray-400 truncate">{{ auth?.user?.email }}</p>
                                </div>

                                <div class="py-1">
                                    <MenuItem v-for="item in userMenu" :key="item.label" v-slot="{ active }">
                                        <button
                                            type="button"
                                            class="flex w-full items-center gap-2.5 px-4 py-2 text-sm transition-colors"
                                            :class="active ? 'bg-gray-100 text-gray-900 dark:bg-gray-700 dark:text-gray-100' : 'text-gray-700 dark:text-gray-200'"
                                            @click="item.action"
                                        >
                                            <component :is="item.icon" class="size-4 shrink-0 text-gray-400" />
                                            {{ item.label }}
                                        </button>
                                    </MenuItem>
                                </div>

                                <div class="border-t border-gray-100 pt-1 dark:border-gray-700">
                                    <MenuItem v-slot="{ active }">
                                        <button
                                            type="button"
                                            class="flex w-full items-center gap-2.5 px-4 py-2 text-sm transition-colors"
                                            :class="active ? 'bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400' : 'text-gray-700 dark:text-gray-200'"
                                            @click="signOut"
                                        >
                                            <ArrowRightOnRectangleIcon class="size-4 shrink-0 text-gray-400" />
                                            Sign out
                                        </button>
                                    </MenuItem>
                                </div>
                            </MenuItems>
                        </Transition>
                    </Menu>
                </div>
            </header>

            <!-- Flash messages -->
            <div v-if="flash?.success || flash?.error || flash?.warning" class="px-6 pt-4 space-y-2">
                <div v-if="flash?.success" class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-800 dark:bg-green-900/20 dark:text-green-400">
                    {{ flash.success }}
                </div>
                <div v-if="flash?.error" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400">
                    {{ flash.error }}
                </div>
                <div v-if="flash?.warning" class="rounded-lg border border-yellow-200 bg-yellow-50 px-4 py-3 text-sm text-yellow-700 dark:border-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400">
                    {{ flash.warning }}
                </div>
            </div>

            <!-- Page content -->
            <main class="flex-1 overflow-y-auto p-6">
                <slot />
            </main>
        </div>
    </div>
</template>
