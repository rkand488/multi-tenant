<script setup>
/**
 * Input
 *
 * @example
 * <!-- Controlled with v-model -->
 * <Input id="email" v-model="form.email" label="Email" type="email" required />
 *
 * <!-- With error message -->
 * <Input id="name" v-model="form.name" label="Full name" :error="form.errors.name" />
 *
 * <!-- With leading icon slot -->
 * <Input id="search" v-model="query" placeholder="Search…">
 *     <template #leading>
 *         <MagnifyingGlassIcon class="size-4 text-gray-400" />
 *     </template>
 * </Input>
 *
 * <!-- Textarea -->
 * <Input id="bio" v-model="form.bio" label="Bio" multiline :rows="4" />
 */

defineProps({
    id:          { type: String,          required: true },
    modelValue:  { type: [String, Number], default: '' },
    label:       { type: String,          default: null },
    type:        { type: String,          default: 'text' },
    placeholder: { type: String,          default: '' },
    error:       { type: String,          default: null },
    helpText:    { type: String,          default: null },
    disabled:    { type: Boolean,         default: false },
    required:    { type: Boolean,         default: false },
    /** Render a <textarea> instead of <input> */
    multiline:   { type: Boolean,         default: false },
    rows:        { type: Number,          default: 3 },
    autocomplete:{ type: String,          default: null },
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

        <div class="relative">
            <!-- Leading slot (icon) -->
            <div
                v-if="$slots.leading"
                class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"
                aria-hidden="true"
            >
                <slot name="leading" />
            </div>

            <!-- Input or Textarea -->
            <textarea
                v-if="multiline"
                :id="id"
                :value="modelValue"
                :placeholder="placeholder"
                :disabled="disabled"
                :required="required"
                :rows="rows"
                :aria-invalid="!!error"
                :aria-describedby="error ? `${id}-error` : helpText ? `${id}-help` : undefined"
                class="block w-full rounded-lg border bg-white px-3 py-2 text-sm text-gray-900 shadow-sm
                       placeholder:text-gray-400 transition-colors
                       focus:outline-none focus:ring-2 focus:border-transparent
                       disabled:cursor-not-allowed disabled:bg-gray-50 disabled:text-gray-500
                       dark:bg-gray-900 dark:text-gray-100 dark:placeholder:text-gray-500
                       dark:disabled:bg-gray-800 dark:disabled:text-gray-400"
                :class="[
                    error
                        ? 'border-red-400 focus:ring-red-500 dark:border-red-500'
                        : 'border-gray-300 focus:ring-indigo-500 dark:border-gray-600',
                ]"
                @input="$emit('update:modelValue', $event.target.value)"
            />

            <input
                v-else
                :id="id"
                :type="type"
                :value="modelValue"
                :placeholder="placeholder"
                :disabled="disabled"
                :required="required"
                :autocomplete="autocomplete"
                :aria-invalid="!!error"
                :aria-describedby="error ? `${id}-error` : helpText ? `${id}-help` : undefined"
                class="block w-full rounded-lg border bg-white py-2 text-sm text-gray-900 shadow-sm
                       placeholder:text-gray-400 transition-colors
                       focus:outline-none focus:ring-2 focus:border-transparent
                       disabled:cursor-not-allowed disabled:bg-gray-50 disabled:text-gray-500
                       dark:bg-gray-900 dark:text-gray-100 dark:placeholder:text-gray-500
                       dark:disabled:bg-gray-800 dark:disabled:text-gray-400"
                :class="[
                    $slots.leading ? 'pl-9' : 'pl-3',
                    $slots.trailing ? 'pr-9' : 'pr-3',
                    error
                        ? 'border-red-400 focus:ring-red-500 dark:border-red-500'
                        : 'border-gray-300 focus:ring-indigo-500 dark:border-gray-600',
                ]"
                @input="$emit('update:modelValue', $event.target.value)"
            />

            <!-- Trailing slot (icon / action) -->
            <div
                v-if="$slots.trailing"
                class="absolute inset-y-0 right-0 flex items-center pr-3"
                aria-hidden="true"
            >
                <slot name="trailing" />
            </div>
        </div>

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
