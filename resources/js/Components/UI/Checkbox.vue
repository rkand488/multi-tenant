<script setup>
/**
 * Checkbox
 *
 * @example
 * <!-- Basic toggle -->
 * <Checkbox id="remember" v-model="form.remember" label="Remember me" />
 *
 * <!-- With description -->
 * <Checkbox
 *     id="notifications"
 *     v-model="form.notifications"
 *     label="Email notifications"
 *     description="Receive updates about your workspace activity."
 * />
 *
 * <!-- With error -->
 * <Checkbox
 *     id="terms"
 *     v-model="form.terms"
 *     label="I agree to the terms"
 *     :error="form.errors.terms"
 * />
 *
 * <!-- Disabled -->
 * <Checkbox id="locked" :model-value="true" label="Always enabled" disabled />
 */

defineProps({
    id:          { type: String,  required: true },
    modelValue:  { type: Boolean, default: false },
    label:       { type: String,  default: null },
    description: { type: String,  default: null },
    error:       { type: String,  default: null },
    disabled:    { type: Boolean, default: false },
    /** Align checkbox to top when description is long */
    alignTop:    { type: Boolean, default: false },
});

defineEmits(['update:modelValue']);
</script>

<template>
    <div>
        <div class="flex gap-3" :class="alignTop ? 'items-start' : 'items-center'">
            <div class="flex h-5 shrink-0 items-center">
                <input
                    :id="id"
                    type="checkbox"
                    :checked="modelValue"
                    :disabled="disabled"
                    :aria-describedby="description ? `${id}-description` : error ? `${id}-error` : undefined"
                    class="size-4 cursor-pointer rounded border-gray-300 text-indigo-600 shadow-sm
                           transition-colors focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1
                           disabled:cursor-not-allowed disabled:opacity-50
                           dark:border-gray-600 dark:bg-gray-800 dark:checked:bg-indigo-500
                           dark:focus:ring-offset-gray-900"
                    :class="error ? 'border-red-400 dark:border-red-500' : ''"
                    @change="$emit('update:modelValue', $event.target.checked)"
                />
            </div>

            <div v-if="label || description" class="min-w-0">
                <label
                    :for="id"
                    class="block text-sm font-medium leading-none"
                    :class="disabled
                        ? 'cursor-not-allowed text-gray-400 dark:text-gray-500'
                        : 'cursor-pointer text-gray-700 dark:text-gray-300'"
                >
                    {{ label }}
                </label>
                <p
                    v-if="description"
                    :id="`${id}-description`"
                    class="mt-1 text-xs leading-relaxed text-gray-500 dark:text-gray-400"
                >
                    {{ description }}
                </p>
            </div>
        </div>

        <p
            v-if="error"
            :id="`${id}-error`"
            role="alert"
            class="mt-1.5 text-xs text-red-600 dark:text-red-400"
        >
            {{ error }}
        </p>
    </div>
</template>
