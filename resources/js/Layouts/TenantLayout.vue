<script setup>
import { ref, computed, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import {
    HomeIcon,
    UsersIcon,
    ShieldCheckIcon,
    CreditCardIcon,
    Cog6ToothIcon,
    ClipboardDocumentListIcon,
    ClipboardDocumentCheckIcon,
    ChartBarIcon,
    FolderIcon,
} from '@heroicons/vue/24/outline';
import Sidebar from '@/Components/Navigation/Sidebar.vue';
import Topbar from '@/Components/Navigation/Topbar.vue';
import { useNotificationStore } from '@/Stores';

// ── Shared page data ──────────────────────────────────────────────────────────
const page  = usePage();
const flash = computed(() => page.props.flash);

// ── Notification store ────────────────────────────────────────────────────────
const notifStore = useNotificationStore();

onMounted(() => {
    // Seed from Inertia shared props on first load
    notifStore.syncFromInertia();
});

// ── Sidebar state ─────────────────────────────────────────────────────────────
/** Mobile overlay visible */
const mobileOpen = ref(false);
/** Desktop icon-only collapsed mode (persisted in localStorage) */
const collapsed  = ref(localStorage.getItem('sidebar-collapsed') === 'true');

const setCollapsed = (value) => {
    collapsed.value = value;
    localStorage.setItem('sidebar-collapsed', String(value));
};

// ── Navigation items ──────────────────────────────────────────────────────────
const navigation = [
    { label: 'Dashboard',    href: route('tenant.dashboard'),           icon: HomeIcon },
    { label: 'Users',        href: route('tenant.users.index'),          icon: UsersIcon },
    { label: 'Roles',        href: route('tenant.roles.index'),          icon: ShieldCheckIcon },
    { label: 'Files',        href: route('tenant.files.index'),          icon: FolderIcon },
    { label: 'Usage',        href: route('tenant.usage.index'),          icon: ChartBarIcon },
    { label: 'Billing',      href: route('tenant.billing.index'),        icon: CreditCardIcon },
    { label: 'Settings',     href: route('tenant.settings.index'),       icon: Cog6ToothIcon },
    { label: 'Activity Log', href: route('tenant.activity-log.index'),   icon: ClipboardDocumentListIcon },
    { label: 'Audit Log',    href: route('tenant.audit-logs.index'),     icon: ClipboardDocumentCheckIcon },
];
</script>

<template>
    <div class="flex h-screen overflow-hidden bg-gray-50 dark:bg-gray-950">

        <!-- ── Sidebar ──────────────────────────────────────────────────────── -->
        <Sidebar
            :navigation="navigation"
            :open="mobileOpen"
            :collapsed="collapsed"
            @update:open="mobileOpen = $event"
            @update:collapsed="setCollapsed($event)"
        />

        <!-- ── Main column ──────────────────────────────────────────────────── -->
        <div class="flex min-w-0 flex-1 flex-col overflow-hidden">

            <!-- Topbar -->
            <Topbar
                :collapsed="collapsed"
                :notifications="notifStore.recentItems"
                :unread-count="notifStore.unreadCount"
                @toggle-sidebar="mobileOpen = true"
                @toggle-collapsed="setCollapsed(!collapsed)"
                @mark-read="notifStore.markRead($event)"
                @mark-all-read="notifStore.markAllRead()"
            />

            <!-- Flash messages -->
            <div
                v-if="flash?.success || flash?.error || flash?.warning"
                class="space-y-2 px-6 pt-4"
            >
                <div
                    v-if="flash.success"
                    class="flex items-start gap-2.5 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-800/50 dark:bg-green-900/20 dark:text-green-300"
                >
                    {{ flash.success }}
                </div>
                <div
                    v-if="flash.error"
                    class="flex items-start gap-2.5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-800/50 dark:bg-red-900/20 dark:text-red-300"
                >
                    {{ flash.error }}
                </div>
                <div
                    v-if="flash.warning"
                    class="flex items-start gap-2.5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-800/50 dark:bg-amber-900/20 dark:text-amber-300"
                >
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
