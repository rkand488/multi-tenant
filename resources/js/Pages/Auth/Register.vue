<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';

defineOptions({ layout: AuthLayout });

const form = useForm({
    workspace_name: '',
    slug: '',
    owner_name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

// Auto-generate slug from workspace name
const onWorkspaceNameInput = () => {
    form.slug = form.workspace_name
        .toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '')
        .trim()
        .replace(/\s+/g, '-');
};

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <div class="space-y-6">

        <!-- Heading -->
        <div class="text-center">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Create your workspace</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started for free — no credit card required</p>
        </div>

        <form class="space-y-4" @submit.prevent="submit">

            <!-- Workspace name -->
            <div class="space-y-1">
                <label for="workspace_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Workspace name
                </label>
                <input
                    id="workspace_name"
                    v-model="form.workspace_name"
                    type="text"
                    autocomplete="organization"
                    required
                    autofocus
                    placeholder="Acme Corp"
                    class="block w-full rounded-lg border px-3 py-2 text-sm shadow-sm placeholder:text-gray-400
                           focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                           dark:bg-gray-700 dark:text-white dark:placeholder:text-gray-500"
                    :class="form.errors.workspace_name
                        ? 'border-red-400 text-red-900 focus:ring-red-500'
                        : 'border-gray-300 dark:border-gray-600'"
                    @input="onWorkspaceNameInput"
                />
                <p v-if="form.errors.workspace_name" class="text-xs text-red-600">{{ form.errors.workspace_name }}</p>
            </div>

            <!-- Workspace URL / slug -->
            <div class="space-y-1">
                <label for="slug" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Workspace URL
                </label>
                <div class="flex rounded-lg border shadow-sm overflow-hidden"
                     :class="form.errors.slug ? 'border-red-400' : 'border-gray-300 dark:border-gray-600'">
                    <span class="inline-flex items-center px-3 bg-gray-50 text-sm text-gray-500 border-r
                                 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-400 border-gray-300 dark:border-gray-600">
                        app/
                    </span>
                    <input
                        id="slug"
                        v-model="form.slug"
                        type="text"
                        autocomplete="off"
                        required
                        placeholder="acme-corp"
                        class="block w-full px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-inset
                               focus:ring-indigo-500 dark:bg-gray-700 dark:text-white dark:placeholder:text-gray-500"
                    />
                </div>
                <p v-if="form.errors.slug" class="text-xs text-red-600">{{ form.errors.slug }}</p>
            </div>

            <!-- Owner name -->
            <div class="space-y-1">
                <label for="owner_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Your name
                </label>
                <input
                    id="owner_name"
                    v-model="form.owner_name"
                    type="text"
                    autocomplete="name"
                    required
                    placeholder="Jane Smith"
                    class="block w-full rounded-lg border px-3 py-2 text-sm shadow-sm placeholder:text-gray-400
                           focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                           dark:bg-gray-700 dark:text-white dark:placeholder:text-gray-500"
                    :class="form.errors.owner_name
                        ? 'border-red-400 text-red-900 focus:ring-red-500'
                        : 'border-gray-300 dark:border-gray-600'"
                />
                <p v-if="form.errors.owner_name" class="text-xs text-red-600">{{ form.errors.owner_name }}</p>
            </div>

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
                    autocomplete="new-password"
                    required
                    placeholder="Min. 8 characters"
                    class="block w-full rounded-lg border px-3 py-2 text-sm shadow-sm placeholder:text-gray-400
                           focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                           dark:bg-gray-700 dark:text-white dark:placeholder:text-gray-500"
                    :class="form.errors.password
                        ? 'border-red-400 text-red-900 focus:ring-red-500'
                        : 'border-gray-300 dark:border-gray-600'"
                />
                <p v-if="form.errors.password" class="text-xs text-red-600">{{ form.errors.password }}</p>
            </div>

            <!-- Confirm password -->
            <div class="space-y-1">
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Confirm password
                </label>
                <input
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    autocomplete="new-password"
                    required
                    placeholder="••••••••"
                    class="block w-full rounded-lg border px-3 py-2 text-sm shadow-sm placeholder:text-gray-400
                           focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                           dark:bg-gray-700 dark:text-white dark:placeholder:text-gray-500"
                    :class="form.errors.password_confirmation
                        ? 'border-red-400 text-red-900 focus:ring-red-500'
                        : 'border-gray-300 dark:border-gray-600'"
                />
                <p v-if="form.errors.password_confirmation" class="text-xs text-red-600">{{ form.errors.password_confirmation }}</p>
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
                <svg
                    v-if="form.processing"
                    class="size-4 animate-spin"
                    viewBox="0 0 24 24"
                    fill="none"
                >
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                </svg>

                {{ form.processing ? 'Creating workspace…' : 'Create workspace' }}
            </button>
        </form>

        <!-- Login link -->
        <p class="text-center text-sm text-gray-500 dark:text-gray-400">
            Already have an account?
            <Link
                :href="route('login')"
                class="font-medium text-indigo-600 hover:text-indigo-700 dark:text-indigo-400"
            >
                Sign in
            </Link>
        </p>
    </div>
</template>
