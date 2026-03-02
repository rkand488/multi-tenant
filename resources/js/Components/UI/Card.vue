<script setup>
/**
 * Card
 *
 * @example
 * <!-- Basic -->
 * <Card title="Users" subtitle="Manage workspace members">
 *     <p>Body content</p>
 * </Card>
 *
 * <!-- With header action and footer -->
 * <Card>
 *     <template #header>
 *         <span class="font-semibold">Team</span>
 *         <Button size="sm">Invite</Button>
 *     </template>
 *     <p>Body</p>
 *     <template #footer>
 *         <Button variant="secondary" size="sm">Cancel</Button>
 *     </template>
 * </Card>
 *
 * <!-- No padding (e.g. for a table inside) -->
 * <Card :padded="false">
 *     <DataTable />
 * </Card>
 */

defineProps({
    /** Card title rendered in default header area */
    title:    { type: String, default: null },
    /** Subtitle beneath the title */
    subtitle: { type: String, default: null },
    /** Apply px/py padding to the body slot */
    padded:   { type: Boolean, default: true },
    /** Remove the drop-shadow, keep ring border only */
    flat:     { type: Boolean, default: false },
});
</script>

<template>
    <div
        class="rounded-xl bg-white ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-gray-700"
        :class="flat ? '' : 'shadow-sm'"
    >
        <!-- Default header (title/subtitle) -->
        <div
            v-if="title || $slots.header"
            class="flex items-center justify-between border-b border-gray-200 px-5 py-4 dark:border-gray-700"
        >
            <slot name="header">
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ title }}</h3>
                    <p v-if="subtitle" class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ subtitle }}</p>
                </div>
            </slot>
        </div>

        <!-- Body -->
        <div :class="padded ? 'px-5 py-4' : ''">
            <slot />
        </div>

        <!-- Footer -->
        <div
            v-if="$slots.footer"
            class="flex items-center justify-end gap-3 border-t border-gray-200 px-5 py-3 dark:border-gray-700"
        >
            <slot name="footer" />
        </div>
    </div>
</template>
