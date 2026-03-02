<script setup>
/**
 * SubmitButton
 *
 * An Inertia-aware submit button. Bind `form.processing` to the
 * `processing` prop and the button automatically enters a loading state —
 * showing a spinner, swapping the label text, and blocking re-submission —
 * while the request is in-flight.
 *
 * Thin wrapper around the existing <Button> component so all variant/size
 * tokens are preserved.
 *
 * @example
 * <!-- Basic usage with Inertia useForm -->
 * <SubmitButton :processing="form.processing" />
 *
 * <!-- Custom labels -->
 * <SubmitButton
 *     :processing="form.processing"
 *     label="Send invitation"
 *     loading-label="Sending…"
 * />
 *
 * <!-- Danger variant, full width -->
 * <SubmitButton
 *     :processing="form.processing"
 *     label="Delete account"
 *     loading-label="Deleting…"
 *     variant="danger"
 *     full-width
 * />
 *
 * <!-- Custom slot content (e.g. icon before label) -->
 * <SubmitButton :processing="form.processing" label="">
 *     <PaperAirplaneIcon class="size-4" />
 *     Send
 * </SubmitButton>
 */

import Button from '@/Components/UI/Button.vue';

const props = defineProps({
    /** Pass `form.processing` from Inertia's useForm. */
    processing: { type: Boolean, default: false },

    /** Button label when idle. Ignored when using the default slot. */
    label: { type: String, default: 'Save' },

    /** Button label while the form is submitting. */
    loadingLabel: { type: String, default: 'Saving…' },

    /** Visual variant forwarded to <Button>. */
    variant: {
        type: String,
        default: 'primary',
        validator: (v) => ['primary', 'secondary', 'danger', 'ghost', 'link'].includes(v),
    },

    /** Size token forwarded to <Button>. */
    size: {
        type: String,
        default: 'md',
        validator: (v) => ['xs', 'sm', 'md', 'lg'].includes(v),
    },

    /** Stretch to full container width. */
    fullWidth: { type: Boolean, default: false },
});
</script>

<template>
    <Button
        type="submit"
        :variant="variant"
        :size="size"
        :loading="processing"
        :disabled="processing"
        :class="{ 'w-full': fullWidth }"
    >
        <!--
            If the consumer passes slot content, render that.
            Otherwise fall back to the label/loadingLabel props.
        -->
        <slot>
            {{ processing ? loadingLabel : label }}
        </slot>
    </Button>
</template>
