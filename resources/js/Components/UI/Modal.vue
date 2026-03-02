<script setup>
/**
 * Modal
 *
 * A Headless UI Dialog-based modal with focus trap and ESC-to-close.
 *
 * @example
 * <Modal :show="showModal" title="Confirm Deletion" max-width="md" @close="showModal = false">
 *     <p>Are you sure you want to delete this record? This action cannot be undone.</p>
 *
 *     <template #footer>
 *         <Button variant="danger" :loading="deleting" @click="confirmDelete">Delete</Button>
 *         <Button variant="secondary" @click="showModal = false">Cancel</Button>
 *     </template>
 * </Modal>
 */

import {
    Dialog,
    DialogPanel,
    DialogTitle,
    TransitionRoot,
    TransitionChild,
} from '@headlessui/vue';

const props = defineProps({
    /** Controls visibility */
    show: { type: Boolean, required: true },
    /** Dialog title — can be overridden with the #title slot */
    title: { type: String, default: null },
    /** Maximum width of the dialog panel */
    maxWidth: {
        type: String,
        default: 'lg',
        validator: (v) => ['sm', 'md', 'lg', 'xl', '2xl'].includes(v),
    },
    /** Allow closing via backdrop click or ESC key */
    closeable: { type: Boolean, default: true },
});

const emit = defineEmits(['close']);

const close = () => {
    if (props.closeable) {
        emit('close');
    }
};

const maxWidthClass = {
    sm:  'sm:max-w-sm',
    md:  'sm:max-w-md',
    lg:  'sm:max-w-lg',
    xl:  'sm:max-w-xl',
    '2xl': 'sm:max-w-2xl',
};
</script>

<template>
    <TransitionRoot as="template" :show="show">
        <Dialog
            class="relative z-50"
            :open="show"
            @close="close"
        >
            <!-- Backdrop -->
            <TransitionChild
                as="template"
                enter="ease-out duration-200"
                enter-from="opacity-0"
                enter-to="opacity-100"
                leave="ease-in duration-150"
                leave-from="opacity-100"
                leave-to="opacity-0"
            >
                <div
                    class="fixed inset-0 bg-black/40 backdrop-blur-sm dark:bg-black/60"
                    aria-hidden="true"
                />
            </TransitionChild>

            <!-- Panel container -->
            <div class="fixed inset-0 flex items-start justify-center overflow-y-auto p-4 sm:items-center sm:p-6">
                <TransitionChild
                    as="template"
                    enter="ease-out duration-200"
                    enter-from="scale-95 opacity-0"
                    enter-to="scale-100 opacity-100"
                    leave="ease-in duration-150"
                    leave-from="scale-100 opacity-100"
                    leave-to="scale-95 opacity-0"
                >
                    <DialogPanel
                        class="w-full rounded-2xl bg-white shadow-xl ring-1 ring-gray-200 dark:bg-gray-900 dark:ring-gray-700"
                        :class="maxWidthClass[maxWidth]"
                    >
                        <!-- Header -->
                        <div
                            v-if="title || $slots.title"
                            class="flex items-start justify-between gap-4 border-b border-gray-100 px-6 py-4 dark:border-gray-800"
                        >
                            <DialogTitle
                                as="h3"
                                class="text-base font-semibold text-gray-900 dark:text-gray-100"
                            >
                                <slot name="title">{{ title }}</slot>
                            </DialogTitle>

                            <!-- Close button -->
                            <button
                                v-if="closeable"
                                type="button"
                                class="-m-1 rounded-lg p-1 text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-300"
                                aria-label="Close"
                                @click="close"
                            >
                                <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" />
                                </svg>
                            </button>
                        </div>

                        <!-- Body -->
                        <div class="px-6 py-5 text-sm text-gray-600 dark:text-gray-300">
                            <slot />
                        </div>

                        <!-- Footer -->
                        <div
                            v-if="$slots.footer"
                            class="flex flex-row-reverse items-center gap-3 border-t border-gray-100 px-6 py-4 dark:border-gray-800"
                        >
                            <slot name="footer" />
                        </div>
                    </DialogPanel>
                </TransitionChild>
            </div>
        </Dialog>
    </TransitionRoot>
</template>
