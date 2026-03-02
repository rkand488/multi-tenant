<script setup>
/**
 * Pagination
 *
 * Accepts the `links` and `meta` objects from a standard Laravel paginator response.
 *
 * @example
 * <Pagination :links="users.links" :meta="users.meta" @change="page => router.get(route('users.index', { page }))" />
 */

import { computed } from 'vue';

const props = defineProps({
    /**
     * Laravel pagination links array:
     * [{ url: string|null, label: string, active: boolean }]
     */
    links: { type: Array, required: true },
    /**
     * Laravel pagination meta object:
     * { from, to, total, current_page, last_page, per_page }
     */
    meta: { type: Object, required: true },
});

const emit = defineEmits(['change']);

/**
 * Strip the first ("« Previous") and last ("Next »") entries — we render
 * prev/next controls separately — and return the numeric page links.
 */
const pageLinks = computed(() => {
    const inner = props.links.slice(1, -1);

    // For large page counts, insert `null` (ellipsis) elements.
    const current = props.meta.current_page;
    const last    = props.meta.last_page;

    if (last <= 7) { return inner; }

    // Build a condensed set: first, …, (current-1)…(current+1), …, last
    const pages = [];
    inner.forEach((link, idx) => {
        const pageNum = idx + 1; // 1-based
        const nearCurrent  = pageNum >= current - 1 && pageNum <= current + 1;
        const isEdge       = pageNum === 1 || pageNum === last;
        if (nearCurrent || isEdge) {
            pages.push(link);
        } else if (pages.length && pages[pages.length - 1] !== null) {
            pages.push(null); // ellipsis marker
        }
    });
    return pages;
});

const prevLink = computed(() => props.links[0] ?? null);
const nextLink = computed(() => props.links[props.links.length - 1] ?? null);

const changeTo = (link) => {
    if (!link || !link.url || link.active) { return; }

    const url   = new URL(link.url);
    const page  = Number(url.searchParams.get('page') ?? 1);
    emit('change', page);
};

const changeToPage = (pageNum) => {
    if (pageNum < 1 || pageNum > props.meta.last_page) { return; }
    emit('change', pageNum);
};
</script>

<template>
    <div
        v-if="meta.last_page > 1"
        class="flex flex-col items-center gap-3 sm:flex-row sm:justify-between"
        role="navigation"
        aria-label="Pagination"
    >
        <!-- Results summary -->
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Showing
            <span class="font-medium text-gray-700 dark:text-gray-200">{{ meta.from }}</span>
            to
            <span class="font-medium text-gray-700 dark:text-gray-200">{{ meta.to }}</span>
            of
            <span class="font-medium text-gray-700 dark:text-gray-200">{{ meta.total }}</span>
            results
        </p>

        <!-- Page controls -->
        <div class="flex items-center gap-1">

            <!-- Previous -->
            <button
                type="button"
                class="inline-flex h-8 items-center gap-1 rounded-md px-2.5 text-sm font-medium text-gray-600 transition-colors hover:bg-gray-100 disabled:pointer-events-none disabled:opacity-40 dark:text-gray-300 dark:hover:bg-gray-700"
                :disabled="!prevLink?.url"
                :aria-label="'Previous page'"
                @click="changeToPage(meta.current_page - 1)"
            >
                <svg class="size-4 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M11.78 5.22a.75.75 0 0 1 0 1.06L8.06 10l3.72 3.72a.75.75 0 1 1-1.06 1.06l-4.25-4.25a.75.75 0 0 1 0-1.06l4.25-4.25a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd" />
                </svg>
                <span class="hidden sm:inline">Previous</span>
            </button>

            <!-- Page numbers -->
            <template v-for="(link, idx) in pageLinks" :key="idx">
                <!-- Ellipsis -->
                <span
                    v-if="link === null"
                    class="px-2 text-sm text-gray-400 dark:text-gray-500"
                    aria-hidden="true"
                >…</span>

                <!-- Page button -->
                <button
                    v-else
                    type="button"
                    class="inline-flex h-8 min-w-[2rem] items-center justify-center rounded-md px-2 text-sm font-medium transition-colors"
                    :class="link.active
                        ? 'bg-indigo-600 text-white shadow-sm'
                        : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700'"
                    :aria-label="`Page ${link.label}`"
                    :aria-current="link.active ? 'page' : undefined"
                    :disabled="link.active"
                    @click="changeTo(link)"
                    v-html="link.label"
                />
            </template>

            <!-- Next -->
            <button
                type="button"
                class="inline-flex h-8 items-center gap-1 rounded-md px-2.5 text-sm font-medium text-gray-600 transition-colors hover:bg-gray-100 disabled:pointer-events-none disabled:opacity-40 dark:text-gray-300 dark:hover:bg-gray-700"
                :disabled="!nextLink?.url"
                :aria-label="'Next page'"
                @click="changeToPage(meta.current_page + 1)"
            >
                <span class="hidden sm:inline">Next</span>
                <svg class="size-4 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M8.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 1 1-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>
    </div>
</template>
