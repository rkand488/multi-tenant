<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import {
    ChevronLeftIcon,
    ChevronRightIcon,
    BuildingOfficeIcon,
    XMarkIcon,
} from '@heroicons/vue/24/outline';

// ── Props / emits ─────────────────────────────────────────────────────────────
const props = defineProps({
    /** Navigation items: [{ label, href, icon, badge? }] */
    navigation: { type: Array, required: true },
    /** Mobile overlay open state (v-model:open) */
    open: { type: Boolean, default: false },
    /** Desktop icon-only collapsed state (v-model:collapsed) */
    collapsed: { type: Boolean, default: false },
});

const emit = defineEmits(['update:open', 'update:collapsed']);

const closeMobile   = () => emit('update:open', false);
const toggleCollapsed = () => emit('update:collapsed', !props.collapsed);

// ── Active route ─────────────────────────────────────────────────────────────
const page   = usePage();
const tenant = computed(() => page.props.tenant);

const isActive = (href) => {
    const path = href.replace(window.location.origin, '');
    // exact match for dashboard, prefix match for everything else
    if (path === '/dashboard') {
        return page.url === '/dashboard' || page.url === '/dashboard/';
    }
    return page.url.startsWith(path);
};
</script>

<template>
    <!-- ── Mobile backdrop ──────────────────────────────────────────────────── -->
    <Transition
        enter-active-class="transition-opacity duration-200"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition-opacity duration-200"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="open"
            class="fixed inset-0 z-20 bg-black/40 backdrop-blur-sm lg:hidden"
            aria-hidden="true"
            @click="closeMobile"
        />
    </Transition>

    <!-- ── Sidebar panel ────────────────────────────────────────────────────── -->
    <aside
        class="fixed inset-y-0 left-0 z-30 flex flex-col bg-white shadow-lg transition-all duration-300 ease-in-out
               dark:bg-gray-900 lg:static lg:shadow-none lg:border-r lg:border-gray-200 lg:dark:border-gray-800"
        :class="[
            open ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
            collapsed ? 'w-16' : 'w-64',
        ]"
    >
        <!-- ── Header: tenant identity ─────────────────────────────────────── -->
        <div
            class="flex h-16 shrink-0 items-center border-b border-gray-100 dark:border-gray-800"
            :class="collapsed ? 'justify-center px-0' : 'gap-3 px-5'"
        >
            <!-- Logo -->
            <div
                class="flex size-8 shrink-0 items-center justify-center"
            >
                <img src="/logo.png" alt="Tenantrix" class="size-8 object-contain" />
            </div>

            <!-- Tenant name + plan badge (hidden when collapsed) -->
            <div v-if="!collapsed" class="min-w-0 flex-1">
                <p class="truncate text-sm font-semibold text-gray-900 dark:text-white">
                    {{ tenant?.name ?? 'My Workspace' }}
                </p>
                <span
                    v-if="tenant?.plan_name"
                    class="inline-block rounded-full bg-primary-50 px-1.5 py-0.5 text-[10px] font-medium text-primary-600 dark:bg-primary-900/30 dark:text-primary-400"
                >
                    {{ tenant.plan_name }}
                </span>
            </div>

            <!-- Mobile close button -->
            <button
                class="ml-auto rounded-md p-1 text-gray-400 hover:text-gray-600 lg:hidden dark:hover:text-gray-200"
                @click="closeMobile"
            >
                <XMarkIcon class="size-5" />
            </button>
        </div>

        <!-- ── Navigation ──────────────────────────────────────────────────── -->
        <nav
            class="flex-1 overflow-y-auto overflow-x-hidden py-4"
            :class="collapsed ? 'px-2' : 'px-3'"
        >
            <Link
                v-for="item in navigation"
                :key="item.label"
                :href="item.href"
                class="group relative flex items-center rounded-xl text-sm font-medium transition-colors duration-150"
                :class="[
                    collapsed ? 'mb-1 justify-center px-0 py-2.5' : 'mb-0.5 gap-3 px-3 py-2',
                    isActive(item.href)
                        ? 'bg-primary-50 text-primary-700 dark:bg-primary-900/40 dark:text-primary-300'
                        : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white',
                ]"
                @click="closeMobile"
            >
                <!-- Active indicator bar -->
                <span
                    v-if="isActive(item.href)"
                    class="absolute left-0 top-1/2 h-5 w-0.5 -translate-y-1/2 rounded-full bg-primary-500"
                    aria-hidden="true"
                />

                <component
                    :is="item.icon"
                    class="size-5 shrink-0"
                    :class="isActive(item.href) ? 'text-primary-600 dark:text-primary-400' : 'text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300'"
                />

                <!-- Label (hidden when collapsed on desktop) -->
                <span v-if="!collapsed" class="flex-1 truncate">{{ item.label }}</span>

                <!-- Badge (e.g. unread count) -->
                <span
                    v-if="!collapsed && item.badge"
                    class="ml-auto inline-flex min-w-[1.25rem] items-center justify-center rounded-full bg-primary-100 px-1.5 py-0.5 text-xs font-semibold text-primary-700 dark:bg-primary-800 dark:text-primary-200"
                >
                    {{ item.badge }}
                </span>

                <!-- Tooltip when collapsed -->
                <span
                    v-if="collapsed"
                    class="pointer-events-none absolute left-full ml-3 hidden whitespace-nowrap rounded-lg bg-gray-900 px-2.5 py-1.5 text-xs font-medium text-white shadow-lg group-hover:block dark:bg-gray-700"
                >
                    {{ item.label }}
                </span>
            </Link>
        </nav>

        <!-- ── Footer: workspace URL + collapse toggle ─────────────────────── -->
        <div class="shrink-0 border-t border-gray-100 dark:border-gray-800" :class="collapsed ? 'p-2' : 'p-3'">
            <!-- Workspace slug (hidden when collapsed) -->
            <div
                v-if="!collapsed"
                class="mb-2 flex items-center gap-2 rounded-lg px-2 py-1.5 text-xs text-gray-400 dark:text-gray-500"
            >
                <BuildingOfficeIcon class="size-3.5 shrink-0" />
                <span class="truncate font-mono">{{ tenant?.slug ?? 'workspace' }}.app</span>
            </div>

            <!-- Collapse toggle (desktop only) -->
            <button
                class="hidden w-full items-center justify-center rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-800 dark:hover:text-gray-200 lg:flex"
                :title="collapsed ? 'Expand sidebar' : 'Collapse sidebar'"
                @click="toggleCollapsed"
            >
                <ChevronLeftIcon v-if="!collapsed" class="size-4" />
                <ChevronRightIcon v-else class="size-4" />
                <span v-if="!collapsed" class="ml-2 text-xs">Collapse</span>
            </button>
        </div>
    </aside>
</template>
