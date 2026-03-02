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
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Sign in</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">to continue to your workspace</p>
        </div>

        <!-- Status message (e.g. after password reset) -->
        <div
            v-if="status"
            class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700"
        >
            {{ status }}
        </div>

        <!-- Form -->
        <form class="space-y-4" @submit.prevent="submit">

            <!-- Email -->
            <div class="space-y-1">
                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Email address
                </label>
                <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    autocomplete="email"
                    required
                    autofocus
                    placeholder="you@example.com"
                    class="block w-full rounded-lg border px-3 py-2 text-sm shadow-sm placeholder:text-gray-400
                           focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                           dark:bg-gray-700 dark:text-white dark:placeholder:text-gray-500"
                    :class="form.errors.email
                        ? 'border-red-400 text-red-900 focus:ring-red-500'
                        : 'border-gray-300 dark:border-gray-600'"
                />
                <p v-if="form.errors.email" class="text-xs text-red-600">{{ form.errors.email }}</p>
            </div>

            <!-- Password -->
            <div class="space-y-1">
                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Password
                </label>
                <input
                    id="password"
                    v-model="form.password"
                    type="password"
                    autocomplete="current-password"
                    required
                    placeholder="••••••••"
                    class="block w-full rounded-lg border px-3 py-2 text-sm shadow-sm placeholder:text-gray-400
                           focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                           dark:bg-gray-700 dark:text-white dark:placeholder:text-gray-500"
                    :class="form.errors.password
                        ? 'border-red-400 text-red-900 focus:ring-red-500'
                        : 'border-gray-300 dark:border-gray-600'"
                />
                <p v-if="form.errors.password" class="text-xs text-red-600">{{ form.errors.password }}</p>
            </div>

            <!-- Remember me + Forgot password -->
            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                    <input
                        v-model="form.remember"
                        type="checkbox"
                        class="size-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                    />
                    Remember me
                </label>

                <Link
                    :href="route('password.request')"
                    class="text-sm font-medium text-indigo-600 hover:text-indigo-700 dark:text-indigo-400"
                >
                    Forgot password?
                </Link>
            </div>

            <!-- Submit -->
            <button
                type="submit"
                :disabled="form.processing"
                class="flex w-full items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm
                       font-semibold text-white shadow-sm transition-colors hover:bg-indigo-700
                       focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2
                       disabled:cursor-not-allowed disabled:opacity-60"
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
        <p class="text-center text-sm text-gray-500 dark:text-gray-400">
            Don't have an account?
            <Link
                :href="route('register')"
                class="font-medium text-indigo-600 hover:text-indigo-700 dark:text-indigo-400"
            >
                Sign up free
            </Link>
        </p>
    </div>
</template>
