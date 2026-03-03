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
    email:    '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <div class="space-y-6">

        <!-- Heading -->
        <div class="text-center">
            <h1 class="text-3xl font-bold text-gradient-primary">Welcome back</h1>
            <p class="mt-2 text-sm text-gray-500">Sign in to continue to your workspace</p>
        </div>

        <!-- Status message (e.g. after password reset) -->
        <div
            v-if="status"
            class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800"
        >
            {{ status }}
        </div>

        <!-- Form -->
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

            <!-- Password -->
            <div class="space-y-2">
                <label for="password" class="block text-sm font-medium text-primary-800">
                    Password
                </label>
                <input
                    id="password"
                    v-model="form.password"
                    type="password"
                    name="password"
                    autocomplete="current-password"
                    required
                    placeholder="Enter your password"
                    class="block w-full rounded-lg border px-4 py-3 text-sm shadow-sm placeholder:text-gray-400
                           transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent
                           bg-white"
                    :class="form.errors.password
                        ? 'border-red-300 text-red-900 focus:ring-red-500'
                        : 'border-gray-300 hover:border-gray-400'"
                />
                <p v-if="form.errors.password" class="text-sm text-red-600">{{ form.errors.password }}</p>
            </div>

            <!-- Remember me + Forgot password -->
            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                    <input
                        v-model="form.remember"
                        type="checkbox"
                        name="remember"
                        class="size-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500 cursor-pointer transition-colors"
                    />
                    Remember me
                </label>

                <Link
                    :href="route('password.request')"
                    class="text-sm font-medium text-accent-600 underline-offset-2 hover:text-accent-700 hover:underline transition-colors"
                >
                    Forgot password?
                </Link>
            </div>

            <!-- Submit -->
            <button
                type="submit"
                :disabled="form.processing"
                class="flex w-full items-center justify-center gap-2 rounded-lg bg-gradient-primary px-4 py-3 text-sm
                       font-semibold text-white shadow-lg shadow-primary-800/40 transition-all hover:opacity-90 hover:shadow-xl hover:shadow-primary-800/50
                       focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2
                       disabled:cursor-not-allowed disabled:opacity-60 disabled:shadow-none"
            >
                <!-- Spinner -->
                <svg
                    v-if="form.processing"
                    class="size-4 animate-spin"
                    viewBox="0 0 24 24"
                    fill="none"
                >
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                </svg>

                {{ form.processing ? 'Signing in…' : 'Sign in' }}
            </button>
        </form>

        <!-- Register link -->
        <p class="text-center text-sm text-gray-500">
            Don't have an account?
            <Link
                :href="route('register')"
                class="font-semibold text-primary-600 underline-offset-2 hover:text-accent-600 hover:underline transition-colors"
            >
                Sign up free
            </Link>
        </p>
    </div>
</template>
