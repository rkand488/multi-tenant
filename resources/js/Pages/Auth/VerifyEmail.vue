<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

defineOptions({ layout: AuthLayout });

const props = defineProps({
    status: {
        type: String,
        default: null,
    },
});

const form = useForm({});
const justSent = ref(false);

const resend = () => {
    form.post(route('verification.send'), {
        onSuccess: () => {
            justSent.value = true;
        },
    });
};

const logout = () => {
    router.post(route('logout'));
};
</script>

<template>
    <div class="space-y-6">

        <!-- Heading -->
        <div class="text-center">
            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-primary-100">
                <svg class="h-8 w-8 text-primary-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-gradient-primary">Verify your email</h1>
            <p class="mt-2 text-sm text-gray-500">
                Thanks for signing up! Before you can access your workspace, please verify
                your email address by clicking the link we just sent you.
            </p>
        </div>

        <!-- Success: link sent -->
        <div
            v-if="status === 'verification-link-sent' || justSent"
            class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800"
        >
            A new verification link has been sent to your email address.
        </div>

        <!-- Actions -->
        <div class="space-y-3">
            <button
                type="button"
                :disabled="form.processing"
                class="flex w-full items-center justify-center rounded-lg bg-primary-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 disabled:opacity-50"
                @click="resend"
            >
                <span v-if="form.processing">Sending&hellip;</span>
                <span v-else>Resend verification email</span>
            </button>

            <button
                type="button"
                class="flex w-full items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500"
                @click="logout"
            >
                Sign out
            </button>
        </div>
    </div>
</template>
