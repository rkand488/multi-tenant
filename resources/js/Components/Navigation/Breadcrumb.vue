<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { HomeIcon, ChevronRightIcon } from '@heroicons/vue/20/solid';

// ── Props ─────────────────────────────────────────────────────────────────────
const props = defineProps({
    /**
     * Breadcrumb items: [{ label: string, href?: string }]
     * Last item is treated as the current page (no link rendered).
     * Falls back to page.props.breadcrumbs if not provided.
     */
    items: { type: Array, default: null },
});

// ── Data ──────────────────────────────────────────────────────────────────────
const page  = usePage();

const crumbs = computed(() =>
    props.items ?? page.props.breadcrumbs ?? [],
);
</script>

<template>
    <nav aria-label="Breadcrumb" class="flex items-center gap-1 text-sm">
        <!-- Home icon always links to dashboard -->
        <Link
            :href="route('tenant.dashboard')"
            class="flex shrink-0 items-center text-gray-400 transition hover:text-indigo-600 dark:text-gray-500 dark:hover:text-indigo-400"
            aria-label="Home"
        >
            <HomeIcon class="size-4" />
        </Link>

        <template v-for="(crumb, idx) in crumbs" :key="idx">
            <ChevronRightIcon class="size-3.5 shrink-0 text-gray-300 dark:text-gray-600" aria-hidden="true" />

            <!-- Last item = current page (no link) -->
            <span
                v-if="idx === crumbs.length - 1"
                class="max-w-[180px] truncate font-medium text-gray-800 dark:text-gray-200"
                aria-current="page"
            >
                {{ crumb.label }}
            </span>

            <!-- Ancestor item -->
            <Link
                v-else
                :href="crumb.href ?? '#'"
                class="max-w-[150px] truncate text-gray-500 transition hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400"
            >
                {{ crumb.label }}
            </Link>
        </template>
    </nav>
</template>
