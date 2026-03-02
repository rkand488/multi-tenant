<script setup>
/**
 * Select
 *
 * @example
 * <Select
 *     id="role"
 *     v-model="form.role"
 *     label="Role"
 *     :options="[
 *         { value: 'admin',  label: 'Administrator' },
 *         { value: 'member', label: 'Member' },
 *         { value: 'viewer', label: 'Viewer' },
 *     ]"
 *     :error="form.errors.role"
 * />
 *
 * <!-- With option groups -->
 * <Select id="plan" v-model="plan" label="Plan" :groups="planGroups" />
 */

defineProps({
    id:          { type: String,          required: true },
    modelValue:  { type: [String, Number, null], default: null },
    /** Flat list of options: [{ value, label, disabled? }] */
    options:     { type: Array,           default: () => [] },
    /** Grouped options: [{ group, items: [{ value, label }] }] */
    groups:      { type: Array,           default: null },
    label:       { type: String,          default: null },
    placeholder: { type: String,          default: 'Select an option' },
    error:       { type: String,          default: null },
    helpText:    { type: String,          default: null },
    disabled:    { type: Boolean,         default: false },
    required:    { type: Boolean,         default: false },
});

defineEmits(['update:modelValue']);
</script>

<template>
    <div class="space-y-1">
        <label
            v-if="label"
            :for="id"
            class="block text-sm font-medium text-gray-700 dark:text-gray-300"
        >
            {{ label }}
            <span v-if="required" class="ml-0.5 text-red-500" aria-hidden="true">*</span>
        </label>

        <select
            :id="id"
            :value="modelValue"
            :disabled="disabled"
            :required="required"
            :aria-invalid="!!error"
            :aria-describedby="error ? `${id}-error` : helpText ? `${id}-help` : undefined"
            class="block w-full appearance-none rounded-lg border bg-white py-2 pl-3 pr-8 text-sm
                   text-gray-900 shadow-sm transition-colors
                   focus:outline-none focus:ring-2 focus:border-transparent focus:ring-indigo-500
                   disabled:cursor-not-allowed disabled:bg-gray-50 disabled:text-gray-500
                   dark:bg-gray-900 dark:text-gray-100 dark:focus:ring-indigo-400
                   dark:disabled:bg-gray-800 dark:disabled:text-gray-400"
            :class="[
                error
                    ? 'border-red-400 dark:border-red-500'
                    : 'border-gray-300 dark:border-gray-600',
            ]"
            @change="$emit('update:modelValue', $event.target.value)"
        >
            <option value="" disabled :selected="!modelValue">{{ placeholder }}</option>

            <!-- Grouped options -->
            <template v-if="groups">
                <optgroup v-for="grp in groups" :key="grp.group" :label="grp.group">
                    <option
                        v-for="opt in grp.items"
                        :key="opt.value"
                        :value="opt.value"
                        :disabled="opt.disabled"
                    >
                        {{ opt.label }}
                    </option>
                </optgroup>
            </template>

            <!-- Flat options -->
            <template v-else>
                <option
                    v-for="opt in options"
                    :key="opt.value"
                    :value="opt.value"
                    :disabled="opt.disabled"
                >
                    {{ opt.label }}
                </option>
            </template>
        </select>

        <!-- Chevron icon overlay (non-interactive) -->
        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2" aria-hidden="true" />

        <p
            v-if="error"
            :id="`${id}-error`"
            role="alert"
            class="text-xs text-red-600 dark:text-red-400"
        >
            {{ error }}
        </p>
        <p
            v-else-if="helpText"
            :id="`${id}-help`"
            class="text-xs text-gray-500 dark:text-gray-400"
        >
            {{ helpText }}
        </p>
    </div>
</template>
