<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';

defineOptions({ layout: AuthLayout });

defineProps({
    status: {
        type: String,
        default: null,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <div class="space-y-6">

        <!-- Heading -->
        <div class="text-center">
            <h1 class="text-3xl font-bold text-gradient-primary">Forgot password?</h1>
            <p class="mt-2 text-sm text-gray-500">
                Enter your email address and we'll send you a link to reset your password.
            </p>
        </div>

        <!-- Success status -->
        <div
            v-if="status"
            class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800"
        >
            {{ status }}
        </div>

        <form class="space-y-4" @submit.prevent="submit">

            <!-- Email -->
            <div class="space-y-2">
                <label for="email" class="block text-sm font-medium text-primary-800">
                    Email address
                </label>
                <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    name="email"
                    autocomplete="email"
                    required
                    autofocus
                    placeholder="you@example.com"
                    class="block w-full rounded-lg border px-4 py-3 text-sm shadow-sm placeholder:text-gray-400
                           transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent
                           bg-white"
                    :class="form.errors.email
                        ? 'border-red-300 text-red-900 focus:ring-red-500'
                        : 'border-gray-300 hover:border-gray-400'"
                />
                <p v-if="form.errors.email" class="text-sm text-red-600">{{ form.errors.email }}</p>
            </div>

            <!-- Submit -->
            <button
                type="submit"
                :disabled="form.processing"
                class="flex w-full items-center justify-center gap-2 rounded-lg bg-gradient-primary px-4 py-3 text-sm
                       font-semibold text-white shadow-lg shadow-primary-800/40 transition-all hover:opacity-90 hover:shadow-xl
                       focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2
                       disabled:cursor-not-allowed disabled:opacity-60 disabled:shadow-none"
            >
                <svg v-if="form.processing" class="size-4 animate-spin" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                </svg>
                {{ form.processing ? 'Sending…' : 'Send reset link' }}
            </button>
        </form>

        <p class="text-center text-sm text-gray-500">
            Remembered it?
            <Link
                :href="route('login')"
                class="font-semibold text-primary-600 underline-offset-2 hover:text-accent-600 hover:underline transition-colors"
            >
                Back to sign in
            </Link>
        </p>
    </div>
</template>
