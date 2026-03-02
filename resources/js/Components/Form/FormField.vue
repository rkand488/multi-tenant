<script setup>
/**
 * FormField
 *
 * Generic field wrapper: label → default slot (your input) → error/help text.
 *
 * Pair with any input element or the existing <Input>, <Select>, or
 * <Checkbox> components. Error messages are rendered via <FormError>
 * and animate in/out automatically.
 *
 * If you pass both `error` and `helpText`, the error takes priority and the
 * help text is hidden until the error is cleared.
 *
 * @example
 * <!-- Wrapping the UI Input component -->
 * <FormField
 *     id="email"
 *     label="Email address"
 *     :error="form.errors.email"
 *     required
 * >
 *     <Input
 *         id="email"
 *         v-model="form.email"
 *         type="email"
 *         :error="form.errors.email"
 *     />
 * </FormField>
 *
 * <!-- Help text shown when there is no error -->
 * <FormField id="slug" label="Slug" :error="form.errors.slug" help-text="Lowercase letters and dashes only.">
 *     <Input id="slug" v-model="form.slug" :error="form.errors.slug" />
 * </FormField>
 *
 * <!-- Inline layout with a custom input element -->
 * <FormField id="active" label="Active" horizontal>
 *     <input id="active" v-model="form.active" type="checkbox" />
 * </FormField>
 */

import FormError from '@/Components/Form/FormError.vue';

defineProps({
    /** Must match the `id` on the child input so the label is clickable. */
    id:       { type: String,  default: null },
    /** Label text rendered in a <label>. Omit to suppress the label element. */
    label:    { type: String,  default: null },
    /** Error string — e.g. `form.errors.fieldName`. Shows FormError when truthy. */
    error:    { type: String,  default: null },
    /** Hint shown below the input when `error` is absent. */
    helpText: { type: String,  default: null },
    /** Appends a required asterisk to the label. */
    required: { type: Boolean, default: false },
    /**
     * When true, renders label and input side-by-side (e.g. for checkboxes).
     * The label receives `items-center` flex alignment.
     */
    horizontal: { type: Boolean, default: false },
});
</script>

<template>
    <div :class="horizontal ? 'flex items-center gap-3' : 'space-y-1.5'">

        <!-- Label -->
        <label
            v-if="label"
            :for="id"
            class="block text-sm font-medium text-gray-700 dark:text-gray-300"
            :class="horizontal ? 'order-2 cursor-pointer' : ''"
        >
            {{ label }}
            <span v-if="required && !horizontal" class="ml-0.5 text-red-500" aria-hidden="true">*</span>
        </label>

        <!-- Input slot -->
        <div :class="horizontal ? 'order-1' : ''">
            <slot />
        </div>

        <!-- Error or help text (not shown in horizontal mode to keep layout clean) -->
        <template v-if="!horizontal">
            <FormError
                v-if="error"
                :message="error"
                :id="id ? `${id}-error` : undefined"
            />
            <p
                v-else-if="helpText"
                :id="id ? `${id}-help` : undefined"
                class="text-xs text-gray-500 dark:text-gray-400"
            >
                {{ helpText }}
            </p>
        </template>
    </div>
</template>
