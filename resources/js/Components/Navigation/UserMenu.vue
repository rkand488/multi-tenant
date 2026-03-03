<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import {
    UserCircleIcon,
    Cog6ToothIcon,
    CreditCardIcon,
    BuildingOfficeIcon,
    ArrowRightOnRectangleIcon,
    ChevronDownIcon,
    ChevronUpDownIcon,
} from '@heroicons/vue/24/outline';
import {
    Menu,
    MenuButton,
    MenuItems,
    MenuItem,
} from '@headlessui/vue';

// ── Data ──────────────────────────────────────────────────────────────────────
const page   = usePage();
const auth   = computed(() => page.props.auth);
const tenant = computed(() => page.props.tenant);

const initials = computed(() => {
    const name = auth.value?.user?.name ?? '';
    return name
        .split(' ')
        .map((n) => n[0])
        .slice(0, 2)
        .join('')
        .toUpperCase() || '?';
});
</script>

<template>
    <Menu as="div" class="relative">
        <!-- ── Trigger ────────────────────────────────────────────────── -->
        <MenuButton
            class="group flex items-center gap-2.5 rounded-xl px-2.5 py-1.5 text-sm text-gray-700 transition hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-800"
        >
            <!-- Avatar -->
            <span class="flex size-7 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-primary-600 to-secondary-500 text-xs font-bold text-white ring-2 ring-white dark:ring-gray-900">
                {{ initials }}
            </span>

            <!-- Name + tenant (collapsed on small screens) -->
            <span class="hidden flex-col items-start leading-none sm:flex">
                <span class="text-xs font-semibold text-gray-900 dark:text-white">{{ auth?.user?.name ?? 'User' }}</span>
                <span class="mt-0.5 text-[10px] text-gray-400 dark:text-gray-500">{{ tenant?.name ?? 'Workspace' }}</span>
            </span>

            <ChevronUpDownIcon class="size-3.5 text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300" />
        </MenuButton>

        <!-- ── Dropdown ───────────────────────────────────────────────── -->
        <Transition
            enter-active-class="transition duration-150 ease-out"
            enter-from-class="-translate-y-1 scale-95 opacity-0"
            enter-to-class="translate-y-0 scale-100 opacity-100"
            leave-active-class="transition duration-100 ease-in"
            leave-from-class="translate-y-0 scale-100 opacity-100"
            leave-to-class="-translate-y-1 scale-95 opacity-0"
        >
            <MenuItems
                class="absolute right-0 top-11 z-50 w-60 origin-top-right divide-y divide-gray-100 rounded-2xl bg-white shadow-xl ring-1 ring-gray-200 focus:outline-none dark:divide-gray-800 dark:bg-gray-900 dark:ring-gray-700"
            >
                <!-- Account header -->
                <div class="px-4 py-3">
                    <div class="flex items-center gap-3">
                        <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-primary-600 to-secondary-500 text-sm font-bold text-white">
                            {{ initials }}
                        </span>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-gray-900 dark:text-white">{{ auth?.user?.name ?? 'User' }}</p>
                            <p class="truncate text-xs text-gray-400">{{ auth?.user?.email ?? '' }}</p>
                        </div>
                    </div>

                    <!-- Tenant context -->
                    <div
                        v-if="tenant"
                        class="mt-3 flex items-center gap-2 rounded-lg bg-gray-50 px-2.5 py-2 dark:bg-gray-800"
                    >
                        <BuildingOfficeIcon class="size-3.5 shrink-0 text-gray-400" />
                        <span class="truncate text-xs text-gray-600 dark:text-gray-300">{{ tenant.name }}</span>
                        <span
                            v-if="tenant.plan_name"
                            class="ml-auto shrink-0 rounded-full bg-primary-50 px-1.5 py-0.5 text-[9px] font-semibold text-primary-600 dark:bg-primary-900/40 dark:text-primary-400"
                        >
                            {{ tenant.plan_name }}
                        </span>
                    </div>
                </div>

                <!-- Navigation links -->
                <div class="py-1">
                    <MenuItem v-slot="{ active }">
                        <Link
                            :href="route('tenant.settings.profile')"
                            class="flex items-center gap-3 px-4 py-2 text-sm transition"
                            :class="active ? 'bg-gray-50 text-gray-900 dark:bg-gray-800 dark:text-white' : 'text-gray-700 dark:text-gray-300'"
                        >
                            <UserCircleIcon class="size-4 text-gray-400" />
                            Profile Settings
                        </Link>
                    </MenuItem>

                    <MenuItem v-slot="{ active }">
                        <Link
                            :href="route('tenant.settings.team')"
                            class="flex items-center gap-3 px-4 py-2 text-sm transition"
                            :class="active ? 'bg-gray-50 text-gray-900 dark:bg-gray-800 dark:text-white' : 'text-gray-700 dark:text-gray-300'"
                        >
                            <Cog6ToothIcon class="size-4 text-gray-400" />
                            Team Settings
                        </Link>
                    </MenuItem>

                    <MenuItem v-slot="{ active }">
                        <Link
                            :href="route('tenant.billing.index')"
                            class="flex items-center gap-3 px-4 py-2 text-sm transition"
                            :class="active ? 'bg-gray-50 text-gray-900 dark:bg-gray-800 dark:text-white' : 'text-gray-700 dark:text-gray-300'"
                        >
                            <CreditCardIcon class="size-4 text-gray-400" />
                            Billing
                        </Link>
                    </MenuItem>
                </div>

                <!-- Sign out -->
                <div class="py-1">
                    <MenuItem v-slot="{ active }">
                        <Link
                            :href="route('logout')"
                            method="post"
                            as="button"
                            class="flex w-full items-center gap-3 px-4 py-2 text-sm transition"
                            :class="active ? 'bg-red-50 text-red-600 dark:bg-red-900/20 dark:text-red-400' : 'text-gray-600 dark:text-gray-400'"
                        >
                            <ArrowRightOnRectangleIcon class="size-4" />
                            Sign out
                        </Link>
                    </MenuItem>
                </div>
            </MenuItems>
        </Transition>
    </Menu>
</template>
